<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

//卖出模块
class TradeSellController extends HomeController{

    public function __construct(){
        parent::__construct();
        if (session('user')['id']==""){
            $this->redirect('Index/index');
        }
    }


    public function trade_buy(){
        $transactionrecords_d=D('transactionrecords'); //（成交记录）
        $poundage_m=M('poundage');
        $entrust_d=D('entrust');           //委托管理
        $xnb_d=D('xnb');                   //虚拟币
        $userproperty_d=D('userproperty');//用户财产
//        $userproperty_m=M('userproperty');//用户财产
        $markethouse_d=D('markethouse');  //市场
        $Keeppushing_d=D('keeppushing');;  //分红
        $price=I('price');    //单价
        $number=I('number');   //卖出数量
        $dealpassword=I('dealpassword');   //交易密码
        $xnb_id=$this->strFilter(I('xnb'),true,'非法数据');     //虚拟币id
        $markethouse=$this->strFilter(I('markethouse'),true,'非法数据！');   //交易市场

        if ($xnb_id==1){
            $this->error('非法数据');
        }

        if (session('user')['dealpwd']!=jiami($dealpassword)){   //交易密码验证
            $this->error('交易密码不正确！');
            exit();
        }

        if (check_number($price)!=$price || check_number($number)!=$number || $price=="" || $number==""){    //单价，数量校验
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

       // $poundage=round($allnumber*$xnb_back['sellpoundage']/100,6);  //本次手续费
        $number = getEffective($number);   //交易数,保留6位小数
        $price  = getEffective($price);     //单价，保留6位小数
        $Transacallmoney=getEffective($price*$number);

        if($xnb_back['id']==""){
            $this->error('非法参数！2');
            exit();
        }

        if($markethouse_back['openingquotation']==2){
            $this->error('该市场未开盘，无法交易！');
            exit();
        }

        $checkmoney_back=$userproperty_d->checkmoney($xnb_id,$number,true,$xnb_back['scale']); //判断出售的币种是否足够

        switch ($checkmoney_back){
            case 1:
                $this->error("系统错误！请联系我们！se");
                exit();
                break;
            case 2:
                $this->error("余额不足！请充值或分批购买");
                exit();
                break;
            case 3:
                $this->error('挂单比例大于'.$xnb_back['scale'].'%！');
                exit();
                break;
        }

        //交易卖出上限
//        if($number*$price>$xnb_back['selltop']){
//            $this->error("挂单数超过上限".$xnb_back['selltop']."！");
//            exit();
//        }
        //判断是否超出交易单价区间
       if ($xnb_back['price_up']<$price ){
           $this->error("你的挂单价格超出最大控制价！￥".floatval($xnb_back['price_up']).'元');
           exit();
       }

        if ( $price<$xnb_back['price_dow']){
            $this->error("你的挂单价格低于最大控制价！￥".floatval($xnb_back['price_dow']).'元');
            exit();
        }


        //从开盘时间算，判断涨幅！是否合法
        $transac['xnb']=$xnb_id;
        $transac['time']=array('gt',$markethouse_back['closetime']);

        $transac_back=$transactionrecords_d->field('id,price')->where($transac)->order('time desc')->limit(1)->find();

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

        if ($price>$markethouse_back['buymaxprice']){                          //买家最大交易价
            $this->error('单价大于最大交易价'.'￥'.$markethouse_back['buymaxprice'].' !');
            exit();
        }

        if ($price<$markethouse_back['buyminprice']){                           //买家最小交易价
            $this->error('单价小于最小交易价'.'￥'.$markethouse_back['buyminprice'].' !');
            exit();
        }



        $standardmoney_id=$markethouse_back['standardmoney'];  //该市场本位币id
        $standardmoney_name=$markethouse_back['standardmoney_brief'];   //该市场本位币简称
        $xnb_name=$xnb_back['brief']; //此次交易虚拟币简称

        $fp = fopen($xnb_back['sellcomplicated'],'r+');     //文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读

        if(flock($fp,LOCK_EX)){           //单线业务逻辑处理

            $property_d= D('property');   //用户资产流水
            $entrustwater_m=M('entrustwater');
            $entrust_m=M('entrust');

            $entrustwater_m->startTrans();  ////开启事务

            $buy_trde=array(
                'userid'=>session('user')['id'],
                'username'=>session('user')['user_name'],
                'market'=>$markethouse_back['id'],  //市场
                'price'=>$price,
                'number'=>$number,
                'allmoney'=>$price*$number,     //交易总额
                'type'=>2,                      //1买2卖
                'poundage'=>$xnb_back['sellpoundage'],   //卖家手续费百分比
                'xnb'=>$xnb_id,                //交易的币种类型
                'oderfor'=>session('user')['id'].time().rand(1000000,2000000),  //订单号
                'addtime'=>time(),
                'standardmoney'=>$standardmoney_id
            );

            //交易开始将用户资产锁死
            $lock_back=$userproperty_d->lock(true)->where(array('userid'=>session('user')['id']))->find();
            if ($lock_back!=true){            //判断是否开启读写锁死！
                $entrustwater_m->rollback();
                $this->error('挂单失败！9');
                exit();
            }

            //卖家卖出金额流水金额的流水
            $property_sellset_back=$lock_back; //获取用户的财产信息
            $property_sellset['userid']=session('user')['id'];
            $property_sellset['username']=session('user')['user_name'];
            $property_sellset['xnb']=$xnb_id;  //卖家扣除的是交易币
            $property_sellset['operatenumber']=$number; //操作数量（金额）
            $property_sellset['operatetype']='卖出挂单';
            $property_sellset['operaefront']=$property_sellset_back[$xnb_name];  //操作之前
            $property_sellset['operatebehind']=$property_sellset['operaefront']-$property_sellset['operatenumber']; //操作之后
            $property_sellset['time']=$buy_trde['addtime'];
            $back=$property_d->PropertyAdd($property_sellset); //添加流水
            if ($back==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！L');
                exit();
            }

            //卖家扣除出售货币(总币数，包括手续费)
            $user_setDec_back=$userproperty_d->where(array(
                'userid'=>session('user')['id']
            ))->setDec($xnb_name,$number);

            if($user_setDec_back==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！1');
                exit();
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
            $entrust_where['price']=array('egt',$price);    //匹配条件 小于等于 买家
            $entrust_where['type']=1;                       //买入类型
            $entrust_where['xnb']=$xnb_id;                   //购买的虚拟币
            $entrust_where['market']=$markethouse_back['id'];  //所在的市场

            $this->trade_sell(
                $entrust_d,
                $entrust_where,
                $entrust_m,
                $buy_trde,
                $entrustwater_m,
                $number,
                $transactionrecords_d,
                $xnb_back['currency_markethouse_id'],
                $xnb_back['buypoundage'],
                $xnb_back['sellpoundage'],
                $poundage_m,
                $standardmoney_id,
                $standardmoney_name,
                $userproperty_d,
                $xnb_name,
                $property_d,
                $xnb_back,
                $Keeppushing_d,
                $xnb_back['memory_day']);
            $entrustwater_m->commit();

            $this->success("挂单成功！");
        }else{
            $this->error('系统错误！请联系我们！wj');
        }
        fclose($fp);
        exit();
    }


//卖出交易方法
    private function trade_sell(
        $entrust_d,
        $entrust_where,
        $entrust_m,
        $buy_trde,
        $entrustwater_m,
        $number,
        $transactionrecords_d,
        $market,
        $buypoundage,
        $sellpoundage,
        $poundage_m,
        $standardmoney_id,
        $standardmoney_name,
        $userproperty_d,
        $xnb_name,
        $property_d,
        $xnb_back,
        $Keeppushing_d,
        $memory_day){

        $entrust_back=$entrust_d->buy_data($entrust_where);     //匹配交易数量
        if ($entrust_back['id']==""){                     //如果买家单id为空，说明价格过高，匹配失败，交易单直接挂起！
            $entrust_addback=$entrust_m->add($buy_trde);
            if ($entrust_addback==false){
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
        if ($entrust_lock!=true || $userproperty_lock!=true || $entrustwater_lock !=true){

            $entrustwater_m->rollback();
            $this->error('挂单失败！3');
            exit();
        }


        $trde          =0; //交易数
        $status        =0; //状态变量，用于判断是否递归，处理掉交易匹配后数量为0时的挂单bug
        $status_a      =0; //用于判断执行返回用户挂单金额
        $poundage_buy  =0; //本次买家手续费
        $poundage_sell =0; //本次卖家手续费
        $number_top    =$buy_trde['number'];  //交易前的数量
        //$poundage_top  =$buy_trde['poundage'];               //交易前的手续费
        $surplus_money=0;  //卖单剩余的金额


        if ($entrust_back['number']<$number_top) {       //卖单数大于买单数
            $status=1;
            $status_a=3;
            $trde                = $entrust_back['number'];         //本次交易数
            $buy_trde['number'] =$buy_trde['number']-$entrust_back['number'];  //交易后的数量，递归变量

            $entrust_deleteback=$entrust_d->where(array(              //卖单数大于买单数，说明买单已经买完，则删除该条记录.并且返回用户的剩余金额
                'id'=>$entrust_back['id']
            ))->delete();
            if ($entrust_deleteback==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！4');
                exit();
            }
        }

        if ($entrust_back['number']>$number_top){                  //买单大于卖单
            $trde                = $buy_trde['number'];            //本次交易数
            $buy_trde['number'] = 0;                               //交易后的数量，递归变量
            $status=2;
        }

        if ($number_top==$entrust_back['number']){
            $status_a=3;
            $trde                = $buy_trde['number'];                //本次交易数
            $buy_trde['number'] = 0;     //交易后的数量，递归变量
            
            $entrust_deleteback=$entrust_d->where(array(         //如果买单等于卖单，说明卖单已经卖完，买家买完，则删除该条记录
                'id'=>$entrust_back['id']
            ))->delete();
            if ($entrust_deleteback==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！6');
                exit();
            }
        }
        //为了防止手续费因为小数点的原因变为负数，故当手续费小于0的情况下将手续费归0；
        $poundage_buy   = getEffective($trde*($entrust_back['poundage']/100));          //本次买家手续费
        $poundage_sell  = getEffective($trde*$buy_trde['price']*($buy_trde['poundage']/100));      //卖家手续费=交易数/交易前的数量*手续费

        if ($status_a==3){    //返回买家剩余金额,修改流水单为交易中
            $buy_back=$entrustwater_m->where(['id'=>$entrustwater_lock['id']])->save(['cancel'=>2]);
            if ($buy_back==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！6');
                exit();
            }
            $surplus_money=$entrust_back['allmoney']-($trde*$buy_trde['price']);
            if ($surplus_money>0){
                //返回买家买家购买金额的流水
                $property_buy_back=$userproperty_d->getUserMoney($entrust_back['userid'],$standardmoney_name); //获取用户的财产信息
                $property_buyback['userid']=$entrust_back['userid'];
                $property_buyback['username']=$entrust_back['username'];
                $property_buyback['xnb']=$standardmoney_id;  //买家返回的币种是是手续费类型
                $property_buyback['operatenumber']=$surplus_money; //操作数量（金额）
                $property_buyback['operatetype']='买单余额返回';
                $property_buyback['operaefront']=$property_buy_back[$standardmoney_name];  //操作之前
                $property_buyback['operatebehind']=$property_buyback['operaefront']+$property_buyback['operatenumber']; //操作之后
                $property_buyback['time']=$buy_trde['addtime'];
                $back=$property_d->PropertyAdd($property_buyback); //添加流水
                if ($back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！L');
                    exit();
                }
                //返回买家金额的操作
                $back_money=$userproperty_d->where(array(
                    'userid'=>$entrust_back['userid']
                ))->setInc($standardmoney_name,$surplus_money);
                if ($back_money==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！51');
                    exit();
                }
            }

        }

        if ($status==2){         //买家扣除本次交易数量，并且扣除总金额
            $entrust_savedata['id']=$entrust_back['id'];
            $entrust_savedata['number']= $entrust_back['number']- $trde ;  //数量=原数量-交易数量
            $entrust_savedata['allmoney']= getEffective($entrust_back['allmoney']-($trde*$buy_trde['price']));
            $entrust_saveback=$entrust_d->save($entrust_savedata);
            if ($entrust_saveback==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！5');
                exit();
            }
        }

        //交易记录的生成！

        $transactionrecords_addback=$transactionrecords_d->adds(
            $entrust_back['userid'],
            session('user')['id'],
            $buy_trde['market'],
            $buy_trde['price'],
            $trde,
            $poundage_buy,
            $poundage_sell,
            $buy_trde['xnb'],
            2,
            $entrust_back['oderfor'],
            $buy_trde['oderfor'],
            $standardmoney_id
            );

        //手续费记录的生成！
        $oderfor=array(
            '买家订单'=>$buy_trde['oderfor'],
            '卖家订单'=>$entrust_back['oderfor']
        );
        $oderfor=json_encode($oderfor);

        $keep=$Keeppushing_d->addKeeppushing($entrust_back['userid'],$poundage_buy,$xnb_name,$entrust_back['xnb'],$buy_trde['time'],'买入虚拟币',$oderfor);    //分红手续费功能的实现！
        if ($keep==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！L');
            exit();
        }

        $poundage_addback=$poundage_m->add(array(   //买家手续费
            'market'=>$buy_trde['market'],
            'type'=>1,
            'money'=>$poundage_buy,
            'time'=>$buy_trde['addtime'],
            'xnb'=>$entrust_back['xnb'],
            'oderfor'=>$buy_trde['oderfor'],
            'userid'=>$entrust_back['userid'],
            'username'=>$entrust_back['username']
        ));

        $keep=$Keeppushing_d->addKeeppushing(session('user')['id'],$poundage_sell,$standardmoney_name,$standardmoney_id,$buy_trde['time'],'卖出虚拟币',$oderfor);    //分红手续费功能的实现！
        if ($keep==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！L');
            exit();
        }

        $poundage_addbacks=$poundage_m->add(array(   //卖家手续费
            'market'=>$buy_trde['market'],
            'type'=>2,
            'money'=>$poundage_sell,
            'time'=>$buy_trde['addtime'],
            'xnb'=>$standardmoney_id,
            'oderfor'=>$entrust_back['oderfor'],
            'userid'=>session('user')['id'],
            'username'=>session('user')['user_name'],
        ));

        //卖家收入本金币（部分进入重销）
        #进入重销
        $repeat_back = $this->repeat([
            'userid'=>session('user')['id'],
            'money'=>$trde*$buy_trde['price']-$poundage_sell
        ],$userproperty_d);



        #重销流水
        $property_sell_back=$userproperty_d->getUserMoney(session('user')['id'],$standardmoney_name); //获取用户的财产信息
        if ($repeat_back){
            //卖家收入流水账的生成！
            $property_repeat['userid'] = session('user')['id'];
            $property_repeat['username'] = session('user')['user_name'];
            $property_repeat['xnb'] = $standardmoney_id;  //卖家收入的是本金币
            $property_repeat['operatenumber']=$trde*$buy_trde['price']-$poundage_sell; //操作数量（金额）
            $property_repeat['operatetype']='卖出收入';
            $property_repeat['operaefront']=$property_sell_back[$standardmoney_name];  //操作之前
            $property_repeat['operatebehind']=$property_repeat['operaefront']+$property_repeat['operatenumber']; //操作之后
            $property_srepeat['time']=$buy_trde['addtime'];
            $back=$property_d->PropertyAdd($property_repeat); //添加流水
            if ($back==false){
                $entrustwater_m->rollback();
                $this->error('挂单失败！r');
                exit();
            }
        }

        //卖家收入流水账的生成！
        $property_sell['userid']=session('user')['id'];
        $property_sell['username']=session('user')['user_name'];
        $property_sell['xnb']=$standardmoney_id;  //卖家收入的是本金币
        $property_sell['operatenumber']=$trde*$buy_trde['price']-$poundage_sell-$repeat_back; //操作数量（金额）
        $property_sell['operatetype']='卖出收入';
        $property_sell['operaefront']=$property_sell_back[$standardmoney_name];  //操作之前
        $property_sell['operatebehind']=$property_sell['operaefront']+$property_sell['operatenumber']; //操作之后
        $property_sell['time']=$buy_trde['addtime'];
        $back=$property_d->PropertyAdd($property_sell); //添加流水
        if ($back==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！L');
            exit();
        }
        //卖家收入本金币
        $userproperty_savaback=$userproperty_d->where(array(
            'userid'=>session('user')['id']
        ))->setInc($standardmoney_name,$trde*$buy_trde['price']-$poundage_sell-$repeat_back);



        //买家收入流水账的生成！
        /* 方法弃用
        $property_buy_backperty=$userproperty_d->getUserMoney($entrust_back['userid'],$xnb_name); //获取用户的财产信息
        $property_buy['userid']=$entrust_back['userid'];
        $property_buy['username']=$entrust_back['username'];
        $property_buy['xnb']=$entrust_back['xnb'];  //买家收入的是认购币
        $property_buy['operatenumber']=$trde; //操作数量（金额）
        $property_buy['operatetype']='买入收入';
        $property_buy['operaefront']=$property_buy_backperty[$xnb_name];  //操作之前
        $property_buy['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
        $property_buy['time']=$buy_trde['addtime'];
        $back=$property_d->PropertyAdd($property_buy); //添加流水
        if ($back==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！L');
            exit();
        }
        //买家收入认购币
        $userproperty_savabacks=$userproperty_d->where(array(
            'userid'=>$entrust_back['userid']
        ))->setInc($xnb_name,$trde);
        */

        //资产转存
        $memory_m = M('memory');
        $back = $memory_m->add([
            'user_id'=>$entrust_back['userid'],
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




        if ($poundage_addback==false||$poundage_addbacks==false || $transactionrecords_addback===false || $userproperty_savaback===false || $userproperty_savabacks===false){    //手续费类型添加，交易记录添加统一回滚
            $entrustwater_m->rollback();
            $this->error('挂单失败！7');
            exit();
        }

        if ($status==1){
            $this->trade_sell(
                $entrust_d,
                $entrust_where,
                $entrust_m,
                $buy_trde,
                $entrustwater_m,
                $number,
                $transactionrecords_d,
                $market,
                $buypoundage,
                $sellpoundage,
                $poundage_m,
                $standardmoney_id,
                $standardmoney_name,
                $userproperty_d,
                $xnb_name,
                $property_d,
                $xnb_back,
                $Keeppushing_d,
                $memory_day);
        }
        //$status!=1说明卖完了修改记录为交易完成
        $sell_back=$entrustwater_m->where(['id'=>$buy_trde['entrustwater_id']])->save(['cancel'=>2]);
        if ($sell_back===false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！7');
        }

    }

    /**
     * @param array $data
     *                     userid 用户id
     *                     money  金额（本次交易的金额）
     *@param  $userproperty_d 用户资金模型
     */
    function repeat(array $data){
        $repeat_cfg = M('repeat_cfg');

//        $userproperty_d = D('userproperty');

        $cfg = $repeat_cfg->find();

        $number = $data['money']*$cfg['data'];
        
        if (!empty($cfg['data'])){
            $repeat= 'repeats';
            $back =M('userproperty')->where(['userid'=>$data['userid']])->setInc($repeat,$number);
            if ($back){
                
                return  $number;
            }else{
                return false;
            }
            
        }else{
            
            return true;
        
        }

    }




}
