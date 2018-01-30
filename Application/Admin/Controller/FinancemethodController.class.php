<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Home\Model\XnbModel;
use Think\Model;
use Think\Page;

class FinancemethodController extends AdminController {

    //虚拟币转入申请 复审方法（拒绝/通过）
    public function xnbintrefuse($type=4,$text='《复审》《拒绝》'){
        
        $id=$this->strFilter(I('id'));
        if (IS_POST && $id!=""){
            $Data = M('xnbrollin'); //
            $xnbrollinwater_m = M('xnbrollinwater');
            $userproperty = M('userproperty');
            $xnb_d= new XnbModel();
            $Data->startTrans();


            $id_back=$Data->lock(true)->where(array(   //将该条申请单锁死
                'id'=>$id
            ))->find();

            $property_lock=true;    //如果是确认通过，那么将用户的资产锁死，并且添加用的
            $property_sava=true;
            $property_xnb=true;
            if($type==3){   //同意时，增加用户的资产
                $property_m=M('property');
                $property_xnb=$xnb_d->getstandar($id_back['xnb']);//获取该次充值的虚拟币信息
             
                $property_lock=$userproperty->lock(true)->where(array(
                    'userid'=>$id_back['userid']
                ))->find();
                //用户财产流水
                $property_sell_back = $property_lock;     //获取用户的财产信息
                $property_sell['userid'] = $id_back['userid'];
                $property_sell['username'] = $id_back['username'];
                $property_sell['xnb'] = $id_back['xnb'];
                $property_sell['operatenumber'] = $id_back['number']; //操作数量（金额）
                $property_sell['operatetype']='虚拟币装入';
                $property_sell['operaefront']=$property_sell_back[$property_xnb['brief']];  //操作之前
                $property_sell['operatebehind']=$property_sell['operaefront']+$property_sell['operatenumber']; //操作之后
                $property_sell['time']=time();

                $back=$property_m->add($property_sell); //添加流水
                if ($back==false){
                    $Data->rollback();
                    $this->error('审核失败！');
                    exit();
                }

                //添加资产
                $property_sava=$userproperty->where(array('userid'=>$id_back['userid']))->setInc($property_xnb['brief'],$id_back['number']);
            }
            if ($id_back['id']=="" || $property_lock==false || $property_sava == false || $property_xnb==false){
                $Data->rollback();
                $this->error('参数错误！');
                exit();
            }

            unset($id_back['id']);                               //删除原id
            $id_back['admin']=session('user_auth.username');   //添加审核人和审核时间
            $id_back['endtime']=time();
            $id_back['status']=$type;                                //拒绝状态
           
            $delet_back=$Data->where(array('id'=>$id))->delete();
            if ($delet_back==false){
                $Data->rollback();
                $this->error('审核失败！');
                exit();
            }
            $add_back=$xnbrollinwater_m->add($id_back);
            if ($add_back==false){
                $Data->rollback();
                $this->error('审核失败！');
                exit();
            }
            action_log('currency_xnbrollinwater','currency_xnbrollinwater',$id_back['orderfor'],UID,$add_back,$text.','); //第一个id修改那条数据

            $Data->commit();
            $this->success('审核成功！');
            exit();
        }

    }

    public function paging_data($Data,$type){
//        $orderfor=$this->strFilter(I('orderfor'));
//        $xnb=$this->strFilter(I('xnb'));
        //所有虚拟币
        $xnb_list = M("xnb") -> field("id, name, brief") -> where("id <> 1") -> select();
        $name=$this->strFilter(I('name'));
        $search_xnb=$this->strFilter(I('xnbid')) ? $this -> strFilter(I('xnbid')) : "";

        $map_1['currency_xnb.name']=array('like',"%".$name."%");
        $map_1['currency_xnbrollin.orderfor']=array('like',"%".$name."%");;
        $map_1['currency_xnbrollin.username'] =array('like',"%".$name."%");
        $map_1['_logic'] = 'OR';
        $map['_complex']=$map_1;
        $map['currency_xnbrollin.status'] = $type;
        if ($search_xnb != "") {
            $map['currency_xnbrollin.xnb'] = $search_xnb;
        }
        $count = $Data->where($map)
            ->join('left join currency_xnb on currency_xnbrollin.xnb=currency_xnb.id')
            ->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name, 'xnbid' => $search_xnb));// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)
            ->field('
            currency_xnb.id as currency_xnb_id,
            currency_xnb.brief,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrollin.id as currency_xnbrollin_id,
            currency_xnbrollin.allnumber,
            currency_xnbrollin.number,
            currency_xnbrollin.addr,
            currency_xnbrollin.remarks as currency_xnbrollin_remarks,
            currency_xnbrollin.addtime as currency_xnbrollin_addtime,
            currency_xnbrollin.orderfor,
            currency_xnbrollin.username,
            currency_xnbrollin.userid
            ')
            ->join('left join currency_xnb on currency_xnbrollin.xnb=currency_xnb.id')
            ->order('currency_xnbrollin.addtime desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this -> assign('xnb_list', $xnb_list);
        $this -> assign('data',$list);// 赋值数据集,委托的数据
        $this -> assign('page',$show);// 赋值分页输出
    }

    public function paging_data_out($Data,$type){
        $xnb_list = M("xnb") -> field("id, name, brief") -> where("id <> 1") -> select();
        $map=array();
        $name=$this->strFilter(I('name'));
        $search_xnb=$this->strFilter(I('xnbid')) ? $this -> strFilter(I('xnbid')) : "";

        $map_1['currency_xnbrollout.orderfor'] = array('like',"%".$name."%");;
        $map_1['currency_xnbrollout.username'] = array('like',"%".$name."%");
        $map_1['_logic'] = 'OR';
        $map['_complex'] = $map_1;
        $map['currency_xnbrollout.status'] = $type;
        if ($search_xnb != "") {
            $map['currency_xnbrollout.xnb'] = $search_xnb;
        }
        $count = $Data->where($map)
            ->join('left join currency_xnb on xnbrollout.xnb=currency_xnb.id')
            ->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name, 'xnbid' => $search_xnb));// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)
            ->field('
            currency_xnb.id as currency_xnb_id,
            currency_xnb.brief,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrollout.id as currency_xnbrollout_id,
            currency_xnbrollout.allnumber,
            currency_xnbrollout.number,
            currency_xnbrollout.addr,
            currency_xnbrollout.remarks as currency_xnbrollout_remarks,
            currency_xnbrollout.addtime as currency_xnbrollout_addtime,
            currency_xnbrollout.orderfor,
            currency_xnbrollout.username,
            currency_xnbrollout.userid,
            currency_xnbrollout.poundage
            ')
            ->join('left join currency_xnb on currency_xnbrollout.xnb=currency_xnb.id')
            ->order('currency_xnbrollout.addtime desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this -> assign('xnb_list', $xnb_list);
        $this->assign('data',$list);// 赋值数据集,委托的数据
        $this->assign('page',$show);// 赋值分页输出
    }

    //转出虚拟币的终审核
    public function xnbintrefuse_out($type=2,$text='《复审》《拒绝》'){
        $id=$this->strFilter(I('id'));
        if (IS_POST && $id!=""){
            $Data =   M('xnbrollout'); //
            $xnbrollinwater_m=M('xnbrolloutwater');
            $userproperty_m = M('userproperty');
//            $xnb_d = D('xnb');
            $xnb_d = new XnbModel();
            $Data->startTrans();

            $id_back=$Data->lock(true)->where(array(   //将该条申请单锁死
                'id'=>$id
            ))->find();

            $property_lock=true;    //如果是确认通过，那么将用户的资产锁死
            $property_sava=true;
            if($type==3){    //同意时，生成手续费记录
                //添加手续费记录
                $user['userid']=$id_back['userid'];
                $user['username']=$id_back['username'];
                $user['oderfor']=$id_back['orderfor'];
                $this->poundage(3,$id_back['poundage'],$id_back['xnb'], $Data,$user);

            }

            if ($type==4){   //当拒绝时返回用户的本金+手续费
                $property_m=M('property');

                $property_xnb=$xnb_d->getstandar($id_back['xnb']);//获取该次充值的虚拟币信息

                $property_lock=$userproperty_m->lock(true)->where(array(    //锁死用户资产
                    'userid'=>$id_back['userid']
                ))->find();

                //流水账
                $property_sell_back = $property_lock;     //获取用户的财产信息
                $property_sell['userid'] = $id_back['userid'];
                $property_sell['username'] = $id_back['username'];
                $property_sell['xnb'] = $id_back['xnb'];
                $property_sell['operatenumber'] = $id_back['allnumber']; //操作数量（金额）
                $property_sell['operatetype']='转出返回';
                $property_sell['operaefront']=$property_sell_back[$property_xnb['brief']];  //操作之前
                $property_sell['operatebehind']=$property_sell['operaefront']+$property_sell['operatenumber']; //操作之后
                $property_sell['time']=time();
                $back=$property_m->add($property_sell); //添加流水
                if ($back==false){
                    $Data->rollback();
                    $this->error('审核失败！');
                    exit();
                }
                //返回用户的手续费和本经
                $property_sava=$userproperty_m->where(array('userid'=>$id_back['userid']))->setInc($property_xnb['brief'],$id_back['allnumber']);
            }
//            var_export($id_back['id']);
//            var_export($property_lock);
//            var_export($property_sava);
            if ($id_back['id']=="" || $property_lock==false || $property_sava == false ){
                $Data->rollback();
                $this->error('参数错误！');
                exit();
            }

            unset($id_back['id']);                               //删除原id
            $id_back['admin']=session('user_auth.username');   //添加审核人和审核时间
            $id_back['endtime']=time();
            $id_back['status']=$type;                                //拒绝状态

            $delet_back=$Data->where(array('id'=>$id))->delete();
            if ($delet_back==false){
                $Data->rollback();
                $this->error('审核失败！');
                exit();
            }
            $add_back=$xnbrollinwater_m->add($id_back);
            if ($add_back==false){
                $Data->rollback();
                $this->error('审核失败！');
                exit();
            }
            //行为日志的生成
            action_log('currency_xnbrollinwater','currency_xnbrollinwater',$id_back['orderfor'],UID,$add_back,$text.','); //第一个id修改那条数据

            $Data->commit();
            $this->success('审核成功！');
            exit();
        }
    }

    //手续费
    public function poundage($type,$moeny,$xnb,$back,$user){
        $poundage_m    =M('poundage');
        $add['type']  =$type;
        $add['time'] =time();
        $add['money'] =$moeny;
        $add['xnb']=$xnb;
        $add['userid']=$user['userid'];
        $add['username']=$user['usernameusername'];
        $addback=$poundage_m->add($add);
        if ($addback==false){
            $back->rollback();
            $this->error('审核失败12');
        }
    }

    //人民币提现终审
    public function cnyintout_fuse($id,$Data,$type=4,$text='《复审》《拒绝》'){
        $carryapplywater_m=M('carryapplywater');
        $userproperty=M('userproperty');
        $Data->startTrans();
        
        $id_back = $Data->lock(true)->where(array(   //将改条记录锁死，防止数据库脏读
            'id'=>$id,
        ))->find();

        $property = $userproperty->lock(true)->where(array('userid'=>$id_back['userid']))->find();  //将用户资产锁住

        if ($id_back['id']=="" || $property['id']==""){
            $this->error('参数错误！');
            exit();
        }
        unset($id_back['id']);                              //删除原id
        $id_back['admin']=session('user_auth.username');   //添加审核人和审核时间
        $id_back['endtime']=time();
        $id_back['status']=$type;  //1通过2拒绝
        $delet_back=$Data->where(array('id'=>$id))->delete();
        if ($delet_back==false){
            $Data->rollback();
            $this->error('审核失败！1');
            exit();
        }

        $add_back=$carryapplywater_m->add($id_back);
        if ($add_back==false){
            $Data->rollback();
            $this->error('审核失败！2');
            exit();
        }

        //同意时，生成手续费记录
        if($type==3){
            $user['userid']=$id_back['userid'];
            $user['userid']=$id_back['username'];
            $user['oderfor']=$id_back['orderfor'];
            $this->poundage(4,$id_back['poundage'],1,$Data,$user);
        }

        //当拒绝时返回用户的本金+手续费
        if ($type==4){
            //用户流水
            $property_m=M('property');
            $property_sell_back=$property; //获取用户的财产信息
            $property_sell['userid']=$id_back['userid'];
            $property_sell['username']=$id_back['username'];
            $property_sell['xnb']=1;  //卖家收入的是本金币
            $property_sell['operatenumber']=$id_back['allmoney']; //操作数量（金额）
            $property_sell['operatetype']='转出返回';
            $property_sell['operaefront']=$property['cny'];  //操作之前
            $property_sell['operatebehind']=$property_sell['operaefront']+$property_sell['operatenumber']; //操作之后
            $property_sell['time']=time();
            $back=$property_m->add($property_sell); //添加流水
            if ($back==false){
                $Data->rollback();
                $this->error('审核失败！3');
                exit();
            }

            $property=$userproperty->where(array('userid'=>$id_back['userid']))->setInc('cny',$id_back['allmoney']);
            if ($property==false){
                $Data->rollback();
                $this->error('审核失败！3');
                exit();
            }
        }

        action_log('currency_carryapply','currency_carryapply',$id_back['orderfor'],UID,$id_back['id'],$text.'，'); //第一个id修改那条数据

        $Data->commit();
        $this->success('审核成功！3');
        exit();
    }


    public function cnyout_page($Data,$status){
        $name=$this->strFilter(I('name'))?$this->strFilter(I('name')):"";
        $map_1['currency_carryapply.id']=array('like',"%".$name."%");;
        $map_1['currency_carryapply.username'] =array('like',"%".$name."%");
        $map_1['currency_users.username'] =array('like',"%".$name."%");
        $map_1['currency_carryapply.orderfor'] =array('like',"%".$name."%");
        $map_1['_logic'] = 'OR';
        $map['_complex']=$map_1;
        $map['currency_carryapply.status']=$status;
        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name));// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data
            ->field('
            currency_carryapply.id as currency_carryapply_id,
            currency_carryapply.orderfor,
            currency_carryapply.username,
            currency_carryapply.userid,
            currency_carryapply.allmoney,
            currency_carryapply.poundage,
            currency_carryapply.money,
            currency_carryapply.bankaddr,
            currency_carryapply.bankuser,
            currency_carryapply.addtime,
            currency_carryapply.bank as currency_carryapply_bank,
            currency_bank.type as currency_bank_type,
            currency_bank.bank as currency_bank_bank,
            currency_bank.bankname as currency_bank_bankname,
            currency_bank.bankcard as currency_bank_bankcard,
            currency_users.username as currency_users_username,
            currency_banktype.bankname as bank_name
            ')
            ->join(' left join currency_bank on currency_carryapply.bank=currency_bank.id')
            ->join('left join currency_banktype on currency_bank.bank = currency_banktype.id')
            ->join(' left join currency_users on currency_carryapply.userid=currency_users.id')
            ->where($map)
            ->order('addtime desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('data',$list);// 赋值数据集,委托的数据
        $this->assign('page',$show);// 赋值分页输出
    }

    /****
     * @param $Data
     * @param $status
     * 人名币充值
     */
    public function cnyint_page($Data,$status){

        $name=$this->strFilter(I('name'))?$this->strFilter(I('name')):"";
//        $rechargetype=$this->strFilter(I('rechargetype'))?$this->strFilter(I('rechargetype')):"";
        $map_1['id']=array('like',"%".$name."%");;
        $map_1['username'] =array('like',"%".$name."%");
        $map_1['order'] =array('like',"%".$name."%");
        $map_1['paymentcard'] =array('like',"%".$name."%");
        $map_1['collectionaccount'] =array('like',"%".$name."%");
        $map_1['_logic'] = 'OR';
        $map['status']=$status;
        $map['_complex']=$map_1;
//        if ($rechargetype){
//            $map['rechargetype'] = $rechargetype;
//        }

        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name));// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('addtime desc')->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条

        $this->assign('data',$list);// 赋值数据集,委托的数据
        $this->assign('page',$show);// 赋值分页输出

    }
    public function cnyintint_fuse($id,$Data,$type=2,$text='《复审》《拒绝》'){
        $carryapplywater_m=M('rechargewater');
        $userproperty=M('userproperty');
        $Data->startTrans();

        $id_back=$Data->lock(true)->where(array(   //将改天记录锁死，防止数据库脏读
            'id'=>$id,
        ))->find();
        if ($id_back['id']==""){
            $this->error('参数错误！');
            exit();
        }
        unset($id_back['id']);                              //删除原id
        $id_back['admin']=session('user_auth.username');   //添加审核人和审核时间
        $id_back['endtime']=time();
        $id_back['status']=$type;  //3通过2拒绝
//        $id_back['rechargetype']=$id_back['rechargetype'];
        $id_back['allmoney']=$id_back['money'];
        $id_back['bank']=$id_back['paymentcard'];
        $id_back['bankcard']=$id_back['collectionaccount'];
//        $id_back['bankaddr']=$id_back['ss'];
        $id_back['arrivetype']=$id_back['rechargetype'];
        $delet_back=$Data->where(array('id'=>$id))->delete();
        if ($delet_back==false){
            $Data->rollback();
            $this->error('审核失败！1');
            exit();
        }

        $add_back=$carryapplywater_m->add($id_back);
        if ($add_back==false){
            $Data->rollback();
            $this->error('审核失败！2');
            exit();
        }
        $userid['userid']=$id_back['userid'];
        $rmb=$userproperty->field('cny')->where($userid)->find();
        
        $cny['cny']=$id_back['money']+$rmb['cny'];
        $rest=$userproperty->where($userid)->save($cny);
        if($rest==false){
            $Data->rollback();
            $this->error('审核失败！3');
            exit();
        }
        //同意时，生成手续费记录
        $date['userid']=$userid['userid'];
        $old=$userproperty->field('username')->where($date)->select();
        $oldqian=$old[0]['username'];
//        $data['cny']= $cny['money']+$old[0]['cny'];
        $cnymodel=M('property');
        $chong['operaefront']=$rmb['cny'];
        $chong['operatebehind']=$cny['cny'];
        $chong['operatenumber']=$id_back['money'];
        $chong['operatetype']="充值人民币";
        $chong['userid']= $userid['userid'];
        $chong['username']=$oldqian;
        $chong['xnb']=1;
        $chong['time']=time();
        $chongmodel=$cnymodel->add($chong);
        if(!$chongmodel){
            $Data->rollback();
            $this->error("审核失败4");
        }
//        action_log('currency_carryapply','currency_carryapply',$id_back['orderfor'],UID,$id_back['id'],$text.'，'); //第一个id修改那条数据

        $Data->commit();
        $this->success('审核成功！3');
        exit();
    }
}
