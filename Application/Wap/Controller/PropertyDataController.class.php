<?php
namespace Wap\Controller;
use Think\Controller;

class PropertyDataController extends WapController {

    //人民币提现记录数据
    public function getCarryapplywater(){
        $start=$this->strFilter(I('start'));
        $end=$this->strFilter(I('end'));

        $carryapplywater_m=M('carryapplywater');
        $carryapply_m=M('carryapply');

        $carryapply_data=$carryapply_m->where(['userid'=>session('user_wap')['id']])->order('addtime desc,id desc')->limit($start,4)->select();
        $carryapplywater_data=$carryapplywater_m->where(['userid'=>session('user_wap')['id']])->order('endtime desc,id desc')->limit($start,4)->select();

        $carryapplywater_data=$carryapplywater_data?$carryapplywater_data:[];
        $carryapply_data =$carryapply_data?$carryapply_data:[];
        //合并体现记录和未完成的
        $water_data=array_merge($carryapplywater_data,$carryapply_data);
        $water_data=$water_data?$water_data:[];
        $this->ajaxReturn($water_data);
    }
    //人民币提现撤销
    public function black($order){
        //判断订单号是否合法
        if (check_number($order)!=$order || $order==""){
            $this->error('非法参数1');
        }
        $uid=session('user_wap')['id'];
        $uname=session('user_wap')['user_name'];
        $carryapply_m= M('carryapply');
        $userproperty_m= M('userproperty');
        $carryapplywater_m= M('carryapplywater');
        $property_m= M('property');

        //判断订单是否合法！并且将订单锁死
        $carryapply_m->startTrans(); //开启事务


        $carr_back=$carryapply_m->lock(true)->where(['userid'=>$uid,'orderfor'=>$order])->find();

        if ($carr_back['id']=="" || $carr_back['userid']!=session('user_wap.id')){
            $carryapply_m->rollback();
            $this->error('非法参数2');
        }

        if ($carr_back['status']!=1){  //只能是待审核的状态下才能撤销
            $carryapply_m->rollback();
            $this->error('非法参数3');
        }

        //锁死用户资产
        $uproperty= $userproperty_m->lock(true)->field('id,cny')->where(['userid'=>$uid])->find();

        if ($uproperty['id']==""){
            $carryapply_m->rollback();
            $this->error('系统错误！请联系我们！');
        }

        //添加记录流水,删除申请
        $carr_back['status']=5; //修改为撤销状态
        $carr_back['admin']=$uname;
        $carr_back['endtime']=time();
        unset($carr_back['id']);

        $water_back=$carryapplywater_m->add($carr_back);
        $delete_back=$carryapply_m->where(['orderfor'=>$carr_back['orderfor']])->delete();
        if ($water_back==false || $delete_back==false){
            $carryapply_m->rollback();
            $this->error('撤销失败！1');
        }

        //生成用户资产流水
        $propertyadd['userid']=$uid;
        $propertyadd['username']=$uname;
        $property['xnb']=1;
        $property['operatenumber']=$carr_back['allmoney'];
        $property['operatetype']='提现返回';
        $property['operaefront']=$uproperty['cny'];
        $property['operatebehind']= $property['operaefront']+$property['operatenumber'];
        $property['time']=time();
        $property_add=$property_m->add($property);
        if($property_add==false){
            $carryapply_m->rollback();
            $this->error('撤销失败！2');
        }
        //返回用户本金
        $property_back=$userproperty_m->where(['userid'=>$uid])->setInc('cny',$carr_back['allmoney']);
        if ($property_back==false){
            $carryapply_m->rollback();
            $this->error('撤销失败！3');
        }

        $userproperty_m->commit();
        $this->success('撤销成功！');
        exit();
    }

    //委托管理数据
    public function getEntrustwater(){
        //数据检验
        $xnb= $this->strFilter(I('xnb'));
        $market= $this->strFilter(I('Market'));
        $markethouse_d=D('markethouse');
        $check_back=$markethouse_d->criterionid($market,$xnb);
        if ($check_back == false){
            $this->ajaxReturn([]);
            exit();
        }
        $where['currency_entrustwater.xnb']=$xnb;
        $where['currency_entrustwater.market']=$market;
        $where['currency_entrustwater.userid']=session('user_wap.id');
        $start= $this->strFilter(I('start'));

        //返回数据
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
            ->where($where)->order('currency_entrustwater.cancel')->limit($start,4)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
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

        $list= $list ? $list : [];
        $this->ajaxReturn($list);
    }

    //用户的资产
    public function getUserPropert(){
        $markethouse_m=M('markethouse');//市场
        $userproperty_m=M('userproperty');
        $xnb_m=M('xnb');
        $entrust_m=M('entrust');
        $standardmoney_m= M('standardmoney');

        $xnb_data_sql=$xnb_m       //获取所有虚拟币
        ->field('currency_xnb.id as xnb_id,currency_xnb.brief as xnb_brief,currency_xnb.name as xnb_name,currency_markethouse.name as marke,currency_xnb.imgurl,currency_transactionrecords.price')
            ->join('left join currency_markethouse on  currency_xnb.id=currency_markethouse.standardmoney')
            ->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb and currency_transactionrecords.standardmoney=1')  //保证最新价是本位币
            ->order('currency_xnb.id ,currency_transactionrecords.time desc')
            ->select(false);
        $xnb_data=$xnb_m->table($xnb_data_sql.'a')->group('xnb_id')->select();

        $property=$userproperty_m->where(['userid'=>session('user_wap')['id']])->field()->find();   //获取用户的所有资产！
        $xnb_data['allpropertys']=0;


        //匹配该本位币的用户可用资产
        $xnb_data['allpropertys']=0;

        foreach ( $xnb_data as $k=>&$v){
            $v['property']=$property[$v['xnb_brief']];   //用户可用的资产
            $v['price']=$v['price']?$v['price']:0;
            if ($v['xnb_id']!=1) {  //人民币不考虑卖单

                //卖单的冻结
                $entrust_buy_data = $entrust_m->where(['userid' => session('user_wap')['id'], 'xnb' => $v['xnb_id'], 'type' => 2])->field('sum(number),standardmoney')->group('type')->find();
//                var_export($entrust_buy_data);
                $v['standardmoney_b']=$entrust_buy_data['standardmoney'];
                if ($entrust_buy_data != "") {
                        //冻结的数量
                    $v['property_clos'] += $entrust_buy_data['sum(number)'];
                }
            }

            //虚拟币作为本位币的交易冻结
            $entrust_sell_data=$entrust_m->where(['userid'=>session('user_wap')['id'],'standardmoney'=>$v['xnb_id'],'type'=>1])->field('sum(allmoney)')->group('type')->find();
            if ($entrust_sell_data!=""){
                $v['property_clos']+=$entrust_sell_data['sum(allmoney)'];
            }


            if (floatval($v['property']) == "" && $v['xnb_id']!="" && $v['xnb_id']!=1 && $v['property_clos']==""){
                unset($xnb_data[$k]);
                continue;
            }


            if ($v['xnb_id']!=1){  //人人民币不考虑折合
                #锁定资产
                $v['memory'] = M('memory')->where([
                    'user_id'=>session('user_wap')['id'],
                    'xnb_id'=>$v['xnb_id']
                ])->sum('balance');

                // 计算折合
                if ($v['price']!=""){
                    $v['allproperty']+=($v['property']+$v['property_clos']+$v['memory'])*$v['price'];
                }else{

                    //与虚拟币本位币转换
                    $transactionrecords_m = M('transactionrecords');
                    //与虚拟币本位币最新成交价
                    $b_price=$transactionrecords_m->where(['xnb'=>$v['xnb_id']])->field('price,standardmoney')->order('time desc')->find();
                    //虚拟币本位币与人民币最新成交价
                    $b_price_r=$transactionrecords_m->where(['xnb'=>$b_price['standardmoney'],'standardmoney'=>1])->field('price')->order('time desc')->find();
                    $v['allproperty']+= ($v['property']+$v['property_clos'])*$b_price['price']*$b_price_r['price'];
                }
            }else{
                $v['allproperty']=$v['property']+$v['property_clos'];
            }

            $xnb_data['allpropertys']+= $v['allproperty'];
        }
        return $xnb_data;
    }


}