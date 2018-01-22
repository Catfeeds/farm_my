<?php
namespace Wap\Controller;

use Think\Controller;

class TradeBuyController extends WapController {


    public function trade_buy(){

        $transactionrecords_d=D('transactionrecords'); //（成交记录）
        $poundage_m=M('poundage');
        $entrust_d=D('entrust');           //委托管理
        $xnb_d=D('xnb');                   //虚拟币
        $userproperty_d=D('userproperty');//用户财产
        $markethouse_d=D('markethouse');  //市场
        $keeppushing_d=D('keeppushing');

        $price=I('price');    //单价
        $number=I('number');   //购买数量
        $dealpassword=I('dealpassword');   //交易密码
        $xnb_id=$this->strFilter(I('xnb'),true,'非法数据！');       //虚拟币id
        $markethouse=$this->strFilter(I('markethouse'),true,'非法数据！');   //交易市场

        if ($xnb_id==1){
            $this->error('非法数据');
        }


        if (session('user_wap')['dealpwd']!=jiami($dealpassword)){   //交易密码验证
            $this->error('交易密码不正确！');
            exit();
        }

        if (check_number($price)!=$price || check_number($number)!=$number || $price=="" || $number==""){    //单价，校验
            $this->error('非法参数！1');
            exit();
        }

        if (!$markethouse_d->criterionid($markethouse,$xnb_id)){   //判断虚拟币与市场的关系是否合法！S
            $this->error('非法参数！2');
            exit();
        };

        $xnb_back=$xnb_d->trade_xnb($xnb_id);   //本次交易货币

        if ($xnb_back['id']==""){
            $this->error('非法参数！x');
            exit();
        }

        $markethouse_back=$markethouse_d->getMarkethouse($markethouse);  //本次市场信息,
        if ($markethouse_back['id']==""){
            $this->error('非法参数！M');
            exit();
        }



//		$poundage=$price*$number*$xnb_back['buypoundage']/100;  //本次手续费，扣购买的币
        $Transacallmoney=$price*$number;               //本次交易总额
        $number = round($number,6);     //购买数量,保留6位小数
        $price  =  round($price,6);		//购买单价,保留6位小数

        if($xnb_back['id']==""){
            $this->error('非法参数！2');
            exit();
        }

        if($markethouse_back['openingquotation']==2){
            $this->error('该市场未开盘，无法交易！');
            exit();
        }

        //判断判断本位币是否足够
        $checkmoney_back=$userproperty_d->checkmoney($markethouse_back['standardmoney'],$Transacallmoney);

        switch ($checkmoney_back){
            case 1:
                $this->error("系统错误！请联系我们！1");
                exit();
                break;
            case 2:
                $this->error("余额不足！请充值或分批购买");
                exit();
                break;
        }

        //交易买入上限
//		if($number>$xnb_back['buytop']){
//			$this->error("挂单数超过上限".$xnb_back['buytop']."！");
//			exit();
//		}


        $transac['xnb']=$xnb_id;
        $transac['market']=$markethouse_back['id'];
        $transac['time']=array('gt',$xnb_back['closetime']);

        $transac_back=$transactionrecords_d->field('id,price')->where($transac)->order('time desc')->limit(1)->select();

        if($price/$transac_back['price']>$xnb_back['riserange']/100){
            $this->error("你的单价超过涨停幅度！".$xnb_back['riserange']."%");
            exit();
        }

        if($price/$transac_back['price']>$xnb_back['fallrange']/100){
            $this->error("你的单价超过跌停幅度！".$xnb_back['fallrange']."%");
            exit();
        }
        if ($Transacallmoney>$markethouse_back['maxallmoney'] && $markethouse_back['maxallmoney']!=0){                          //买家最大交易价
            $this->error('单笔交易总额大于最大交易总额'.'￥'.$markethouse_back['maxallmoney'].' !');
            exit();
        }
        if ($Transacallmoney<$markethouse_back['minallmoney'] && $markethouse_back['minallmoney']!=0){                          //买家最大交易价
            $this->error('单笔交易总额小于最小交易总额'.'￥'.$markethouse_back['minallmoney'].' !');
            exit();
        }
        //最大最小交易额暂定挂单时限制
        if ($price>$markethouse_back['buymaxprice'] && $markethouse_back['buymaxprice']!=0){                          //买家最大交易价
            $this->error('单价大于最大交易价'.'￥'.$markethouse_back['buymaxprice'].' !');
            exit();
        }

        if ($price<$markethouse_back['buyminprice'] && $markethouse_back['buyminprice']!=0){                           //买家最小交易价
            $this->error('单价小于最小交易价'.'￥'.$markethouse_back['buyminprice'].' !');
            exit();
        }

        $standardmoney_id   = $markethouse_back['standardmoney'];  //该市场本位币id
        $standardmoney_name = $markethouse_back['standardmoney_brief'];   //该市场本位币简称
        $xnb_name=$xnb_back['brief'];   //此次交易虚拟币简称

        $fp = fopen($xnb_back['buycomplicated'],'r+');     //文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读
        if(flock($fp,LOCK_EX)){         //单线业务逻辑处理
            $property_d = D('property');          //用户资产流水


            $entrustwater_m =M('entrustwater');   //挂单记录
            $entrust_m = M('entrust');

            $entrustwater_m->startTrans();   //开启事务

            $buy_trde=array(
                'userid'=>session('user_wap')['id'],
                'username'=>session('user_wap')['user_name'],
                'market'=>$markethouse_back['id'],  //市场
                'price'=>$price,
                'number'=>round($number,6),
                'allmoney'=>$Transacallmoney,   //交易总额
                'type'=>1,                      //1买2卖
                'poundage'=>$xnb_back['buypoundage'],  //记录手续费，手续费为百分比，按购买到的币百分比收币
                'xnb'=>$xnb_id,                 // 购买的虚拟币类型
                'oderfor'=>session('user_wap')['id'].time().rand(1000000,2000000),
                'addtime'=>time(),
                'standardmoney'=>$standardmoney_id  //本位币
            );

            //交易开始将用户资产锁死，获取本用户的资产
            $lock_back=$userproperty_d->lock(true)->where(array(
                'userid'=>session('user_wap')['id']
            ))->find();

            if ($lock_back!=true){            //判断是否开启读写锁死！
                $entrustwater_m->rollback();
                $this->error('挂单失败！9');
                exit();
            }

            #重消费

            if ($lock_back['repeats']>0){
                $pay_money = $price*$number;
                $pay_repeats = 0;
                if ($lock_back['repeats']>=$pay_money){ #如果重消足够,全部扣重消

                    $pay_repeats = $pay_money;

                }else{

                    $pay_repeats = $lock_back['repeats'];  #如果不够 ，讲重消扣完 ，并且扣现金

                    //买家购买金额的流水
                    $property_buyset_back=$lock_back; //获取用户的财产信息
                    $property_buyset['userid']=session('user')['id'];
                    $property_buyset['username']=session('user')['user_name'];
                    $property_buyset['xnb']=$markethouse_back['standardmoney'];  //买家扣除的是市场本位币
                    $property_buyset['operatenumber']=$price*$number-$pay_repeats; //操作数量（金额）
                    $property_buyset['operatetype']='挂单扣除';
                    $property_buyset['operaefront']=$property_buyset_back[$standardmoney_name];  //操作之前
                    $property_buyset['operatebehind']=round($property_buyset['operaefront']-$property_buyset['operatenumber'],6); //操作之后
                    $property_buyset['time']=$buy_trde['addtime'];
                    $back=$property_d->PropertyAdd($property_buyset); //添加流水
                    if ($back==false){
                        $entrustwater_m->rollback();
                        $this->error('挂单失败！L1');
                        exit();
                    }

                    //买家扣除购买金额，市场本位币
                    $user_setDec_back=$userproperty_d->where(array(
                        'userid'=>session('user_wap')['id']
                    ))->setDec($standardmoney_name,$price*$number-$pay_repeats);

                    if($user_setDec_back==false){
                        $entrustwater_m->rollback();
                        $this->error('挂单失败！1');
                        exit();
                    }

                }


                #优先扣除重消币   因为有重消，所以不论哪种情况都要扣重消账户
                //重消币的流水
                $property_buyset_back=$lock_back; //获取用户的财产信息
                $property_buyset['userid']=session('user')['id'];
                $property_buyset['username']=session('user')['user_name'];
                $property_buyset['xnb']=2;  //买家扣除的是市场本位币
                $property_buyset['operatenumber']=$pay_repeats; //操作数量（金额）
                $property_buyset['operatetype']='挂单扣除';
                $property_buyset['operaefront']=$property_buyset_back[$standardmoney_name];  //操作之前
                $property_buyset['operatebehind']=round($property_buyset['operaefront']-$property_buyset['operatenumber'],6); //操作之后
                $property_buyset['time']=$buy_trde['addtime'];
                $back=$property_d->PropertyAdd($property_buyset); //添加流水
                if ($back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！L1');
                    exit();
                }

                //买家扣除重消币
                $user_setDec_back=$userproperty_d->where(array(
                    'userid'=>session('user_wap')['id']
                ))->setDec('repeats',$pay_repeats);

                if($user_setDec_back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！1');
                    exit();
                }
                $buy_trde['repeats'] = $pay_repeats;

            }else{
                //买家购买金额的流水
                $property_buyset_back=$lock_back; //获取用户的财产信息
                $property_buyset['userid']=session('user_wap')['id'];
                $property_buyset['username']=session('user_wap')['user_name'];
                $property_buyset['xnb']=$markethouse_back['standardmoney'];  //买家扣除的是市场本位币
                $property_buyset['operatenumber']=$price*$number; //操作数量（金额）
                $property_buyset['operatetype']='挂单扣除';
                $property_buyset['operaefront']=$property_buyset_back[$standardmoney_name];  //操作之前
                $property_buyset['operatebehind']=round($property_buyset['operaefront']-$property_buyset['operatenumber'],6); //操作之后
                $property_buyset['time']=$buy_trde['addtime'];
                $back=$property_d->PropertyAdd($property_buyset); //添加流水
                if ($back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！L1');
                    exit();
                }

                //买家扣除购买金额，市场本位币
                $user_setDec_back=$userproperty_d->where(array(
                    'userid'=>session('user_wap')['id']
                ))->setDec($standardmoney_name,$price*$number);

                if($user_setDec_back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！1');
                    exit();
                }
            }

            //挂单记录的处理
            $water_back=$entrustwater_m->add($buy_trde);
            if ($water_back===false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！2');
                exit();
            }
            $buy_trde['entrustwater_id']=$water_back; //挂单id

            //交易处理
            $entrust_where['price']=array('elt',$price);    //匹配条件 小于等于 买家
            $entrust_where['type']=2;                       //卖出类型
            $entrust_where['xnb']=$xnb_id;                   //购买的虚拟币
            $entrust_where['market']=$markethouse_back['id'];  //所在的市场

            $this->Transactionrecords(
                $entrust_d,
                $entrust_where,
                $entrust_m,
                $buy_trde,
                $entrustwater_m,
//				$number,
                $transactionrecords_d,
//				$markethouse_back['id'],        //市场
//				$xnb_back['buypoundage'],      //买家手续费
//				$xnb_back['sellpoundage'],    //卖家手续费
                $poundage_m,
                $standardmoney_id,
                $standardmoney_name,
                $userproperty_d,
                $xnb_name,
                $property_d,
                $keeppushing_d,
                $xnb_back['memory_day']
            );

            $entrustwater_m->commit();
            $this->success("挂单成功！");
        }else{
            $this->error('系统错误！请联系我们！wj');
        }
        fclose($fp);
        exit();
    }
    //购买交易方法
    public function Transactionrecords(                            //购买交易方法
        $entrust_d,
        $entrust_where,
        $entrust_m,
        $buy_trde,
        $entrustwater_m,
//		$number,
        $transactionrecords_d,
//		$market,
//		$buypoundage,
//		$sellpoundage,
        $poundage_m,
        $standardmoney_id,
        $standardmoney_name,
        $userproperty_d,
        $xnb_name,
        $property_d,
        $keeppushing_d,
        $memory_day		//储存天数

    ){

        $entrust_back=$entrust_d->buy_data($entrust_where);     //匹配交易数量

        if ($entrust_back['id']==""){                     //如果卖家单id为空，说明价格过低匹配失败，交易单直接挂起！

            $entrust_addback=$entrust_m->add($buy_trde);
            if ($entrust_addback===false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！3');
                exit();
            }
            $entrustwater_m->commit();
            $this->success('挂单成功！');
            exit();
        }
        //数据库读写锁死，该订单和发布订单的资产！
        $entrust_lock      =  $entrust_d->lock(true)->where(array('id'=>$entrust_back['id']))->find(); //将查找出来的订单锁死，防止数据脏读
        $userproperty_lock =  $userproperty_d->lock(true)->where(array('userid'=>$entrust_back['userid']))->find();       //将该订单发布者的资产锁住，防止脏读数据
        $entrustwater_lock =  $entrustwater_m->lock(true)->where(array('oderfor'=>$entrust_back['oderfor']))->find();    //将该委托记录锁死

        if ($entrust_lock!=true || $userproperty_lock!=true || $entrustwater_lock!=true){
            $entrustwater_m->rollback();
            $this->error('挂单失败！3');
            exit();
        }


        $trde          =0; //交易数
        $status        =0; //状态变量，用于判断是否递归
        $poundage_buy  =0; //本次买家手续费
        $poundage_sell =0; //本次卖家手续费
        $number_top    =$buy_trde['number'];  //交易前的数量
//		$poundage_top  =$buy_trde['poundage'];               //交易前的手续费
        $sell_back=0;   //判断卖家是否卖完


        if ($number_top>$entrust_back['number']) {       //买单大于卖单
            $sell_back=1;
            $status=1;
            $trde                = $entrust_back['number'];         //本次交易数
            $buy_trde['number'] = $buy_trde['number']-$entrust_back['number'];  //交易后的数量，递归变量

            $entrust_deleteback=$entrust_d->where(array(              //如果买单大于卖单，说明卖单已经卖完，则删除该条记录.
                'id'=>$entrust_back['id']
            ))->delete();

            if ($entrust_deleteback==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！4');
                exit();
            }
        }

        if ($number_top<$entrust_back['number']){         //买单小于卖单
            $trde                = $buy_trde['number'];                                      //本次交易数
            $buy_trde['number'] = 0;  //交易后的数量，递归变量

            $entrust_saveback=$entrust_d->where(array(    //扣除本次交易数量
                'id'=>$entrust_back['id']
            ))->setDec('number',$trde);

            if ($entrust_saveback==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！5');
                exit();
            }

        }

        if ($number_top==$entrust_back['number']){
            $sell_back=1;
            $trde                = $buy_trde['number'];         //本次交易数
            $buy_trde['number'] = 0;     //交易后的数量，递归变量

            $entrust_deleteback=$entrust_d->where(array(         //如果买单等于卖单，说明卖单已经卖完，则删除该条记录
                'id'=>$entrust_back['id']
            ))->delete();
            if ($entrust_deleteback==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！6');
                exit();
            }
        }


        if ($sell_back===1){
            $sell_backs=$entrustwater_m->where(['id'=>$entrustwater_lock['id']])->save(['cancel'=>2]);
            if ($sell_backs==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！11');
                exit();
            }

        }

        //因为手续费有可能因为小数点的原因，变为负数，所以当小于0的时候，将手续费变为0
        $poundage_buy           	= getEffective($trde*($buy_trde['poundage']/100));          //本次买家手续费=交易数量*买家手续费百分比

        $poundage_sell         		= getEffective($trde*$entrust_back['price']*($entrust_back['poundage']/100));      //卖家手续费=（交易数量*单价）* 卖家手续费

        $buy_trde['allmoney']     	= getEffective($buy_trde['allmoney']-($trde*$entrust_back['price']));   //总金额=总金额-交易额

        //交易记录的生成！
        $transactionrecords_addback=$transactionrecords_d->adds(
            session('user_wap')['id'],
            $entrust_back['userid'],
            $buy_trde['market'],
            $entrust_back['price'],
            $trde,
            $poundage_buy,
            $poundage_sell,
            $buy_trde['xnb'],
            1,
            $buy_trde['oderfor'],
            $entrust_back['oderfor'],/*$buy_trde['oderfor']*/
            $standardmoney_id
        );

        //手续费记录的生成！
        $oderfor=array(
            '买家订单'=>$buy_trde['oderfor'],
            '卖家订单'=>$entrust_back['oderfor']
        );
        $oderfor=json_encode($oderfor);


        //分红手续费功能的实现！
        $keep = $keeppushing_d->addKeeppushing(session('user_wap')['id'],$poundage_buy,$xnb_name,$entrust_back['xnb'],$buy_trde['addtime'],'买入虚拟币',$oderfor);

        if ($keep==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！L6');
            exit();
        }

        $poundage_addback=$poundage_m->add(array(   //买家手续费
            'market'=>$buy_trde['market'],
            'type'=>1,
            'money'=>$poundage_buy,
            'time'=>$buy_trde['addtime'],
            'xnb'=>$entrust_back['xnb'],  //币种类型,为得到的虚拟币
            'oderfor'=>$buy_trde['oderfor'],
            'userid'=>session('user_wap')['id'],
            'username'=>session('user_wap')['user_name']
        ));

        //分红手续费功能的实现！
        $keep=$keeppushing_d->addKeeppushing($entrust_back['userid'],$poundage_sell,$standardmoney_name,$standardmoney_id,$buy_trde['addtime'],'卖出虚拟币',$oderfor);

        if ($keep==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！L9');
            exit();
        }

        $poundage_addbacks=$poundage_m->add(array(   //卖家手续费
            'market'=>$buy_trde['market'],
            'type'=>2,
            'money'=>$poundage_sell,
            'time'=>$buy_trde['addtime'],
            'xnb'=>$standardmoney_id,
            'oderfor'=>$entrust_back['oderfor'],
            'userid'=>$entrust_back['userid'],
            'username'=>$entrust_back['username'],
        ));

        //卖家资产流水账的生成！
        $property_sell_back=$userproperty_d->getUserMoney($entrust_back['userid'],$standardmoney_name.',repeat'); //获取用户的财产信息


        //卖家收入本金币  ....  卖家收入的本金币的一部分进入重消账户

        #进入重复消费
        $tradeSellController = new TradeSellController();
        $repeat_back = $tradeSellController->repeat([  //返回本次增加的重消币
            'userid'=>$entrust_back['userid'],  //用户id
            'money'=>getEffective($trde*$entrust_back['price']-$poundage_sell) //本次交易的金额
        ]);

        if ($repeat_back){
            #重复消费资金明细
            $property_repeat['userid']=$entrust_back['userid'];
            $property_repeat['username']=$entrust_back['username'];
            $property_repeat['xnb']=2;  //卖家得到的是本位币
            $property_repeat['operatenumber']=$repeat_back; //操作数量
            $property_repeat['operatetype']='卖出收入';
            $property_repeat['operaefront']=$property_sell_back['repeat'];  //操作之前
            $property_repeat['operatebehind']=getEffective($property_repeat['operaefront']+$property_repeat['operatenumber']); //操作之后
            $property_repeat['time']=$buy_trde['addtime'];
            $back=$property_d->PropertyAdd($property_repeat); //添加流水
            if ($back==false){
                $this->error('挂单失败！B');
            }
        }

        $property_sell['userid']=$entrust_back['userid'];
        $property_sell['username']=$entrust_back['username'];
        $property_sell['xnb']=$standardmoney_id;  //卖家得到的是本位币
        $property_sell['operatenumber']=getEffective($trde*$entrust_back['price']-$poundage_sell-$repeat_back); //操作数量
        $property_sell['operatetype']='卖出收入';
        $property_sell['operaefront']=$property_sell_back[$standardmoney_name];  //操作之前
        $property_sell['operatebehind']=getEffective($property_sell['operaefront']+$property_sell['operatenumber']); //操作之后
        $property_sell['time']=$buy_trde['addtime'];
        $back=$property_d->PropertyAdd($property_sell); //添加流水
        if ($back==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！L');
            exit();
        }




        #进入本位币
        $userproperty_savaback=$userproperty_d->where(array(
            'userid'=>$entrust_back['userid']
        ))->setInc($standardmoney_name,getEffective($trde*$entrust_back['price']-$poundage_sell-$repeat_back));


        //买家资产流水账的生成！

        $property_buy_back=$userproperty_d->getUserMoney(session('user_wap')['id'],$xnb_name); //获取用户的财产信息
        $property_buy['userid']=session('user_wap')['id'];
        $property_buy['username']=session('user_wap')['user_name'];
        $property_buy['xnb']=$buy_trde['xnb'];     //买家收入的是认购币
        $property_buy['operatenumber']=$trde-$poundage_buy; //操作数量
        $property_buy['operatetype']='买入收入';
        $property_buy['operaefront']=$property_buy_back[$xnb_name];  //操作之前
        $property_buy['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
        $property_buy['time']=$buy_trde['addtime'];

        /*  废弃该方法 ，得到的认购币自动锁死
     $back=$property_d->PropertyAdd($property_buy); //添加流水
     if ($back==false){
         $entrustwater_m->rollback();
         $this->error('挂单失败！L');
         exit();
     }

     //买家收入认购币 交易数量-手续费

     $userproperty_savabacks=$userproperty_d->where(array(
         'userid'=>session('user')['id']
     ))->setInc($xnb_name,$trde-$poundage_buy);
     */

        //资产转存
        $memory_m = M('memory');
        $back = $memory_m->add([
            'user_id'=>session('user_wap')['id'],
            'xnb_id'=>$entrust_back['xnb'],
            'number_all'=>$trde,
            'time_start'=>time(),
            'time_end'=>time()+($memory_day*86400),
            'balance'=>$trde
        ]);

        if ($back==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！z');
            exit();
        }


        if ($poundage_addback==false||$poundage_addbacks==false || $transactionrecords_addback===false || $userproperty_savaback===false ){    //手续费类型添加，交易记录添加统一回滚
            $entrustwater_m->rollback();
            $this->error('挂单失败！7');
            exit();
        }

        if ($status==1){
            $this->Transactionrecords($entrust_d,
                $entrust_where,
                $entrust_m,
                $buy_trde,
                $entrustwater_m,
//				$number,
                $transactionrecords_d,
//				$market,
//				$buypoundage,
//				$sellpoundage,
                $poundage_m,
                $standardmoney_id,
                $standardmoney_name,
                $userproperty_d,
                $xnb_name,
                $property_d,
                $keeppushing_d,
                $memory_day
            );   //递归
        }
        //如果$status!=1说明该流程将买家的购买数量完成了，那么将返回用户未消耗完的用于购买的金额
        //修改订单状态为已完成！
        $bakc_stutus=$entrustwater_m->where(array('id'=>$buy_trde['entrustwater_id']))->save(['cancel'=>2]);
        if ($bakc_stutus===false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！p');
        }

        if ($buy_trde['allmoney']>0){
            $property_uback_back=$userproperty_d->getUserMoney(session('user')['id'],$standardmoney_name); //获取用户的财产信息

            $back_money = $buy_trde['allmoney'];
            $entrustwater_lock = $entrustwater_m->where(['id'=>$buy_trde['entrustwater_id']])->find();

            if ($entrustwater_lock['repeats']>0){  //如果用户使用重消账户支付

                #扣除的金额
                $out_money = $entrustwater_lock['allmoney']-$buy_trde['allmoney'];

                if ($out_money<=$entrustwater_lock['repeats']){  //消费的金额<重消金额
                    #应返的重消金额
                    $pay_repeats = $entrustwater_lock['repeats']- $out_money;
                    #应返的本金
                    $pay_money = $entrustwater_lock['allmoney']-$entrustwater_lock['repeats'];

                    #返回用户重消金额
                    $user_setinc=$userproperty_d->where(array('userid'=>session('user')['id']))->setInc('repeats',$pay_repeats);
                    if ($user_setinc==false){
                        $entrust_m->rollback();
                        $this->error('撤销失败！2');
                    }


                    #返回用户重消金额的流水
                    $property_buyset_repeats = $property_uback_back; //获取用户的财产信息
                    $property_repeats['userid'] = session('user')['id'];
                    $property_repeats['username'] = session('user')['user_name'];
                    $property_repeats['xnb']=2;  //买家扣除的是市场本位币
                    $property_repeats['operatenumber'] = $pay_repeats; //操作数量（金额）
                    $property_repeats['operatetype']='买单余额返回';
                    $property_repeats['operaefront']=$property_buyset_repeats['repeats'];  //操作之前
                    $property_repeats['operatebehind'] = round($property_repeats['operaefront']+$property_repeats['operatenumber'],6); //操作之后
                    $property_repeats['time'] = time();
                    $back=$property_d->PropertyAdd($property_repeats); //添加流水
                    if ($back==false){
                        $entrust_m->rollback();
                        $this->error('挂单失败！L1');
                        exit();
                    }

                    if ($pay_money!=0){
                        $user_setinc=$userproperty_d->where(array('userid'=>$entrust_lock['userid']))->setInc($standardmoney_name,$pay_money);
                        if ($user_setinc==false){
                            $entrust_m->rollback();
                            $this->error('撤销失败！2');
                        }

                        //返回买家金额的流水
                        $property_uback['userid']=session('user')['id'];
                        $property_uback['username']=session('user')['user_name'];
                        $property_uback['xnb']=$standardmoney_id;  //买家收入的是认购币
                        $property_uback['operatenumber']=$pay_money; //操作数量（金额）
                        $property_uback['operatetype']='买单余额返回';
                        $property_uback['operaefront']=$property_uback_back[$standardmoney_name];  //操作之前
                        $property_uback['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
                        $property_uback['time']=$buy_trde['addtime'];
                        $back=$property_d->PropertyAdd($property_uback); //添加流水
                        if ($back==false){
                            $entrustwater_m->rollback();
                            $this->error('挂单失败！L3');
                            exit();
                        }


                    }


                }

            }else{

                $property_uback=$userproperty_d->where(array(   //返回金额
                    'uerid'=>session('user')['id']
                ))->setInc($standardmoney_name,$buy_trde['allmoney']);


                if ($property_uback==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！s');
                }

                //返回买家金额的流水

                $property_uback['userid']=session('user')['id'];
                $property_uback['username']=session('user')['user_name'];
                $property_uback['xnb']=$standardmoney_id;
                $property_uback['operatenumber']=$buy_trde['allmoney']; //操作数量（金额）
                $property_uback['operatetype']='买单余额返回';
                $property_uback['operaefront']=$property_uback_back[$standardmoney_name];  //操作之前
                $property_uback['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
                $property_uback['time']=$buy_trde['addtime'];
                $back=$property_d->PropertyAdd($property_uback); //添加流水
                if ($back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！L3');
                    exit();
                }
            }
        }

    }
}