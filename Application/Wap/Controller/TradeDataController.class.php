<?php
namespace Wap\Controller;

use Think\Controller;

class TradeDataController extends WapController {

    //数据请求 卖，买挂单数据请求
    public function getentrust_s($limit,$xnb_id,$market){
        $entrust_m=M('entrust');
        $tentrust=array();
        //卖10，买10
        $tentrust['buy_data']=$entrust_m->where(array(
            'type'=>1,
            'xnb'=>$xnb_id,
            'market'=>$market
        ))->field('price,sum(number) as num')->group('price')->order('price desc,addtime desc')->limit($limit)->select();

        $tentrust['sell_data']=$entrust_m->where(array(
            'type'=>2,
            'xnb'=>$xnb_id,
             'market'=>$market
        ))->field('price,sum(number) as num')->limit($limit)->group('price')->order('price desc,addtime asc')->select();
        return $tentrust;
    }

    //传入用户id和虚拟币，返回用户订单信息(未成交的)
    public function getUserEntrust($uid,$market,$xnb='*'){
        $entrust_m=M('entrust');
        $data= $entrust_m
                ->where(['currency_entrust.userid'=>$uid,'currency_entrust.xnb'=>$xnb,'currency_entrust.market'=>$market])
                ->field('currency_entrust.oderfor,currency_entrust.type,currency_entrust.price as currency_entrust_price,currency_entrust.number as currency_entrust_number,currency_transactionrecords.number as currency_transactionrecords_number')
                ->join('left join currency_transactionrecords on currency_entrust.oderfor=currency_transactionrecords.buypoundage or currency_entrust.oderfor=currency_transactionrecords.sellpoundage')
                ->select();
        return $data;
    }

    //传入用户id和虚拟币，返回用户订单信息(成交的和未成交的)
    public function getUserEntrust_all($where,$start,$end){
        $entrustwater_m=M('entrustwater');
        $transactionrecords_m=M('transactionrecords');
        // 进行分页数据查询
        $list = $entrustwater_m
            ->field('
                currency_entrustwater.type,
                currency_entrustwater.cancel,
                currency_entrustwater.id as currency_entrustwater_id,
                currency_entrustwater.oderfor,
                currency_entrustwater.username as currency_entrustwater_username,
                currency_entrustwater.price,
                currency_entrustwater.number,
                currency_entrustwater.allmoney,
                currency_entrustwater.addtime as currency_entrustwater_time,
                currency_entrust.id as currency_entrust_id
                ')
            ->join('left join currency_entrust on currency_entrustwater.id=currency_entrust.entrustwater_id')
            ->where($where)->order('currency_entrustwater.cancel')->limit($start,$end)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条

        foreach ($list as $k=>$v){
            $where=array();
            switch ($v['type']){
                case 1:
                    $where['buyoderfor']=$v['oderfor'];
                    break;
                case 2:
                    $where['selloderfor']=$v['oderfor'];
                    break;
            }
            $deal=$transactionrecords_m->where($where)->sum('number');
            $deal=$deal?$deal:0;
            $list[$k]['deal']=$deal;
        }
        $this->assign('data',$list);// 赋值数据集,委托的数据
    }

    //撤销委托
    public function delete_order($oderfor){
        if (IS_AJAX){
            $oderfor=I('orderfor');
            $back=preg_match('/\d.{1,}/',$oderfor);
            if ($back!=1){
                $this->error('非法数据！');
            }
            $userproperty_m = M('userproperty');
            $entrust_m =   M('entrust');
            $entrustwater_m=M('entrustwater');
            $xnb_m=D('xnb');
            $property_d=D('property');
            $entrust_m->startTrans();

            //将自己 和 该委托单，委托单用户的资产锁死,订单必须是自己
            $entrustwater_lock=$entrustwater_m->lock(true)->field('id,userid,repeats,allmoney')->where(array('oderfor'=>$oderfor,'userid'=>session('user_wap')['id']))->find(); //记录锁死
            $entrust_lock = $entrust_m->lock(true)->field('allmoney,poundage,userid,standardmoney,xnb,type,number,repeats')->where(array('oderfor'=>$oderfor,'userid'=>session('user_wap')['id']))->find(); //数据池锁死
            $userproperty_lock= $userproperty_m->lock(true)->where(array('userid'=>$entrust_lock['userid']))->find();

            if ($entrust_lock!=true || $userproperty_lock!=true ||$entrustwater_lock !=true){      //判断是否开启行级锁
                $entrust_m->rollback();
                $this->error('撤销失败！1');
            }

            $property_buy=array();//用于流水
            //判断是买还是卖
            if ($entrust_lock['type']==1){     //如果是买返回allmoney
                #重消账户
                $pay_repeats = 0;
                #应返用户的金额
                $pay_money = $entrust_lock['allmoney'];

                $poundagetype_data=$xnb_m->getstandar($entrust_lock['standardmoney']);  //买返回本位币

                #判断重消的状况
                if ($entrustwater_lock['repeats']>0){  //如果用户使用重消账户支付

                    #扣除的金额
                    $out_money = $entrustwater_lock['allmoney']-$entrust_lock['allmoney'];


                    if ($out_money<=$entrustwater_lock['repeats']){  //消费的金额<重消金额
                        #应返的重消金额
                        $pay_repeats = $entrustwater_lock['repeats']- $out_money;
                        #应返的本金
                        $pay_money = $entrustwater_lock['allmoney']-$entrustwater_lock['repeats'];

                        #返回用户重消金额
                        $user_setinc=$userproperty_m->where(array('userid'=>$entrust_lock['userid']))->setInc('repeats',$pay_repeats);
                        if ($user_setinc==false){
                            $entrust_m->rollback();
                            $this->error('撤销失败！2');
                        }


                        #返回用户重消金额的流水
                        $property_buyset_repeats = $userproperty_lock; //获取用户的财产信息
                        $property_repeats['userid'] = session('user')['id'];
                        $property_repeats['username'] = session('user')['user_name'];
                        $property_repeats['xnb']=2;  //买家扣除的是市场本位币
                        $property_repeats['operatenumber'] = $pay_repeats; //操作数量（金额）
                        $property_repeats['operatetype']='买单撤销';
                        $property_repeats['operaefront']=$property_buyset_repeats['repeats'];  //操作之前
                        $property_repeats['operatebehind']=round($property_repeats['operaefront']+$property_repeats['operatenumber'],6); //操作之后
                        $property_repeats['time']=time();
                        $back=$property_d->PropertyAdd($property_repeats); //添加流水
                        if ($back==false){
                            $entrust_m->rollback();
                            $this->error('挂单失败！L1');
                            exit();
                        }



                    }

                }

                if ($pay_money!=0){
                    $user_setinc=$userproperty_m->where(array('userid'=>$entrust_lock['userid']))->setInc($poundagetype_data['brief'],$pay_money);
                    if ($user_setinc==false){
                        $entrust_m->rollback();
                        $this->error('撤销失败！2');
                    }
                }



                //卖家收入流水账的生成！
                $property_buy['operatenumber']=$entrust_lock['allmoney']; //操作数量（金额）
                $property_buy['operatetype']='买单撤销';
                $property_buy['xnb']=$entrust_lock['standardmoney'];  //卖家收入的是本金币


            }else{                             //如果是卖返回xnb个数
                $poundagetype_data=$xnb_m->getstandar($entrust_lock['xnb']);   //卖返回卖出交易币
                $property_buy['operatenumber'] = $entrust_lock['number']; //操作数量（金额）
                $property_buy['operatetype'] = '卖单撤销';
                $property_buy['xnb'] = $entrust_lock['xnb'];  //卖家收入的是本金币

                $user_setinc=$userproperty_m->where(array('userid'=>$entrust_lock['userid']))->setInc($poundagetype_data['brief'],$entrust_lock['number']);
                if ($user_setinc==false){
                    $entrust_m->rollback();
                    $this->error('撤销失败！2');
                }


            }


            //用户财产流水
            $property_buy_back=$userproperty_lock; //获取用户的财产信息
            $property_buy['userid']=session('user')['id'];
            $property_buy['username']=session('user')['user_name'];
            $property_buy['operaefront']=$property_buy_back[$poundagetype_data['brief']];  //操作之前
            $property_buy['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
            $property_buy['time']=time();
            $back=$property_d->PropertyAdd($property_buy); //添加流水

            if ($back==false){
                $entrust_m->rollback();
                $this->error('撤销失败！3');
            }

            //删除数据池数据
            $delete_back=$entrust_m->where(array('oderfor'=>$oderfor))->delete();
            if ($delete_back==false){
                $entrust_m->rollback();
                $this->error('撤销失败！3');
            }

            //修改记录状态为1，撤单状态
            $cancel_back=$entrustwater_m->where(array('id'=>$entrustwater_lock['id']))->save(array('cancel'=>1));
            if ($cancel_back==false){
                $entrust_m->rollback();
                $this->error('撤销失败！3');
            }

            $entrust_m->commit();
            $this->success('撤销成功！');
        }

    }

    //获取某个虚拟币的详细信息
    public function getXnbData($xnb,$marke){
        $nxb_d= D('xnb');
        $transactionrecords_m=M('transactionrecords');

        $markethouse=D('markethouse');
        //判断市场和虚拟币是否合法，不合法跳到交易市场去
        $check_back=$markethouse->criterionid($marke,$xnb);
        if ($check_back==false){
            $this->redirect('Trade/buy');
        }

        $xnb_data=$nxb_d
            ->where(['currency_xnb.id'=>$xnb])
            ->field('currency_xnb.id,
                   currency_xnb.name,
                   currency_xnb.brief, 
                   currency_xnb.imgurl,
                   sum(currency_transactionrecords.number) as smum_number,
                   avg(currency_transactionrecords.price) as avg_price
                  ')
            ->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb and currency_transactionrecords.market='.$marke)
            ->group('currency_xnb.id')
            ->find();
        $new_price=M('transactionrecords')->where(['xnb'=>$xnb,'market'=>$marke])
            ->field('
                   price as new_price
                  ')
            ->order('time desc ,id desc')
            ->find();
        $xnb_data['new_price']=$new_price['new_price'];
        $time_1= strtotime(date('Y-m-d',time()));   //今天0点的时间
        //最高最低价
        $transac_data=$transactionrecords_m
             ->where(array(
                'xnb'=>$xnb_data['id'],
                'time'=>['egt',$time_1],
                'market'=>$marke
             ))
             ->field('max(price),min(price),xnb')
             ->find();
        $xnb_data['max(price)']=$transac_data['max(price)'];
        $xnb_data['min(price)']=$transac_data['min(price)'];

            //昨天收盘价
        $oldprice=$transactionrecords_m
            ->where(array(
                'xnb'=>$xnb_data['id'],
                'time'=>[['egt',$time_1-86400],['lt',$time_1]],
                'market'=>$marke
            ))
            ->order('time desc')
            ->field('price')
            ->find();
        $xnb_data['oldprice']=$oldprice['price'];
        $new_price=M('markethouse')->where(['id'=>$marke])
            ->field('
                   standardmoney 
                  ')
            ->find();
        $xnb_data['standardmoney']=$new_price['standardmoney'];
        return $xnb_data;

    }

    //获取虚拟币的买卖挂单，接受条数
    public function getXnbEntrust($limit,$xnb_id,$marke){

        $markethouse=D('markethouse');
        //判断市场和虚拟币是否合法，不合法跳到交易市场去
        $check_back=$markethouse->criterionid($marke,$xnb_id);
        if ($check_back==false){
            $this->redirect('Trade/buy');
        }

        $entrust_m=M('entrust');
        $tentrust=array();
        //卖10，买10
        $tentrust['buy_data']=$entrust_m->where(array(
            'type'=>1,
            'xnb'=>$xnb_id,
            'market'=>$marke
        ))->field('price,sum(number) as num')->group('price')->order('price desc')->limit($limit)->select();

        $tentrust['sell_data']=$entrust_m->where(array(
             'type'=>2,
             'xnb'=>$xnb_id,
             'market'=>$marke
        ))->field('price,sum(number) as num')->limit($limit)->group('price')->order('price desc,addtime asc')->select();
        return $tentrust;
    }
    //动态获取虚拟币的买单买单
    public function returnXnbEntrust(){
        $marke=$this->strFilter(I('market'));
        $xnb_id=$this->strFilter(I('xnb'));
        $markethouse=D('markethouse');
        //判断市场和虚拟币是否合法，不合法跳到交易市场去
        $check_back=$markethouse->criterionid($marke,$xnb_id);
        if ($check_back==false){
            $this->redirect('Trade/buy');
        }
        $entrust_m=M('entrust');
        $tentrust=array();
        //卖10，买10
        $tentrust['buy_data']=$entrust_m->where(array(
            'type'=>1,
            'xnb'=>$xnb_id,
            'market'=>$marke
        ))->field('price,sum(number) as num')->group('price')->order('price desc')->limit(25)->select();
        $tentrust['sell_data']=$entrust_m->where(array(
            'type'=>2,
            'xnb'=>$xnb_id,
            'market'=>$marke
        ))->field('price,sum(number) as num')->limit(25)->group('price')->order('price desc,addtime asc')->select();
        $transactionrecords_m=M('transactionrecords');
        $tentrust['TrdeWater']=$transactionrecords_m->where(array(
            'xnb'=>$xnb_id,
            'market'=>$marke
        ))->field('time,type,price,number')->limit(25)->order('time desc')->select();
        $this->ajaxReturn($tentrust);
    }
    //获取该虚拟币交易记录 xnb /
    public function getTransaction($xnb_id,$marke,$limit){
        //数据获取方法，前25条交易记录
        $markethouse=D('markethouse');
        //判断市场和虚拟币是否合法，不合法跳到交易市场去
        $check_back=$markethouse->criterionid($marke,$xnb_id);
        if ($check_back==false){
            $this->redirect('Trade/buy');
        }
        $transactionrecords_m=M('transactionrecords');
        $transaction_data=$transactionrecords_m->where(array(
            'xnb'=>$xnb_id,
            'market'=>$marke
        ))->field('time,type,price,number')->limit($limit)->order('time desc')->select();

        return $transaction_data;
    }
    //获取虚拟币交易信息数据返回
    public function getXnbTede(){
        $market=$this->strFilter(I('market'));
        if (positive($market)!=1){    //正在判断市场是否合法
            $this->error('非法字符');
        }

        $nxb_d= D('xnb');
        $market_d= D('markethouse');
        $transactionrecords_m=M('transactionrecords');

        $market_data=$market_d->getMarkethouse($market);
        $market_data['xnb']=json_decode($market_data['xnb']);

        $xnb_data=$nxb_d
            ->where(['currency_xnb.id'=>['in',$market_data['xnb']]])
            ->field(' currency_xnb.id,
                           currency_xnb.name,
                           currency_xnb.brief,
                           currency_xnb.imgurl,
                           sum(currency_transactionrecords.number) as smum_number,
                           avg(currency_transactionrecords.price) as avg_price
                          ')
            ->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb')
//                    ->order('currency_transactionrecords.time desc')
            ->group('currency_xnb.id')
            ->select();

        $time_1= strtotime(date('Y-m-d',time()));   //今天0点的时间


        foreach ($xnb_data as $n=>&$i){
            //最高最低价
            $transac_data=$transactionrecords_m
                ->where(array(
                    'xnb'=>$i['id'],
                    'time'=>['egt',$time_1],
                    'market'=>$market
                ))
                ->field('max(price),min(price),xnb')
                ->find();
            $i['max_price']=$transac_data['max(price)'];
            $i['min_price']=$transac_data['min(price)'];

            //昨天收盘价
            $oldprice=$transactionrecords_m
                ->where(array(
                    'xnb'=>$i['id'],
                    'time'=>[['egt',$time_1-86400],['lt',$time_1]],
                    'market'=>$market
                ))
                ->order('time desc')
                ->field('price')
                ->find();
            $i['oldprice']=$oldprice['price'];

            //最新价
            $new_price=$transactionrecords_m->field('price,id,time')->where(['xnb'=>$i['id'],'market'=>$market])->order('time desc,id desc')->find();

            $i['new_price']=$new_price['price'];

        }
        $where['id']=$market;
        $pd=M('markethouse')->field('standardmoney')->where($where)->find();
        $xnb_data['pd']=$pd['standardmoney'];
        $xnb_data['mark']=$market;
        $this->ajaxReturn($xnb_data);

        //日涨跌=（最新价-昨日收盘价格）/昨日收盘价格

    }

}