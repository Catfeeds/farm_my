<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;
use Think\Page;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据 
 */
class PropertyController extends HomeController {
    public function __construct(){
        parent::__construct();
        if (session('user')['id']==""){
            $this->redirect('Index/index');
            exit();
        }
    }

	//首页
    public function finance(){
        $markethouse_m=M('markethouse');//市场
        $userproperty_m=M('userproperty');
        $xnb_m=M('xnb');
        $entrust_m=M('entrust');
        $repeats = 0;

        $xnb_data_sql=$xnb_m       //获取所有虚拟币
            ->field('currency_xnb.id as xnb_id,currency_xnb.brief as xnb_brief,currency_xnb.name as xnb_name,currency_markethouse.name as marke,currency_xnb.imgurl,currency_transactionrecords.price')
            ->join('left join currency_markethouse on  currency_xnb.id=currency_markethouse.standardmoney')
            ->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb and currency_transactionrecords.standardmoney=1')
            ->where('currency_xnb.status=1')
            ->order('currency_transactionrecords.time desc')
            ->select(false);
        $xnb_data=$xnb_m->table($xnb_data_sql.'a')->group('xnb_id')->select();

        $property=$userproperty_m->where(['userid'=>session('user')['id']])->field()->find();   //获取用户的所有资产！
        $repeats = $property['repeats'];
        
        //匹配该本位币的用户可用资产
        $xnb_data['allpropertys']=0;
        foreach ( $xnb_data as $k=>&$v){
            $v['property']=$property[$v['xnb_brief']];   //用户可用的资产

            if ($v['xnb_id']!=1) {  //人民币不考虑卖单
                //卖单的冻结
                $entrust_buy_data = $entrust_m->where(['userid' => session('user')['id'], 'xnb' => $v['xnb_id'], 'type' => 2])->field('sum(number)')->group('type')->find();
                if ($entrust_buy_data != "") {
                    $v['property_clos'] += $entrust_buy_data['sum(number)'];
                }
            }
            //虚拟币作为本位币的交易冻结
            $entrust_sell_data=$entrust_m->where(['userid'=>session('user')['id'],'standardmoney'=>$v['xnb_id'],'type'=>1])->field('sum(allmoney)')->group('type')->find();

            if ($entrust_sell_data!=""){
                $v['property_clos']+=$entrust_sell_data['sum(allmoney)'];
            }
            $v['price']=$v['price']?$v['price']:0;

            if ($v['xnb_id']!=1){  //人人民币不考虑折合
                #锁定资产
                $v['memory'] = M('memory')->where([
                    'user_id'=>session('user')['id'],
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

        $this->assign('repeats',floatval($repeats));
        $this->assign('xnb_data',$xnb_data);
        $this->display();
    }

    /**
     *
     * 成交查询
     *
    */

    public function demand(){
        $User =$transactionrecords_m=M('transactionrecords');
        $where=[];
        $where_o['sell']=session('user')['id'];
        $where_o['buy']=session('user')['id'];
        $where_o['_logic']='or';

        $type=$this->strFilter(I('type'));
        $xnb=$this->strFilter(I('xnb'));
        $date=(I('date'));
        $dates=(I('dates'));

        if ($type!=""){
            if ($type==1){
                unset($where_o['sell']);
            }
            if ($type==2){
                unset($where_o['buy']);
            }
            unset($where_o['_logic']);
            $this->assign('type',$type);
        }
        if ($xnb!=""){
            $this->assign('xnb',$xnb);
            $where['xnb']=$xnb;
        }
        if ($date!="" && $dates!=""){
            $this->assign('data',$date);
            $this->assign('datas',$dates);
            $where['time']=[['egt',strtotime($date)],['elt',strtotime($dates) + 86400]];
        }

        if ($type!="" || $xnb!="" || ($date!="" && $dates!="")){
            $where['_complex']=$where_o;
        }else{
            $where=$where_o;   //如果都为空就将sell，buy的条件查询
        }

        $count      = $User->where($where)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,15,$where);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where($where)->order('time')->limit($Page->firstRow.','.$Page->listRows)->select();


        $this->assign('data',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出

        $xnb_m=M('xnb');
        $xnbd_data=$xnb_m->where(['id'=>['neq',1],'status'=>['eq',1]])->field('id,name,brief,imgurl')->select(); //虚拟币列表

        $this->assign('xnb_data',$xnbd_data);

        $this->display();
    }

    //用户资产去交易的地址请求
    public function getaddr(){
        $xnb=I('xnb');
        if ($xnb!=check_number(I('xnb')))$this->error('非法参数');
        $markethouse_m=M('markethouse');//市场
        $marke= $markethouse_m->field('id,xnb')->select();
        foreach ($marke as $k=>$v){
            $v['xnb']=json_decode($v['xnb']);
            if (in_array($xnb,$v['xnb'])){
                $this->ajaxReturn(['marke'=>$v['id'],'xnb'=>$xnb]);
                break;
            };
        }
    }


//    function bi_zhong(){
//        $xnb_id=session('user.id');
//        $addtime=I('date1');//查询得第一个时间
//        $endtime=I('date2');//查询得第二个时间
//        $add_time=strtotime($addtime);
//        $end_time=strtotime($endtime);
//        $ting=I('bi_zhongl');//查询得虚拟币名称
//        if($ting==''||$add_time==''||$end_time==''){//直接点击查询没选值
//            $order=M('transactionrecords')
//                ->join('currency_entrustwater on currency_transactionrecords.buyoderfor=currency_entrustwater.oderfor')
//                ->join('currency_markethouse on currency_transactionrecords.market=currency_markethouse.id')
//                ->join('currency_xnb on currency_transactionrecords.xnb=currency_xnb.id')
//                ->where(array("currency_entrustwater.userid"=>$xnb_id,"currency_xnb.name"=>'人民币'))
//                ->select();
//            $oderfor=array();
//            foreach ($order as &$buy){
//                $oder['type']="1";
//                $oder['brief']=$buy['brief'];
//                $oder['buypoundage']=$buy['buypoundage'];
//                $oder['number']=$buy['number'];
//                $oder['price']=$buy['price'];
//                $oder['allmoney']=$buy['allmoney'];
//                if($buy['time']>=$add_time||$buy['time']<=$end_time){
//                    $oder['time']=date("Y-m-d H:m:s",$buy['time']);
//
//                }
//
//                array_push($oderfor,$oder);
//            }
//
//            $orders=M('transactionrecords')
//                ->join('currency_entrustwater on currency_transactionrecords.selloderfor=currency_entrustwater.oderfor')
//                ->join('currency_markethouse on currency_transactionrecords.market=currency_markethouse.id')
//                ->join('currency_xnb on currency_transactionrecords.xnb=currency_xnb.id')
//                ->where(array("currency_entrustwater.userid"=>$xnb_id,"currency_xnb.name"=>'人民币'))
//                ->select();
//            foreach ($orders as &$sell){
//                $oder['type']="2";
//                $oder['brief']=$sell['brief'];
//                $oder['sellpoundage']=$sell['sellpoundage'];
//                $oder['number']=$sell['number'];
//                $oder['price']=$sell['price'];
//                $oder['allmoney']=$sell['allmoney'];
//                if($sell['time']>$add_time||$sell['time']<$end_time){
//                    $oder['time']=date("Y-m-d H:i:s",$sell['time']);
//                }
//                array_push($oderfor,$oder);
//            }
//            $this->assign('oder',$oderfor);
//        }
//        session('date',array('addtime'=>$add_time,'endtime'=>$end_time,'xnb'=>$ting));
//
//        $data=M('xnb')->field('id,name,brief,imgurl')->select();
//        //用户作为买家成交记录
//        $brief_id=M('xnb')->field('id')->where(array('brief'=>$ting))->select();
//        $brief_ids=$brief_id[0]['id'];
//
//        //用户作为卖家成交记录
//
//        $this->assign('img',$data[0]['imgurl']);
//        $this->assign('xnb',$data[0]['name']);
//        $this->assign('xnb_name',$data);
//
//        $this->display('demand');
//    }
//    public function query(){
//        $xnb_id=session('user.id');
//        $data=M('xnb')->field('id,name,brief,imgurl')->select();
//        //用户作为买家成交记录
//        $type=I('value');
//        $ting=session('date.xnb');
//        $add_time=session('date.addtime');
//        $end_time=session('date.endtime');
//        $brief_id=M('xnb')->field('id')->where(array('brief'=>$ting))->select();
//        $order=M('transactionrecords')
//            ->join('currency_entrustwater on currency_transactionrecords.buyoderfor=currency_entrustwater.oderfor')
//            ->join('currency_markethouse on currency_transactionrecords.market=currency_markethouse.id')
//            ->join('currency_xnb on currency_transactionrecords.xnb=currency_xnb.id')
//            ->where("currency_entrustwater.userid=$xnb_id,
//            currency_transactionrecords.type=$type")
//            ->select();
//        $oderfor=array();
//        foreach ($order as &$buy){
//            $oder['type']="1";
//            $oder['brief']=$buy['brief'];
//            $oder['buypoundage']=$buy['buypoundage'];
//            $oder['number']=$buy['number'];
//            $oder['price']=$buy['price'];
//            $oder['allmoney']=$buy['allmoney'];
//            if($buy['time']>$add_time||$buy['time']<$end_time){
//                $oder['time']=date("Y-m-d H:i:s",$buy['time']);
//            }
//
//            array_push($oderfor,$oder);
//        }
//
//        //用户作为卖家成交记录
//        $orders=M('transactionrecords')
//            ->join('currency_entrustwater on currency_transactionrecords.selloderfor=currency_entrustwater.oderfor')
//            ->join('currency_markethouse on currency_transactionrecords.market=currency_markethouse.id')
//            ->join('currency_xnb on currency_transactionrecords.xnb=currency_xnb.id')
//            ->where("currency_entrustwater.userid=$xnb_id,
//            currency_transactionrecords.xnb=$brief_id[0],
//            currency_transactionrecords.type=$type")
//            ->select();
//        foreach ($orders as &$sell){
//            $oder['type']="2";
//            $oder['brief']=$sell['brief'];
//            $oder['sellpoundage']=$sell['sellpoundage'];
//            $oder['number']=$sell['number'];
//            $oder['price']=$sell['price'];
//            $oder['allmoney']=$sell['allmoney'];
//            if($sell['time']>$add_time||$sell['time']<$end_time){
//                $oder['time']=date("Y-m-d H:i:s",$sell['time']);
//            }
//            array_push($oderfor,$oder);
//        }
//        $this->assign('oder',$oderfor);
//        $this->assign('img',$data[0]['imgurl']);
//        $this->assign('xnb',$data[0]['name']);
//        $this->assign('xnb_name',$data);
//
//        $this->display('demand');
//    }
    
   
    /***
     *
     * 委托管理
     */
    public function commissioned(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('entrustwater'); // 实例化Data数据对象  date 是你的表名
        $markethouse_m=M('markethouse');
        $xnb_m=M('xnb');

        $xnbd_data=$xnb_m->where(['id'=>['neq',1],'status'=>['eq',1]])->field('id,name,imgurl,brief')->select(); //虚拟币列表

        $xnb=$this->strFilter(I('xnb'));
        $type=$this->strFilter(I('type'));  //买入卖出
        $cancel=$this->strFilter(I('cancel'));   //交易状态

        $date=(I('date'));
        $dates=(I('dates'));

        $where=array();

        if ($xnb!=''){
            $where['currency_entrustwater.xnb']=$xnb;
            $this->assign('xnb',$xnb);
        }

        if ($type!=""){
            $where['currency_entrustwater.type']=$type;
            $this->assign('type',$type);
        }

        if ($cancel!==""){
            $where['currency_entrustwater.cancel']=$cancel;
            $this->assign('cancel',$cancel);
        }

        if ($date!='' && $dates!=''){
            $where['currency_entrustwater.addtime']=[['egt',strtotime($date)],['elt',strtotime($dates) + 86400]];
            $this->assign('date',$date);
            $this->assign('dates',$dates);
        }



        $markedata=$markethouse_m->field('id,name,imgurl')->select();
        $transactionrecords_m=M('transactionrecords');

        $CancelController=new CancelController();
        $CancelController->page($Data, $transactionrecords_m,$where);

        $this->assign('xnb_data',$xnbd_data);
        $this->assign('markedata',$markedata);

        $this->display(); // 输出模板
    }
    /***
     *
     * 虚拟币转入界面
     */
    public function join_currency(){
        $id=session('user.id');
        $xnb_m=M('xnb');
        //虚拟币币列表
        $xnb_data=$xnb_m->field('id,name,brief,imgurl')->where(['id'=>['neq',1],'status'=>['eq',1]])->select();


        //默认虚拟币冻结和可用信息
        $xnb_prop=$this->getAdds($xnb_data[0]['id']);

        //转入记录
        $xnbrollinwater_m= M('xnbrollinwater');
        $xnbrollin_m= M('xnbrollin');

        $status=I('status');
        if (positive($status)!=1){
            $status="";
        }

        if ($status!=""){
             $where_1['currency_xnbrollinwater.status']=$status;
             $where_2['currency_xnbrollin.status']=$status;
        }

        $where_1['currency_xnbrollinwater.userid']=session('user.id');
        $where_2['currency_xnbrollin.userid']=session('user.id');

        $water_1=$xnbrollinwater_m
            ->field('currency_xnbrollinwater.addtime,currency_xnb.name,currency_xnbrollinwater.allnumber,currency_xnbrollinwater.number,currency_xnbrollinwater.addtime,currency_xnbrollinwater.status')
            ->where($where_1)
            ->join('left join currency_xnb on currency_xnbrollinwater.xnb=currency_xnb.id')
            ->order('currency_xnbrollinwater.addtime desc')
            ->select();

        $water_1=$water_1?$water_1:[];

        $water_2=$xnbrollin_m
            ->field('currency_xnbrollin.addtime,currency_xnb.name,currency_xnbrollin.allnumber,currency_xnbrollin.number,currency_xnbrollin.addtime,currency_xnbrollin.status')
            ->where($where_2)
            ->join('left join currency_xnb on currency_xnbrollin.xnb=currency_xnb.id')
            ->order('currency_xnbrollin.addtime desc')
            ->select();
        $water_2=$water_2?$water_2:[];
        $water=array_merge($water_2,$water_1);
        $this->assign('water',$water);
        $this->assign('xnb_prop',$xnb_prop);
        $this->assign('xnb_name',$xnb_data);
        $this->display();
    }

    //转入虚拟币，获取用户虚拟币信息 ,前台调用的方法
    public function case_join(){
        $xnb=$this->strFilter(I('case'));
        $data=$this->getAdds($xnb);
        $this->ajaxReturn($data);
    }
    // 当前用户资产详情
    public function getAdds($xnb){
        $id['userid']=session('user.id');
        $brief=$xnb;
        $xnb_d=D('xnb');
        //获取该虚拟币的信息
        $xnb_data=$xnb_d->getstandar($brief);
        if ($xnb_data['id']==""){
            $this->error('非法参数！');
        }

        $userproperty_m=M('userproperty');
        $xnbrollin_m=M('xnbrollin');
        $brief=$xnb_data['brief'];
        $xnbrollout_m=M('xnbrollout');
        //用户可用资产
        $property=$userproperty_m->field($brief)->where(array('userid'=>session('user.id')))->find();
        //冻结转入的用户资产
        $property_d=$xnbrollin_m->where(['userid'=>session('user.id'),'xnb'=>$xnb_data['id']])->field('sum(allnumber)')->find();
        $property_d['sum(allnumber)']=floatval($property_d['sum(allnumber)']);
        // 冻结转出资产
        $property_out=$xnbrollout_m->where(['userid'=>session('user.id'),'xnb'=>$xnb_data['id']])->field('sum(allnumber)')->find();
        $property_out['sum(allnumber)']=floatval($property_out['sum(allnumber)']);

        $data['property']=$property[$brief];
        $data['property_d']=$property_d['sum(allnumber)'];
        $data['property_out'] = $property_out['sum(allnumber)'];
        return $data;
    }



    //转入虚拟币功能的实现
    public function xnbrollin(){
        $xnb=$this->strFilter(I('xnb'));
        $number=I('number');
        $password=I('password');

        //数量验证！
        if (positive($number)!=1){
            $this->error('请输入整数位！');
            exit();
        }

        //验证密码
        if (jiami($password)!=session('user.dealpwd')){
            $this->error('密码错误！');
            exit();
        }
        //验证虚拟币
        $xnb_m=M('xnb');
        $xnb_bacn=$xnb_m->field('id,brief,inminnumber,inmaxnumber,changestatus')->where(['id'=>$xnb])->find();
        if ($xnb_bacn['id']=""){
            $this->error('不存在该虚拟币！');
            exit();
        }
        //  判断钱包转台是否允许转入
        if ($xnb_bacn['changestatus'] != 1) {
            $this->error("钱包维护,暂停转币,对您造成的不便,敬请谅解");
            exit();
        }

        // 判断当前交易量  是否在 最下交易量与最大交易量之间
        if($number < $xnb_bacn['inminnumber']){
            $this->error('单次转入量不得低于'.$xnb_bacn['inminnumber']);
            exit();
        }
        if ($xnb_bacn['inmaxnumber'] != 0) {
            if ($number > $xnb_bacn['inmaxnumber']) {
                $this->error('单次转入量不得超过' . $xnb_bacn['inmaxnumber']);
                exit();
            }
        }

        //用户的钱包地址
        $address_m = M('address');
        $adds = $address_m->field('address,id')->where(['userid' => session('user.id'), 'xnb' => $xnb])->find();;
        if ($adds['id'] == "") {  //如果该用户在该币种没有钱包就给他分配一个
            $address_m->startTrans();//开启事务
            $adds = $address_m->lock(true)->field('id,address')->where(['userid' => 0, 'xnb' => $xnb])->find();
            if ($adds['id'] == "") {  //如果钱包地址用完了，或者没有导入，则返回钱包维护文字
                $address_m->rollback(); //事务回滚
                $this->error("钱包维护,暂停转币,对您造成的不便,敬请谅解");
                exit();
            } else {  //如果还有地址，则分配一个给他
                $save_back = $address_m->where(['id' => $adds['id']])->save(['userid' => session('user.id')]);
                if ($save_back == false) {
                    $address_m->rollback(); //事务回滚
                    $this->error("钱包维护,暂停转币,对您造成的不便,敬请谅解");
                    exit();
                }

            }
        }
        //写入数据库
        $xnbrollin_m = M('xnbrollin');
        $lin_data['userid'] = session('user.id');
        $lin_data['username'] = session('user.user_name');
        $lin_data['orderfor'] = session('user')['id'] . time() . rand(1000000, 2000000);
        $lin_data['xnb'] = $xnb;
        $lin_data['addtime'] = time();
        $lin_data['allnumber'] = $number;
        $lin_data['number'] = $number;
        $lin_data['status'] = 1;
        $lin_data['addr'] = $adds['address'];
        $xnbrollin_m->add($lin_data);
        $address_m->commit();
        //成功后返回用户地址
        $this->success($adds['address']);
        exit();
    }

    /***
     *
     * 虚拟币转出
     */
    public  function out_currency(){
        $id=session('user.id');
        $status = $this->strFilter(I('type'));
        $xnb_m = M('xnb');
        //默认虚拟币冻结和可用信息
        $xnb_data=M('xnb')->field('id,name,brief,imgurl')->where(['id'=>['neq',1],'status'=>['eq',1]])->select();
        $xnb_prop=$this->getAdds($xnb_data[0]['id']);

        $xnb = I('xnb');
        if ($status!=""){
            $this->assign('type',$status);
        }
        // 所有币种信息
        $data=M('xnb')->field('id,name,brief,imgurl')->where(['id'=>['neq',1],'status'=>['eq',1]])->select();

        //转出记录
        if($status == null){   //   查询所有
            $water1= M('xnbrollout')
                ->join('currency_xnb on currency_xnbrollout.xnb=currency_xnb.id')
                ->field('
            currency_xnbrollout.id as id,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrollout.addr as currency_xnbrollout_addr,
            currency_xnbrollout.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrollout.addtime as currency_xnbrollout_addtime,
            currency_xnbrollout.status as currency_xnbrollout_status
            ')
                ->where(array('userid'=>$id))
                ->order('currency_xnbrollout.addtime desc')
                ->select();

            $water2 = M('xnbrolloutwater')
                ->join('currency_xnb on currency_xnbrolloutwater.xnb=currency_xnb.id')
                ->field('
            currency_xnb.name as currency_xnb_name,
            currency_xnbrolloutwater.addr as currency_xnbrollout_addr,
            currency_xnbrolloutwater.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrolloutwater.addtime as currency_xnbrollout_addtime,
            currency_xnbrolloutwater.status as currency_xnbrollout_status
            ')
                ->where(array('userid'=>$id))
                ->order('currency_xnbrolloutwater.addtime desc')
                ->select();
            if ($water1 == null && $water2 != null) {
                $water = $water2;
            }
            if ($water1 != null && $water2 == null) {
                $water = $water1;
            }
            if($water1 != null && $water2 != null){
                $water = array_merge($water1, $water2);
            }
        }
        if($status == 1 || $status == 2){   // 查新等待 和 审核中的
            $where['userid'] = $id;
            $where['currency_xnbrollout.status'] = $status;
            $water= M('xnbrollout')
                ->join('currency_xnb on currency_xnbrollout.xnb=currency_xnb.id')
                ->field('
            currency_xnbrollout.id as id,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrollout.addr as currency_xnbrollout_addr,
            currency_xnbrollout.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrollout.addtime as currency_xnbrollout_addtime,
            currency_xnbrollout.status as currency_xnbrollout_status
            ')
                ->where($where)
                ->order('currency_xnbrollout.addtime desc')
                ->select();

       }
       if($status == 3 || $status == 4 || $status == 5) { // 查询 成功 - 拒绝 - 撤回的      currency_xnbrolloutwater.id as id,
           $swhere['currency_xnbrolloutwater.userid'] = $id;
           $swhere['currency_xnbrolloutwater.status'] = $status;
           $water= M('xnbrolloutwater')
               ->join('currency_xnb on currency_xnbrolloutwater.xnb=currency_xnb.id')
               ->field(' 
            currency_xnb.name as currency_xnb_name,
            currency_xnbrolloutwater.addr as currency_xnbrollout_addr,
            currency_xnbrolloutwater.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrolloutwater.addtime as currency_xnbrollout_addtime,
            currency_xnbrolloutwater.status as currency_xnbrollout_status
            ')
               ->where($swhere)
               ->order('currency_xnbrolloutwater.addtime desc')
               ->select();
       }
        $this->assign('water',$water);
        $phonenuu['id']=session('user.id');
        $phone=M('users')->field('phone')->where($phonenuu)->find();
        $this->assign('phone',$phone['phone']);
        $this->assign('img',$data[0]['imgurl']);
        $this->assign('xnb',$data[0]['name']);
        $this->assign('xnb_name',$data);
        $this->assign('xnb_prop',$xnb_prop);
        $this->display();
    }

    public function get_currency(){
        $id['userid']=session('user.id');
        $brief=$this->strFilter(I('case'));
        $xnb_yong=M('userproperty')
            ->field($brief)->where($id)
            ->select();
        $allxnb=$xnb_yong[0][$brief];//可用虚拟币
        $xnb=M('xnb')
            ->where(array('brief'=>$brief))
            ->select();
        $xnb_id=$xnb[0]['id'];
        $xnb_dong=M('entrust')
            ->where(array('xnb'=>$xnb_id,$id))
            ->select();
        $datas['brief'] = $brief;
        $xid = M('xnb')->where($datas)->select();    // 虚拟币id
        $map['userid'] = array('eq',session('user.id'));
        $map['xnb'] = array('eq',$xid[0]['id']);
        $map['status'] = array('neq',3);
        $sucsss =  M('xnbrollout')->where($map)->sum('allnumber');
        if($sucsss == 0){
            $sucsss = 0;
        }
        $data['cny']=$allxnb;
;       $data['move']=$sucsss;
        $this->ajaxReturn($data);
    }
/*
 * 撤销
 * */
    public function revoked()
    {
        $userid = session('user.id');// 用户id
        $username = session('user.user_name');// 用户姓名
        $xnbrolloutid = $this->strFilter(I('id')); // 申请记录id
        $where['currency_xnbrollout.id'] = $xnbrolloutid;
        $wheres['userid'] = $userid;
        $rest = M('xnbrollout')->field('
        currency_xnbrollout.xnb as xnb,  
        currency_xnbrollout.addtime as addtime,  
        currency_xnbrollout.addr as addr,  
        currency_xnbrollout.allnumber as allnumber,
        currency_xnbrollout.number as number,  
        currency_xnbrollout.poundage as poundage,  
        currency_xnbrollout.orderfor as orderfor,
        currency_xnb.brief as brief
        ')
            ->join('LEFT JOIN currency_xnb ON currency_xnbrollout.xnb=currency_xnb.id')
            ->where($where)->find();
        // 验证当前信息
        $userids = M('xnbrollout')->field('userid')->where(['id' => $xnbrolloutid])->find();
        if ($rest != null && $userid == $userids['userid']) {
            $numbers = M('userproperty')->field($rest['brief'])->where($wheres)->find();  //查询当虚拟币的数量
            $bnumber = $numbers[$rest['brief']];
            $Data = M('userproperty');
            $where3['userid'] = $userid;
            $Data->startTrans();  //  开启事务
            $old = $Data->field($rest['brief'])->lock(true)->where($where3)->find();
            if ($old == null || $numbers == null) {
                $Data->rollback(); //  操作失败  事务回滚
                $this->error("撤销失败");
            }
            $xnbin = $Data->where($where3)->setInc($rest['brief'], $rest['allnumber']);
            if ($xnbin == null) {
                $Data->rollback(); //  操作失败  事务回滚
                $this->error("撤销失败");
            }

            $Data = M('xnbrolloutwater');
            $data = array(
                'userid' => $userid,
                'username' => $username,
                'xnb' => $rest['xnb'],
                'addtime' => $rest['addtime'],
                'addr' => $rest['addr'],
                'allnumber' => $rest['allnumber'],
                'number' => $rest['number'],
                'poundage' => $rest['poundage'],
                'status' => '5',
                'admin' => $username,
                'orderfor' => $rest['orderfor'],
            );
            $info = $Data->add($data);
            if ($info == null) {
                $Data->rollback(); //  操作失败  事务回滚
                $this->error("撤销失败");
            }
            $where2['id'] = $xnbrolloutid;
            $info2 = M('xnbrollout')->where($where2)->delete(); // S删除转出申请单行信息
            if ($info2 == null) {
                $Data->rollback(); //  操作失败  事务回滚
                $this->error("撤销失败");
            }

            $dataproperty = array(
                'userid' => $userid,
                'username' => $username,
                'xnb' => $rest['xnb'],
                'operatenumber' => $rest[0]['allnumber'],
                'operatetype' => '转出撤销',
                'operaefront' => $bnumber,
                'operatebehind' => $bnumber+$rest['allnumber'],
                'time' => time(),
            );
            $property = M('property')->add($dataproperty); // 转出撤销 -- 财产明细
            if ($property == null) {
                $Data->rollback(); //  操作失败  事务回滚
                $this->error("撤销失败");
            }
            $Data->commit();
            $this->success("撤销成功");
        } else {
            $this->error("撤销失败,非法操作！");
        }
    }

    //虚拟币转出订单生成
    public function order()
    {
        $address = $this->strFilter(I('address'));//获取钱包地址
        $number = $this->strFilter(I('number'));//获取转出数量
        $password = $this->strFilter(I('password'));//获取交易密码
        $bid = $this->strFilter(I('bid'));//获取虚拟币id
        $userid = session('user.id');// 用户id
        $username = session('user.user_name');// 用户姓名

        if($number<=0){
            $this->error('输入交易数量有误！');
            exit;
        }
        if (jiami($password) != session('user')['dealpwd']) {
            $this->error('密码错误！');
            exit;
        }
//        $phonenum = $this->strFilter(I('phonenum'));
//        $reg=new PhotomaController();
//        $phone=$reg->rmbout($phonenum);
//        if(!$phone){
//            $this->error('验证码错误！');
//            exit;
//        }
        // 当前币的名字、转出单价
        $where['id'] = $bid;
        $xnb = M('xnb')->field('name,poundage,brief,minnumber,maxnumber,totalmaxnumber')->where($where)->find();
        // 进入开始时间
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        // 进入结束时间
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        // 进入转出未审核总量
        $map1['addtime'] = array('between', array($beginToday,$endToday));
        $map1['userid'] = $userid;
        $tnumber = M('xnbrollout')->where($map1)->sum('allnumber');
        if ($tnumber == "") {
            $tnumber = 0;
        }
        // 进入转出已审核且通过的
        $map2['addtime'] = array('between', array($beginToday,$endToday));
        $map2['userid'] = $userid;
        $map2['status'] = 3;
        $ynumber = M('xnbrollinwater')->where($map1)->sum('allnumber');
        if($ynumber == ""){
            $ynumber = 0;
        }
        // 当天当前转出总量
        $znumber = $tnumber + $ynumber + $number;
        //  判断是否超出当日转出限定总量
        if( $xnb['totalmaxnumber']  != 0){
            if($znumber > $xnb['totalmaxnumber'] ){
                // 超出部分
                $cnumber = $znumber-$xnb['totalmaxnumber'];
                $this->error('抱歉，你已超出当日转出限定总量！超出'.$cnumber);
                exit;
            }
        }

        // 判断当前单笔转出量是否在 最小量 与 最大量之间
        if($number < $xnb['minnumber']){
            $this->error('单次转出量不得低于'.$xnb['minnumber']);
            exit;
        };
        if($xnb['maxnumber'] != 0){
            if($number > $xnb['maxnumber']){
                $this->error('单次转出量不得高于'.$xnb['maxnumber']);
                exit;
            };
        }
        //查询当虚拟币的数量
        $wheres['userid'] = $userid;
        $numbers = M('userproperty')->field($xnb['brief'])->where($wheres)->find();
        // 的用户当前货币的具体数据
        $bnumber = $numbers[$xnb['brief']];
        if ($bnumber>=$number){
            $poundage = $number * ($xnb['poundage']/100); // 交易的手续费
            $shengyu  = $bnumber - $number ;// 交易剩余  当前总量 - 扣除数量
            $cny = session('user')['id'].time().rand(1000000,2000000); // 订单号
            $Data = M('userproperty'); // 实例化当前用户虚拟币对象
            $value['userid'] = $userid; // 用户id
            $Data->startTrans();  //  开启事务
            $ok=$Data->field($xnb['brief'])->lock(true)->where($value)->find();  // 对当前被操作数据枷锁
            if($ok == null){
                $Data->rollback(); //  操作失败  事务回滚
                $this->error("操作失败");
            }
            $data['userid'] = $userid;// 用户id
            $returnnum= M('userproperty')->where($data)->setDec($xnb['brief'],$number);  //   修改当前用户操作的虚拟币数据
            if($returnnum == null){
                $Data->rollback();
                $this->error("操作失败");
            }
            $data2 = array(
                'userid'=> $userid,
                'username'=>$username,
                'xnb'=>$bid,
                'addtime'=>time(),
                'addr' =>$address,
                'allnumber'=>$number, // 转出总数 = 实际
                'number'=>$number - $poundage,
                'poundage'=>$poundage,
                'status'=>'1',
                'orderfor'=>$cny,
                'remarkes'=>'',
            );
            $datas = M('xnbrollout')->add($data2);  //  执行虚拟币操作
            if($datas == null){
                $Data->rollback();
                $this->error("操作失败");
            }
            $data3 = array(
                'userid'     => $userid,
                'username'   => $username,
                'xnb'         => $bid,
                'operatenumber' => $number,
                'operatetype' => '虚拟币转出',
                'operaefront' => $bnumber,
                'operatebehind' => $shengyu,
                'time' => time(),
            );
            $success = M('property')->add($data3);// 插入用户 -- 财产明细
            if($success == null){
                $Data->rollback();
                $this->error("操作失败");
            }
            $Data->commit();      // 提交事务
            $this->success("操作成功");
        }else{
            $this->error("当前货币余额不足！");
        }
     }

    /***
     *
     * 人民币充值
     */
    public function rmbrecharge(){
        $id=session('user.id');
        $cny=M('userproperty')->where(array('userid'=>$id))->select();
        $xnb=$cny[0]['cny'];//用户cny数量
        $bankname= M('bank')->field("
            currency_banktype.bankname as bankname,
            currency_bank.bankcard as bankcard
        ")->join("LEFT JOIN currency_banktype ON currency_bank.bank=currency_banktype.id")->where(array('currency_bank.userid'=>$id))->select();//用户银行
        $type=I('type') ? I('type') : "";
        $yhcard=M('rechargeapply')->field('collectionaccount')->where(array('userid'=>$id))->select();
        foreach ($yhcard as $card){
            $cards=print_r($card,1);
        }
        $model=M('bank');
        $data['userid']=session('user.id');
        $rest=$model->field('bankcard')->where($data)->select();
        $bank=M('bankreceive')->field("
            currency_banktype.bankname as bankname,
            currency_bankreceive.bankcard as bankcard,
            currency_bankreceive.payee as payee
        ")
            ->join("LEFT JOIN currency_banktype ON currency_bankreceive.bank=currency_banktype.id")
        ->where("currency_bankreceive.sort=1")->select();
        $this->assign('type',$type);
        $this->assign("shoubank",$bank);
        $this->assign('cny',$xnb);
        $this->assign('bank',$bankname);
        $this->assign('bankcard',$rest[0]);

        if($type===""){
            $order=M('rechargeapply')
                ->where(array('userid'=>$id))
                ->select();//用户订单信息
            $orderfor=M('rechargewater')
                ->where(array('userid'=>$id))
                ->select();//用户订单信息
            $order=$order?$order:[];
            $orderfor=$orderfor?$orderfor:[];
            $order2 = array_merge($order,$orderfor);
            $this->assign('order',$order2);

        }

        if($type==3){
            $orderfor=M('rechargewater')
                ->where(array('userid'=>$id,'status'=>$type))
                ->select();//用户订单信息
            $this->assign('order',$orderfor);

        }else if($type==2){
            $orderfor=M('rechargewater')
                ->where(array('userid'=>$id,'status'=>$type))
                ->select();//用户订单信息
            $this->assign('order',$orderfor);

        }else if($type==1){
            $orderfor=M('rechargeapply')
                ->where(array('userid'=>$id,'status'=>$type))
                ->select();//用户订单信息
            $this->assign('order',$orderfor);

        } else if($type===0){
            $orderfor=M('rechargeapply')
                ->where(array('userid'=>$id,'status'=>$type))
                ->select();//用户订单信息
            $this->assign('order',$orderfor);

        }

        $this->display();
    }
    //弹框
    public function bomb(){
        $id=session('user.id');
        $case=$this->strFilter(I('case'));
        $cny['money']=$this->strFilter(I('cny_money'));//充值金额
        $cny['collectionaccount']=I('shoukuan');
        $cny['payee']=I('payee');
        $cny['collbankname']=I('shoukuanbank');


        $cny['paymentcard']=$this->strFilter(I('fukuan'));
        
       
        if($case==1){
         $cny['rechargetype']=3;
        }
        if($case==2){
            $cny['rechargetype']=2;
        }
        if($case==3){
            $cny['rechargetype']=1;
        }
        if($cny['money']){
            $cny['addtime']=time();//充值申请时间
            $cny['username']=session('user.user_name');
            $cny['userid']=$id;
            $cny['status']=0;
            $cny['order']=session('user')['id'].time().rand(1000000,2000000);
        }
       $seeft= M('rechargeapply')->add($cny);
        if(!$seeft){
           
            $this->error("充值失败1");
        }
       
        $this->ajaxReturn($cny);
    }

    public function xnboutlook(){
        $type=$this->strFilter(I('type'));
        $id=$this->strFilter(I('id'));
       
        if($type==0){
            $cny=M('rechargeapply')->where(array('id'=>$id))->select();
            $this->ajaxReturn($cny[0]);
        }else{
            $cny=M('rechargewater')->where(array('id'=>$id))->select();

            $this->ajaxReturn($cny[0]);
        }
    }

    /***
     *
     * 人民币提现
     */
    public function rmbwithdrawal(){
        $bank_m=M('bank');
        $userproperty_m=M('userproperty');
        $cnyconfigure_m=M('cnyconfigure');
        $carryapplywater_m=M('carryapplywater');
        $carryapply_m=M('carryapply');
        $type=I('type');
        $type=check_number($type)!=$type ? "" : $type;
        if ($type!=""){
            $this->assign('type',$type);
        }


        $usertid['userid']=session('user.id');
        $bank_data=$bank_m->field('
            currency_bank.id as id,
            currency_banktype.bankname as bankname,
            currency_bank.name as name
        ')->join('LEFT JOIN currency_banktype ON currency_bank.bank=currency_banktype.id')->where($usertid)->select();   //用户的银行卡

        $usermoney =$userproperty_m->field('cny')->where($usertid)->find(); //用户cny资产

        $cnyconfigure_data=$cnyconfigure_m->find();    //手续费配置信息

        //用户提现列表
        $water_data=[];
        switch ($type){
            case "":
                $carryapplywater_data=$carryapplywater_m->where(['userid'=>session('user')['id']])->select();
                $carryapply_data=$carryapply_m->where(['userid'=>session('user')['id']])->select();
                $carryapplywater_data=$carryapplywater_data?$carryapplywater_data:[];
                $carryapply_data =$carryapply_data?$carryapply_data:[];
                $water_data=array_merge($carryapplywater_data,$carryapply_data);
                $water_data=$water_data;
                break;
            case 1:
                $carryapply_data=$carryapply_m->where(['userid'=>session('user')['id'],'status'=>1])->select();  //等待
                $water_data=$carryapply_data;
                break;
            case 2:
                $carryapply_data=$carryapply_m->where(['userid'=>session('user')['id'],'status'=>2])->select();   //处理中
                $water_data=$carryapply_data;
                break;
            case 3:
                $carryapply_data=$carryapplywater_m->where(['userid'=>session('user')['id'],'status'=>3])->select();  //成功
                $water_data=$carryapply_data;
                break;
            case 4:
                $carryapply_data=$carryapplywater_m->where(['userid'=>session('user')['id'],'status'=>4])->select();  //失败
                $water_data=$carryapply_data;
                break;
            case 5:
                $carryapply_data=$carryapplywater_m->where(['userid'=>session('user')['id'],'status'=>5])->select();  //撤销的
                $water_data=$carryapply_data;
                break;
        }
        $data['id']=session('user.id');
        $phone=M('users')->field('phone')->where($data)->find();

        $this->assign('phone',$phone['phone']);
        $this->assign('water',$water_data);//提现列表
        $this->assign('cnyconfigure_data',$cnyconfigure_data);  //手续费配置信息
        $this->assign('usermoney',$usermoney);//用户资产
        $this->assign('back_data',$bank_data);//用户的银行卡
        $this->display();
    }


    //用户提现人民币功能的实现
    public function record(){
        $bank=$this->strFilter(I('bank'));
        $password=$this->strFilter(I('password'));
        $money=$this->strFilter(I('money'));
        $type=$this->strFilter(I('type'));
        $phonenum=$this->strFilter(I('phonenum'));

        //金额是否合法
        if(check_number($money)!=$money){
            $this->error('非法数据1');
            exit;
        }
        $cnyconfigure_m=M('cnyconfigure');
        $confige=$cnyconfigure_m->find();
        if ($money>$confige['maxmoney'] ){
            $this->error('单次金额不得大于'.$confige['maxmoney'].'元');
        }
        if ( $money<$confige['minmoney'] ){
            $this->error('单次金额不得小于'.$confige['minmoney'].'元');
        }
        if ( $money%$confige['times']){
            $this->error('提现金额需为'.$confige['times'].'的整数倍！');
        }

        //验证提现方式
        if ($type!=1 && $type!=2){
            $this->error('非法数据2');
            exit;
        }

        $cnyconfigure_m=M('cnyconfigure');
        $config=$cnyconfigure_m->find();
        if ($type==1){    //普通提现
            $config=$config['slowpoundage'];
        }
        if ($type==2){    //快熟提现
            $config=$config['fastpoundage'];
        }


        //验证密码是否正确
        if (jiami($password) != session('user')['dealpwd']){
            $this->error('密码错误！');
            exit;
        }

        //验证手机短信
        $reg=new PhotomaController();
        $phone=$reg->rmbout($phonenum);
        if(!$phone){
            $this->error('验证码错误！');
            exit;
        }

        //验证银行是否合法！
        $bank_m=M('bank');
        $bank_back=$bank_m->where(['userid'=>session('user')['id'],'id'=>$bank])->find();
        if ($bank_back==false){
            $this->error('非法数据4');
            exit;
        }
        


        //条件满足住后开始流程！开启事务
        $userproperty_m=M('userproperty');
        $userproperty_m->startTrans();

        //余额是否足够
        $usermoney=$userproperty_m->lock(true)->where(['userid'=>session('user')['id']])->field('cny')->find();
        if ($money>$usermoney['cny']){
            $userproperty_m->rollback();
            $this->error('余额不足！');
            exit;
        }

        $p=array('egt',strtotime(date('Y-m-d',time())));

        //开始验证提现规则！获取小时判断是不是偶数

        $gtime=0;    //开始时间
        $endtime=0;   //结束时间
        $H= date("H"); //当前小时数
        if ($H%2==0){
            $gtime=mktime($H,0,0,date('m'),date('d'),date('Y'));
            $endtime=$gtime+(1000*60*60*2);
        }else{
            $gtime=mktime($H-1,0,0,date('m'),date('d'),date('Y'));
            $endtime=$gtime+(1000*60*60*2);
        }

        $carryapply_m= M('carryapply');      //生请单
        $carryapplywater_m= M('carryapplywater');

        $carry_back= $carryapply_m->where(['userid'=>session('user')['id'],'addtime'=>[['egt',$gtime],['lt',$endtime]]])->field('count(id)')->find();
        $carrywater_back= $carryapplywater_m->where(['userid'=>session('user')['id'],'addtime'=>[['egt',$gtime],['lt',$endtime]]])->field('count(id)')->find();


        if (($carry_back['count(id)']+$carrywater_back['count(id)'])>=3){
            $userproperty_m->rollback();
            $this->error('2小时内你已提现3次！请二小时后再发起申请！');
            exit;
        }

        //用户资产流水
        $property_m= M('property');
        $propertyadd['userid']=session('user')['id'];
        $propertyadd['username']=session('user')['user_name'];
        $property['xnb']=1;
        $property['operatenumber']=$money;
        $property['operatetype']='提现扣除';
        $property['operaefront']=$usermoney['cny'];
        $property['operatebehind']= $property['operaefront']-$property['operatenumber'];
        $property['time']=time();
        $property_add=$property_m->add($property);
        if($property_add==false){
            $carryapply_m->rollback();
            $this->error('撤销失败！2');
        }


        //1.扣除余额
        $dec_back=$userproperty_m->where(['userid'=>session('user')['id']])->setDec('cny',$money);
        if ($dec_back==false){
            $userproperty_m->rollback();
            $this->error('申请失败！1');
            exit;
        }
        //2.生成提现申请
        $carryapply['userid']=session('user')['id'];
        $carryapply['username']=session('user')['user_name'];
        $carryapply['allmoney']=$money;
        $carryapply['poundage']=$money*$config/100;
        $carryapply['money']=$carryapply['allmoney']-$carryapply['poundage'];
        $carryapply['addtime']=time();
        $carryapply['bankuser']=session('user')['truename'];
        $carryapply['bank']=$bank;
        $carryapply['status']=1;
        $carryapply['case']=$type;
        $carryapply['orderfor']=session('user')['id'].time().rand(1000000,2000000);
        $carr_back=$carryapply_m->add($carryapply);

        if ($carr_back==false){
            $userproperty_m->rollback();
            $this->error('申请失败！2');
            exit;
        }
        $userproperty_m->commit();
        $this->success('申请成功！');
        exit();
    }


    //撤销
    public function black(){

        $order=I('order');  //接受一个订单号
        //判断订单号是否合法

        if (check_number($order)!=$order || $order==""){
            $this->error('非法参数1');
        }
        $uid=session('user')['id'];
        $uname=session('user')['user_name'];
        $carryapply_m= M('carryapply');
        $userproperty_m= M('userproperty');
        $carryapplywater_m= M('carryapplywater');
        $property_m= M('property');

        //判断订单是否合法！并且将订单锁死
        $carryapply_m->startTrans(); //开启事务


        $carr_back=$carryapply_m->lock(true)->where(['userid'=>$uid,'orderfor'=>$order])->find();

        if ($carr_back['id']==""){
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




    //区块经理人
    function handler(){
        $users_m=M('users');
        if (IS_POST){
            $qq=$this->strFilter(I('qq'));
            $wx=$this->strFilter(I('wx'));
            $back=$users_m->where(['id'=>session('user')['id']])->save(['qq'=>$qq,'wx'=>$wx,'agent'=>1]);
            if ($back==false){
                $this->error('申请失败！');
                exit();
            }
            $this->success('申请成功！');
            exit();
        }
         //金额的计算
        $agent=$users_m->field('agent,invit')->where(['id'=>session('user')['id']])->find();
        if ($agent['agent']==1){
            $allmoney=0;
            $keepmoney= M('keepmoney');
            $transactionrecords_m=M('transactionrecords');
            $allpaice=$keepmoney->where(['userid'=>session('user')['id']])->field('xnb,sum(number)')->group('xnb')->select();
            foreach ($allpaice as $k=>$v){

                if ($v['xnb']==1){
                    $allmoney+=$v['sum(number)'];
                }else{
                    //不是人民币的情况下，将其转化为本位币
                    $price=$transactionrecords_m->where(['xnb'=>$v['xnb']])->field('id,time,price,standardmoney')->order('time desc')->find();
                    //得到虚拟币的本位币总量
                    $xnb_all=$price['price']*$v['sum(number)'];

                   if ($price['standardmoney']==1){  //判断该本位币是否是虚拟币
                       $allmoney += $xnb_all;
                   }else{                             //不是人民币的情况下，将其转化为人民币
                       $price_s= $transactionrecords_m->where(['xnb'=>$price['standardmoney']])->field('id,time,price,standardmoney')->order('time desc')->find();
                       $xnb_all=$price_s['price']*$xnb_all;
                       $allmoney+=$xnb_all;
                   }
                }
            }
        }
        //人数的计算
//        $mypid=$users_m->where(['id'=>session('user')['id']])->field('invit,pid')->find();
         $allmenber=$users_m->where(['pid'=>session('user')['id']])->count();
        //模板地址，用户推存码
        $config=M('config');
        $config_data=$config->where(['title'=>'专属地址'])->find();


        #红包提成的总数
        $bonus_deduct_m = M('bonus_deduct');

        $number = $bonus_deduct_m->field('sum(number) as number')->where(['user_id'=>session('user.id')])->find();

        $this->assign('number_bonus',$number['number']);

        $this->assign('config',$config_data['value']);//配置地址
        $this->assign('agent',$agent['agent']);
        $this->assign('invit',$agent['invit']);
        $this->assign('allmoney',$allmoney);
        $this->assign('allmenber',$allmenber);
        $this->display();
    }

    //推荐人明细
	function handler_inviter(){
        $users_m= M('users');
        $transactionrecords_m=M('transactionrecords');
//        $mypid=$users_m->where(['id'=>session('user')['id']])->field('invit,pid')->find();
        $allchildren=[];

        $allchild=$users_m
            ->where(['currency_users.pid'=>session('user')['id']])
            ->field('
            currency_users.id as currency_users_id,
            currency_users.addtime as currency_users_addtime,
            currency_users.users as currency_users_users,
            currency_keepmoney.number as currency_keepmoney_number,
            currency_keepmoney.xnb as currency_keepmoney_xnb,
            sum(currency_keepmoney.number)
            ')
            ->join('left join currency_keepmoney on currency_users.id=currency_keepmoney.childid')
            ->group('currency_users_id,currency_keepmoney_xnb')->select();


        foreach ($allchild as $k=>$v){
            $allchildren[$v['currency_users_users']][]=$v;  //将相同的username放在一个数组
        }

        foreach ($allchildren as $k=>&$v){
//            if (count($v)>1){ //将每种虚拟币折合算出i
                foreach ($v as $i=>&$u){
                    $u['allmoney']=0;
                    if ($u['currency_keepmoney_xnb']==1){
                        $u['allmoney']+=$u['sum(currency_keepmoney.number)'];
                    }else{
                        //不是人民币的情况下，将其转化为本位币
                        $price=$transactionrecords_m->where(['xnb'=>$u['currency_keepmoney_xnb']])->field('id,time,price,standardmoney')->order('time desc')->find();
                        //得到虚拟币的本位币总量
                        $xnb_all=$price['price']*$u['sum(currency_keepmoney.number)'];
                        if ($price['standardmoney']==1){  //判断该本位币是否是虚拟币
                            $u['allmoney'] += $xnb_all;
                        }else{                             //不是人民币的情况下，将其转化为人民币
                            $price_s= $transactionrecords_m->where(['xnb'=>$price['standardmoney']])->field('id,time,price,standardmoney')->order('time desc')->find();
                            $xnb_all=$price_s['price']*$xnb_all;
                            $u['allmoney']+=$xnb_all;
                        }
                    }
                    $v['allmoney']+=$u['allmoney'];
                }
            $v['addtime']=$v[0]['currency_users_addtime'];
            $v['users']=$v[0]['currency_users_users'];
        }
        $this->assign('allchild',$allchildren);
		$this->display();
	}

    //佣金明细
	function handler_profit(){
        $keepmoney= M('keepmoney');
        $data=$keepmoney
            ->where(['userid'=>session('user')['id']])
            ->field('currency_keepmoney.username,
                   currency_keepmoney.type as currency_keepmoney_type,
                   currency_keepmoney.number as currency_keepmoney_number,
                   currency_keepmoney.time as currency_keepmoney_time,
                   currency_xnb.name as currency_xnb_name,
                   currency_keepmoney.childname
                    ')
            ->join('left join currency_xnb on currency_keepmoney.xnb = currency_xnb.id')
            ->order('currency_keepmoney.time desc')
            ->select();


        $this->assign('data',$data);
		$this->display();
	}

    /**
     * 红包提成明细
     */
	function bonus_info(){


        $bonus_deduct_m = M('bonus_deduct');

        $where = ['currency_users.id'=>session('user.id')];

        $count = $bonus_deduct_m->where($where)->count();

        $page = new Page($count,15);

        $page = $page->show();

        $data = $bonus_deduct_m ->where($where)
            ->field('currency_users.users,currency_bonus_deduct.*')
            ->join('left join currency_users  on currency_users.id = currency_bonus_deduct.user_id')
            ->select();

        $this->assign('page',$page);

        $this->assign('data',$data);

	    $this->display();
    }



    //二维码的请求地址
    public function getcode(){
        $users=M('users');
        $agent=$users->field('invit')->where(['id'=>session('user')['id']])->find();
        vendor("phpqrcode.phpqrcode");
        $text='http://www.qkl.com/'.$agent['invit'];
        $outfile = false;
        $level = QR_ECLEVEL_L;
        $size = 3;
        $margin = 0;
        $saveandprint=false;
        \QRcode::png($text,$outfile,$level,$size,$margin,$saveandprint);
    }
    public function geturl(){
        $i=check_number(I('id'));
        //验证参数，如果非法就跳交易控制器
        if (positive($i)!=1){
            $this->redirect('Trade/buy');
            exit();
        }
        $markethouse_m=M('markethouse');
        $makr_data=$markethouse_m->field('id,xnb')->find();

        foreach ($makr_data as $k=>$v){

        }
        
    }



}
