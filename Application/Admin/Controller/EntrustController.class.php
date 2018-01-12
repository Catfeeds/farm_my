<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Think\Page;
//交易管理
class EntrustController extends AdminController {

    public function index(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('entrustwater'); // 实例化Data数据对象  date 是你的表名
        $markethouse_m=M('markethouse');
        $markedata=$markethouse_m->field('id,name')->select();
        $transactionrecords_m=M('transactionrecords');
        $xnb = M('xnb');
        $xnb_list = $xnb -> field("id, name") -> select();

        $market=$this->strFilter(I('market'))?$this->strFilter(I('market')):$markedata[0]['id'];
        $username=$this->strFilter(I('username'))?$this->strFilter(I('username')):"";
        $xnb=$this->strFilter(I('xnb'))?$this->strFilter(I('xnb')):"";

        $map['currency_entrustwater.market']=$market;
        $map['currency_entrustwater.username'] =array('like',"%".$username."%");
        $map['currency_xnb.name'] =array('like',"%".$xnb."%");

        $count = $Data->join('left join currency_markethouse on currency_entrustwater.market=currency_markethouse.id') -> join("left join currency_xnb on currency_entrustwater.xnb = currency_xnb.id")->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('market'=>$market,'username'=>$username,'xnb'=>$xnb));// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->field('
        currency_entrustwater.type,
        currency_entrustwater.id as currency_entrustwater_id,
        currency_entrustwater.oderfor,
        currency_entrustwater.username as currency_entrustwater_username,
        currency_markethouse.name as currency_markethouse_name,
        currency_entrustwater.price,
        currency_entrustwater.number,
        currency_entrustwater.allmoney,
        currency_entrustwater.addtime as currency_entrustwater_time,
        currency_entrust.id as currency_entrust_id,
        currency_xnb.name as currency_xnb_name
        ')->join('left join currency_markethouse on currency_entrustwater.market=currency_markethouse.id')
            ->join('left join currency_entrust on currency_entrustwater.id=currency_entrust.entrustwater_id')
            ->join('left join currency_xnb on currency_entrustwater.xnb=currency_xnb.id')
            ->where($map)->order('currency_entrustwater.addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
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
        //如果在成绩记录的和等于发布数则证明完成交易！；
        $this -> assign('xnb_list', $xnb_list);
        $this->assign('data',$list);// 赋值数据集,委托的数据
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('markedata',$markedata);
        $this->display(); // 输出模板

    }

    public function remove(){   //撤销订单，返回用户的手续费和本金！
//
        $oderfor=I('oderfor');
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

//        //将自己 和 该委托单，委托单用户的资产锁死,订单必须是自己
        $entrustwater_lock=$entrustwater_m->lock(true)->field('id,userid,repeats,allmoney')->where(array('oderfor'=>$oderfor))->find(); //记录锁死
        $entrust_lock = $entrust_m->lock(true)->field('allmoney,poundage,userid,standardmoney,xnb,type,number,repeats')->where(array('oderfor'=>$oderfor))->find(); //数据池锁死
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
                    $property_repeats['userid'] = session('user_wap')['id'];
                    $property_repeats['username'] = session('user_wap')['user_name'];
                    $property_repeats['xnb']=2;  //买家扣除的是市场本位币
                    $property_repeats['operatenumber'] = $pay_repeats; //操作数量（金额）
                    $property_repeats['operatetype']='撤单';
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
        $property_buy['userid']=session('user_wap')['id'];
        $property_buy['username']=session('user_wap')['user_name'];
        $property_buy['operaefront']=$property_buy_back[$poundagetype_data['brief']];  //操作之前
        $property_buy['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
        $property_buy['time']=time();
        $back=M('property')->add($property_buy); //添加流水

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
