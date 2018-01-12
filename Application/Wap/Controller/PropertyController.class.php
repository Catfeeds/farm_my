<?php
namespace Wap\Controller;

use Think\Controller;
use Wap\Model\MarkethouseModel;
use Wap\Model\PropertyModel;

class PropertyController extends WapController {

    // 访问该控制器下方法的权限验证
    public function __construct()
    {
        parent::__construct();
        if (session('user_wap.user_name') == "" && session('user_wap.id') == "") {
            $this->redirect('Public/login');
        }
   }


   //资产明细
    public function finance()
    {
        $PropertyDataController = new PropertyDataController();
        $xnb_data = $PropertyDataController->getUserPropert();
        $this->assign('xnb_data', $xnb_data);
        $this->display();
    }

    //人民币充值
    public function rmbrecharge()
    {
        $bankmodel = M('bank');
        $where['userid'] = session('user_wap.id');
        $data = $bankmodel->where($where)->select();
        $this->assign("data", $data);
        $bank = M('bankreceive')->field("
            currency_banktype.bankname as bankname,
            currency_bankreceive.bankcard as bankcard,
            currency_bankreceive.payee as payee
        ")
            ->join("LEFT JOIN currency_banktype ON currency_bankreceive.bank=currency_banktype.id")
            ->where("currency_bankreceive.sort=1")->select();
        $this->assign("shoubank", $bank);
        $this->display();
    }
    //人民币提现
    public function rmbwithdrawal()
    {
        $bank_m = M('bank');
        $userproperty_m = M('userproperty');
        $cnyconfigure_m = M('cnyconfigure');
        $usertid['userid'] = session('user_wap.id');
        $bank_data = $bank_m->field('
            currency_bank.id as id,
            currency_banktype.bankname as bankname,
            currency_bank.name as name
        ')->join('LEFT JOIN currency_banktype ON currency_bank.bank=currency_banktype.id')->where($usertid)->select();   //用户的银行卡

        $usermoney = $userproperty_m->field('cny')->where($usertid)->find(); //用户cny资产

        $cnyconfigure_data = $cnyconfigure_m->find();    //手续费配置信息

        $data['id'] = session('user_wap.id');
        $phone = M('users')->field('phone')->where($data)->find();
        $this->assign('phone', $phone['phone']);
        $this->assign('cnyconfigure_data', $cnyconfigure_data);  //手续费配置信息
        $this->assign('usermoney', $usermoney);//用户资产
        $this->assign('back_data', $bank_data);//用户的银行卡
        $this->display();
    }
    //人民币提现功能的实现
    public function rmbwithdrawal_add(){
        //用户提现人民币功能的实现
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
        if (jiami($password) != session('user_wap')['dealpwd']){
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
        $bank_back=$bank_m->where(['userid'=>session('user_wap')['id'],'id'=>$bank])->find();
        if ($bank_back==false){
            $this->error('非法数据4');
            exit;
        }

        //条件满足住后开始流程！开启事务
        $userproperty_m=M('userproperty');
        $userproperty_m->startTrans();

        //余额是否足够
        $usermoney=$userproperty_m->lock(true)->where(['userid'=>session('user_wap')['id']])->field('cny')->find();
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

        $carry_back= $carryapply_m->where(['userid'=>session('user_wap')['id'],'addtime'=>[['egt',$gtime],['lt',$endtime]]])->field('count(id)')->find();
        $carrywater_back= $carryapplywater_m->where(['userid'=>session('user_wap')['id'],'addtime'=>[['egt',$gtime],['lt',$endtime]]])->field('count(id)')->find();


        if (($carry_back['count(id)']+$carrywater_back['count(id)'])>=3){
            $userproperty_m->rollback();
            $this->error('2小时内你已提现3次！请二小时后再发起申请！');
            exit;
        }

        //用户资产流水
        $property_m= M('property');
        $propertyadd['userid']=session('user_wap')['id'];
        $propertyadd['username']=session('user_wap')['user_name'];
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
        $dec_back=$userproperty_m->where(['userid'=>session('user_wap')['id']])->setDec('cny',$money);
        if ($dec_back==false){
            $userproperty_m->rollback();
            $this->error('申请失败！1');
            exit;
        }
        //2.生成提现申请
        $carryapply['userid']=session('user_wap')['id'];
        $carryapply['username']=session('user_wap')['user_name'];
        $carryapply['allmoney']=$money;
        $carryapply['poundage']=$money*$config/100;
        $carryapply['money']=$carryapply['allmoney']-$carryapply['poundage'];
        $carryapply['addtime']=time();
        $carryapply['bankuser']=session('user_wap')['truename'];
        $carryapply['bank']=$bank;
        $carryapply['status']=1;
        $carryapply['case']=$type;
        $carryapply['orderfor']=session('user_wap')['id'].time().rand(1000000,2000000);
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

    //人民币充值申请
    public function bomm(){
        $id=session('user_wap.id');
        $case=$this->strFilter(I('case'));
        $cny['money']=$this->strFilter(I('cny_money'));//充值金额
        $reg="/(^[1-9]\d*$|^[1-9]\d*\.\d*[1-9]{1,6}$|0\.\d*[1-9]{1,6}$)/";
        $REGold=preg_match($reg,$cny['money']);
        if(!$REGold){
            $this->success("输入充值正确金额");
        }
        if($cny['money']<200){
            $this->success("充值金额不能低于200");
        }
        $shou=M('bankreceive')->field('
            currency_bankreceive.bankcard as bankcard,
            currency_bankreceive.payee as payee,
            currency_banktype.bankname as bankname
        ')->join("LEFT JOIN currency_banktype  ON currency_bankreceive.bank=currency_banktype.id")
            ->where("currency_bankreceive.sort=1")->find();
//银行卡充值记录

        $cny['collectionaccount']=$shou['bankcard'];
        $cny['collbankname']=$shou['bankname'];
        $cny['payee'] = $shou['payee'];
        $fu=M('bank')->field('
            bankcard
        ')->where("userid=$id")->find();
        $cny['paymentcard']=$fu['bankcard'];
        $cny['rechargetype']=3;
        if($cny['money']){
            $cny['addtime']=time();//充值申请时间
            $cny['username']=session('user_wap.user_name');
            $cny['userid']=$id;
            $cny['status']=0;
            $cny['order']=session('user')['id'].time().rand(1000000,2000000);
        }
        $seeft= M('rechargeapply')->add($cny);
        if(!$seeft){
            $this->success("充值失败1");
        }
        $this->ajaxReturn($cny);
    }

    //人民币提现记录
    public function rmbwithdrawal_record(){
        $carryapplywater_m=M('carryapplywater');
        $carryapply_m=M('carryapply');

        $carryapplywater_data=$carryapplywater_m->where(['userid'=>session('user_wap')['id']])->order('addtime desc,id desc')->limit(4)->select();
        $carryapply_data=$carryapply_m->where(['userid'=>session('user_wap')['id']])->order('endtime desc,id desc')->limit(4)->select();
        $carryapplywater_data=$carryapplywater_data?$carryapplywater_data:[];
        $carryapply_data =$carryapply_data?$carryapply_data:[];
            //合并体现记录和未完成的
        $water_data=array_merge($carryapplywater_data,$carryapply_data);
        
        $this->assign('water',$water_data);//提现列表
        $this->display();
    }
    //人民币提现撤销
    public function rmbwithdrawal_delete(){
        if (IS_POST){
            $order= $this->strFilter(I('orderfor'));
            $PropertyDataController = new PropertyDataController();
            $PropertyDataController->black($order);
            exit();
        }

    }

    //人民币充值记录
    public function chargerecord() {
        $start=$this->strFilter(I('start'));
        $id=session("user_wap.id");
        $order=M('rechargeapply')
            ->where(array('userid'=>$id))
            ->order("addtime desc")
            ->limit($start,4)->select();//用户订单信息
        $orderfor=M('rechargewater')
            ->where(array('userid'=>$id))
            ->order("addtime DESC")
            ->limit($start,4)->select();//用户订单信息
        $order=$order?$order:[];
        $orderfor =$orderfor?$orderfor:[];
        $water_data=array_merge($order,$orderfor);
        $water_data=$water_data?$water_data:[];
        $this->display();
    }
    public function rmbchang(){
        $start=$this->strFilter(I('start'));
        $id=session("user_wap.id");
        $order=M('rechargeapply')
            ->where(array('userid'=>$id))
            ->order("addtime desc")
            ->limit($start,4)->select();//用户订单信息
        $orderfor=M('rechargewater')
            ->where(array('userid'=>$id))
            ->order("addtime desc")
            ->limit($start,4)->select();//用户订单信息
        $order=$order?$order:[];
        $orderfor =$orderfor?$orderfor:[];
        $water_data=array_merge($order,$orderfor);
        $water_data=$water_data?$water_data:[];
        $this->ajaxReturn($water_data);
    }

    //银行卡转账状态
    public function chargeRecordState() {
        $type=$this->strFilter(I('type'));
        $where['status']=$this->strFilter(I('type'));
        $where['id']=$this->strFilter(I('id'));
        if($type==3 || $type==2){
            $order=M('rechargewater')
                ->where($where)
                ->select();//用户订单信息
            $this->assign('order',$order);
        }else if( $type==0 || $type==1){
            $order=M('rechargeapply')
                ->where($where)
                ->select();//用户订单信息
            $this->assign('order',$order);
        }
        $this -> display();
    }

    //转入虚拟币列表
    public function join_currency() {
        $data=M('xnb')->where([
            'id'=>['neq',1],
            'number_type'=>['eq',1],
            'status'=>['eq',1],
            ])->field('id,name,brief,imgurl,number_type')->select();
        $this->assign('xnb_name',$data);
        $this->display();
    }

    //转入单个虚拟币详情
    public function join_currency_detail() {
        $id['userid']=session('user_wap.id');
        $brief=$this->strFilter(I('brief'));
        $xnb_d=D('xnb');
        //获取该虚拟币的信息
        $xnb_data =$xnb_d->where(['brief'=>$brief])->find();
        if ($xnb_data['id']==""){
            $this->error('非法参数！');
        }
        $userproperty_m=M('userproperty');
        $xnbrollin_m=M('xnbrollin');
        $brief=$xnb_data['brief'];
        // 钱包地址
        $address_m= M('address');
        $adds=$address_m->field('address,id')->where(['userid'=>session('user_wap.id'),'xnb'=>$xnb_data['id']])->find();
        if ($adds['id']=="") {  //如果该用户在该币种没有钱包就给他分配一个
            $address_m->startTrans();//开启事务
            $adds2 = $address_m->lock(true)->field('id,address')->where(['userid' =>0,'xnb'=>$xnb_data['id']])->find();
            if ($adds2['id']==""){  //如果钱包地址用完了，或者没有导入，则返回钱包维护文字
                $address_m->rollback(); //事务回滚
                $this->assign('tishi',"钱包维护,暂停转币,对您造成的不便,敬请谅解!");
            }
            if($adds2['id']!=""){
                $where['id']= $adds2['id'];
                $data_save['userid'] = session('user_wap.id');
                $save_back=M('address')->where($where)->save($data_save);

                if ($save_back == false){
                    $address_m->rollback(); //事务回滚
                    $this->assign('tishi',"钱包维护,暂停转币,对您造成的不便,敬请谅解!");
                    $this->assign('message',0);
                }
            }
            $address_m->commit();
            $this->assign('address',$adds2['address']);
        }else{
            $this->assign('address',$adds['address']);
            $this->assign('message',3);
        }

        //用户可用资产;
        $maps['userid'] = session('user_wap.id');
        $xnb_yong=$userproperty_m->field($brief)->where($maps)->select();
        
        //冻结的用户资产
        $property_d=$xnbrollin_m->where(['userid'=>session('user_wap.id'),'xnb'=>$xnb_data[0]['id']])->field('sum(allnumber)')->find();
        $property_d['sum(allnumber)']=floatval($property_d['sum(allnumber)']);
        $data['property']=$xnb_yong[0][$brief]; // 用户当前币的可用资产
        $data['property_d']=$property_d['sum(allnumber)'];

        $this->assign('property',$data['property']); //冻结的用户资产
        $this->assign('property_d',$data['property_d']); //冻结的用户资产
        $this->assign('name',$xnb_data['name']);
        $this->assign('bid',$xnb_data['id']);
        $this->assign('brief',$xnb_data['brief']);
        $this -> display();
    }

    //  虚拟币转入订单生成
    public function xnbrollins(){
        $xnb=$this->strFilter(I('bid'));
        $number=$this->strFilter(I('number'));
        $password=$this->strFilter(I('password'));
        //数量验证！
        if (positive($number)!=1){
            $this->error('请输入整数位！');
            exit();
        }
        //验证密码
        if (jiami($password)!=session('user_wap.dealpwd')){
            $this->error('交易密码错误！');
            exit();
        }
        //验证虚拟币
        $xnb_m=M('xnb');
        $xnb_bacn=$xnb_m->field('id,brief,inminnumber,inmaxnumber,changestatus')->where(array('id'=>$xnb))->find();
        if ($xnb_bacn['id']=""){
            $this->error('不存在该虚拟币！');
            exit();
        }
        // 判断当前虚拟币是都允许转入
        if($xnb_bacn['changestatus'] != 1){
            $this->error("钱包维护,暂停转币,敬请谅解");
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
        $address_m= M('address');
        $adds=$address_m->field('address,id')->where(['userid'=>session('user_wap.id'),'xnb'=>$xnb])->find();
        //写入数据库
        if($adds['id'] != null){      // 度过地址不为空 则执行写入操作
            $xnbrollin_m= M('xnbrollin');
            $lin_data['userid']=session('user_wap.id');
            $lin_data['username']=session('user_wap.user_name');
            $lin_data['orderfor']=time().rand(1000000,2000000);//  session('user_wap.user_name').
            $lin_data['xnb']=$xnb;
            $lin_data['addtime']=time();
            $lin_data['allnumber']=$number;
            $lin_data['number']=$number;
            $lin_data['status']=1;
            $lin_data['addr']=$adds['address'];
            $xnbrollin_m->add($lin_data);
            $address_m->commit();
            //成功后返回用户地址
                $this->success('转入成功！');
        }
        $this->error('钱包维护,对您造成的不便,敬请谅解!');
        exit();
    }
        // 手机端 转入记录
    public function join_currency_record(){
        $id = session('user_wap.id');
        $xnb_m = M('xnb');
        $xnb=$this->strFilter(I('bid')); //  货币id
        //转入记录
        $xnbrollinwater_m = M('xnbrollinwater');
        $xnbrollin_m = M('xnbrollin');

        $datas = $xnb_m->field('id,name')->where(array('id'=>$xnb))->find(); // 当前币种的name
        $status = I('status');
        if (positive($status) != 1) {
            $status = "";
        }

        if ($status != "") {
            $where_1['currency_xnbrollinwater.status'] = $status;
            $where_2['currency_xnbrollin.status'] = $status;
        }
        if($xnb != ""){  // 当前 货币 id
            $where_1['currency_xnbrollinwater.xnb'] = $xnb;
            $where_2['currency_xnbrollin.xnb'] = $xnb;
        }
        $where_1['currency_xnbrollinwater.userid'] = $id;
        $where_2['currency_xnbrollin.userid'] = $id;

        $water_1 = $xnbrollinwater_m
            ->field('currency_xnbrollinwater.addtime,currency_xnb.name,currency_xnbrollinwater.allnumber,currency_xnbrollinwater.number,currency_xnbrollinwater.addtime,currency_xnbrollinwater.status,currency_xnbrollinwater.orderfor')
            ->where($where_1)
            ->join('left join currency_xnb on currency_xnbrollinwater.xnb=currency_xnb.id')
            ->order('currency_xnbrollinwater.addtime desc')
            ->select();
        $water_1 = $water_1 ? $water_1 : [];

        $water_2 = $xnbrollin_m
            ->field('currency_xnbrollin.addtime,currency_xnb.name,currency_xnbrollin.allnumber,currency_xnbrollin.number,currency_xnbrollin.addtime,currency_xnbrollin.status,currency_xnbrollin.orderfor')
            ->where($where_2)
            ->join('left join currency_xnb on currency_xnbrollin.xnb=currency_xnb.id')
            ->order('currency_xnbrollin.addtime desc')
            ->select();
        $water_2 = $water_2 ? $water_2 : [];
        $water = array_merge($water_2, $water_1);
        $waters = array_slice($water, 0,5);
        $this->assign('water', $waters);
        $this->assign('name', $datas['name']);  // 当前币的名字
        $this->assign('bid',$datas['id']);  // 当前币的id
        $this->display();
    }

    public function join_currency_record2(){
        $id = session('user_wap.id');
        $xnb=$this->strFilter(I('bid')); //  货币id
        $start = $this->strFilter(I('start'));
        $xnb_m = M('xnb');

        //转入记录
        $xnbrollinwater_m = M('xnbrollinwater');
        $xnbrollin_m = M('xnbrollin');
        $status = I('status');
        if (positive($status) != 1) {
            $status = "";
        }

        if ($status != "") {
            $where_1['currency_xnbrollinwater.status'] = $status;
            $where_2['currency_xnbrollin.status'] = $status;
        }
        if($xnb != ""){ // 当前 货币 id
            $where_1['currency_xnbrollinwater.xnb'] = $xnb;
            $where_2['currency_xnbrollin.xnb'] = $xnb;
        }
        $where_1['currency_xnbrollinwater.userid'] = $id;
        $where_2['currency_xnbrollin.userid'] = $id;

        $water_1 = $xnbrollinwater_m
            ->field('currency_xnbrollinwater.addtime,currency_xnb.name,currency_xnbrollinwater.allnumber,currency_xnbrollinwater.number,currency_xnbrollinwater.addtime,currency_xnbrollinwater.status,currency_xnbrollinwater.orderfor')
            ->where($where_1)
            ->join('left join currency_xnb on currency_xnbrollinwater.xnb=currency_xnb.id')
            ->order('currency_xnbrollinwater.addtime desc')
            ->select();
        $water_1 = $water_1 ? $water_1 : [];

        $water_2 = $xnbrollin_m
            ->field('currency_xnbrollin.addtime,currency_xnb.name,currency_xnbrollin.allnumber,currency_xnbrollin.number,currency_xnbrollin.addtime,currency_xnbrollin.status,currency_xnbrollin.orderfor')
            ->where($where_2)
            ->join('left join currency_xnb on currency_xnbrollin.xnb=currency_xnb.id')
            ->order('currency_xnbrollin.addtime desc')
            ->select();
        $water_2 = $water_2 ? $water_2 : [];
        $water = array_merge($water_2, $water_1);
        $waters = array_slice($water, $start+5,5);
        $this->ajaxReturn($waters);
    }

    //转出虚拟币列表
    public function out_currency() {
        $data=M('xnb')->where(['id'=>['neq',1],'status'=>['eq',1],'number_type'=>['eq',1]])->field('id,name,brief,imgurl')->select();

        $this->assign('xnb_name',$data);
        $this->display();
        exit();
    }

    //转出虚拟币详情
    public function out_currency_detail() {
        // 当前用户id
        $userid = session('user_wap.id');
        // 当前虚拟币的id
        $bid = I('id');
        // 虚拟币名称
        $name = I('name');
        $brief=$this->strFilter(I('brief'));
        // 可用虚拟币
        $maps['userid'] = $userid;
        $xnb_yong=M('userproperty')->field($brief)->where($maps)->select();
        $allxnb=$xnb_yong[0][$brief];
        //冻结虚拟币
        $map['userid'] = array('eq',$userid);
        $map['xnb'] = array('eq',$bid);
        $map['status'] = array('neq',3);
        $sucsss =  M('xnbrollout')->where($map)->sum('allnumber');
        if($sucsss == 0){
            $sucsss = 0;
        }
        //  虚拟币信息
        $mps['id'] = $bid;
        $xnb = M('xnb')->field('minnumber,maxnumber,brief,selltop,poundage')->where($mps)->find();
        // 用户电话
        $phonenuu['id']=session('user_wap.id');
        $phone=M('users')->field('phone')->where($phonenuu)->find();
        $this->assign('phone',$phone['phone']);
        $this->assign('stop',$sucsss);
        $this->assign('money',$allxnb);
        $this->assign('xnb_name',$name);
        $this->assign('xnb_id',$bid);
//        $this->assign('minnumber',$xnb['minnumber']);
//        $this->assign('maxnumber',$xnb['maxnumber']);
        $this->assign('brief',$xnb['brief']);
        $this->assign('selltop',$xnb['selltop']);
        $this->assign('poundage',$xnb['poundage']/100);
        $this -> display();
        exit();
    }
    //  虚拟币转出订单生成
    public function orders(){
        //获取钱包地址
        $address = $this->strFilter(I('address'));
        //获取转出数量
        $number = $this->strFilter(I('number'));
        //获取交易密码
        $password = $this->strFilter(I('password'));
        //获取虚拟币id
        $bid = $this->strFilter(I('bid'));
        // 用户id
        $userid = session('user_wap.id');
        // 用户姓名
        $username = session('user_wap.user_name');
        if($number<=0){
            $this->error('输入交易数量有误！');
            exit;
        }
        if (jiami($password) != session('user_wap.dealpwd')){
            $this->error('交易密码错误！');
            exit;
        }
        //  手机验证码
        $phonenum = $this->strFilter(I('yanzhengma'));
        $reg = new RegisterController();
        $phone=$reg->rmbout2($phonenum);
        if(!$phone){
            $this->error('验证码错误！');
            exit;
        }
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
        if ($xnb['maxnumber'] != 0) {
            if ($number > $xnb['maxnumber']) {
                $this->error('单次转出量不得高于' . $xnb['maxnumber']);
                exit;
            };
        }

        //  所有验证通过
        //查询当虚拟币的数量
        $wheres['userid'] = $userid;
        $numbers = M('userproperty')->field($xnb['brief'])->where($wheres)->find();
        // 的用户当前货币的具体数据
        $bnumber = $numbers[$xnb['brief']];
        // 判断执行相关操作
        if ($bnumber>=$number){
            // 交易的手续费   = 交易数量 x 手续量的百分比
            $poundage = $number * ($xnb['poundage']/100);
            // 交易剩余  = 交易前总量 - 交易数量（实际交易量 + 手续费）
            $shengyu  = $bnumber - $number ;
            // 当前订单号
            $cny = session('user_wap')['id'].time().rand(1000000,2000000);
            // 实例化当前用户虚拟币对象
            $Data = M('userproperty');
            // 用户id
            $value['userid'] = $userid;
            //  开启事务
            $Data->startTrans();
            // 对当前被操作数据枷锁
            $ok=$Data->field($xnb['brief'])->lock(true)->where($value)->find();
            if($ok == null){
                //  操作失败  事务回滚
                $Data->rollback();
                $this->error("操作失败");
            }
            //  修改当前用户操作的虚拟币数据
            // 用户id
            $data['userid'] = $userid;
            $returnnum= M('userproperty')->where($data)->setDec($xnb['brief'],$number);
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
                'remarks'=>'',
            );
            //  执行虚拟币操作
            $datas = M('xnbrollout')->add($data2);
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
            // 插入用户 -- 财产明细
            $success = M('property')->add($data3);
            if($success == null){
                $Data->rollback();
                $this->error("操作失败");
            }
            // 提交事务
            $Data->commit();
            $this->success("操作成功");
        }else{
            $this->error("当前货币余额不足！");
        }
    }

    // 手机端转出记录
    public function out_currency_record()
    {
        $userid = session('user_wap.id');
        $xnb = $this->strFilter(I('xnb'));

        //   查询所有
        //  xnbrollout表记录
        $water1 = M('xnbrollout')
            ->join('currency_xnb on currency_xnbrollout.xnb=currency_xnb.id')
            ->field('
            currency_xnbrollout.id as id,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrollout.addr as currency_xnbrollout_addr,
            currency_xnbrollout.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrollout.addtime as currency_xnbrollout_addtime,
            currency_xnbrollout.status as currency_xnbrollout_status
            ')
            ->where(array('userid' => $userid, 'xnb' => $xnb))
            ->order('currency_xnbrollout.addtime desc')
            ->select();

        //  xnbrolloutwater 表记录
        $water2 = M('xnbrolloutwater')
            ->join('currency_xnb on currency_xnbrolloutwater.xnb=currency_xnb.id')
            ->field('           
            currency_xnb.name as currency_xnb_name,
            currency_xnbrolloutwater.addr as currency_xnbrollout_addr,
            currency_xnbrolloutwater.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrolloutwater.addtime as currency_xnbrollout_addtime,
            currency_xnbrolloutwater.status as currency_xnbrollout_status
            ')
            ->where(array('userid' => $userid, 'xnb' => $xnb))
            ->order('currency_xnbrolloutwater.addtime desc')
            ->select();

        if ($water1 == null && $water2 != null) {
            $water = $water2;
        }
        if ($water1 != null && $water2 == null) {
            $water = $water1;
        }
        if ($water1 == null && $water2 == null) {
            $water = null;
        }
        if ($water1 != null && $water2 != null) {
            $water = array_merge($water1, $water2);
        }
        
        // 下拉加载
        $waters = array_slice($water, 0, 5);
        $this->assign('water', $waters);
        $this->assign('xnbid', $xnb);
        $this->display();
    }

    public function out_currency_record2()
    {
        $start = $this->strFilter(I('start'));
        $userid = session('user_wap.id');
        $xnb = $this->strFilter(I('xnb'));
        //   查询所有
        // xnbrollout 表记录
        $water1 = M('xnbrollout')
            ->join('currency_xnb on currency_xnbrollout.xnb=currency_xnb.id')
            ->field('
            currency_xnbrollout.id as id,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrollout.addr as currency_xnbrollout_addr,
            currency_xnbrollout.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrollout.addtime as currency_xnbrollout_addtime,
            currency_xnbrollout.status as currency_xnbrollout_status
            ')
            ->where(array('userid' => $userid,'xnb' => $xnb))
            ->order('currency_xnbrollout.addtime desc')
            ->select();
        // xnbrolloutwater 表记录
        $water2 = M('xnbrolloutwater')
            ->join('currency_xnb on currency_xnbrolloutwater.xnb=currency_xnb.id')
            ->field('
            currency_xnb.name as currency_xnb_name,
            currency_xnbrolloutwater.addr as currency_xnbrollout_addr,
            currency_xnbrolloutwater.allnumber as currency_xnbrollout_allnumber,
            currency_xnbrolloutwater.addtime as currency_xnbrollout_addtime,
            currency_xnbrolloutwater.status as currency_xnbrollout_status
            ')
            ->where(array('userid' => $userid,'xnb' => $xnb))
            ->order('currency_xnbrolloutwater.addtime desc')
            ->select();

        if ($water1 == null && $water2 != null) {
            $water = $water2;
        }
        if ($water1 != null && $water2 == null) {
            $water = $water1;
        }
        if ($water1 == null && $water2 == null) {
            $water = null;
        }
        if($water1 != null && $water2 != null){
            $water = array_merge($water1, $water2);
        }
        $waters = array_slice($water, $start+5,5);
        $this->ajaxReturn($waters);
    }
    //   转出虚拟币撤销
    public function revokeds()
    {
        // 用户id
        $userid = session('user_wap.id');
        // 用户姓名
        $username = session('user_wap.user_name');
        // 申请记录id
        $xnbrolloutid = $this->strFilter(I('id'));
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
        $userids = M('xnbrollout')->field('userid')->where(['id' => $xnbrolloutid])->find();
        if ($rest != null && $userids['userid'] == $userid) {
            //查询当虚拟币的数量
            $numbers = M('userproperty')->field($rest['brief'])->where($wheres)->find();
            $bnumber = $numbers[$rest['brief']];
            $Data = M('userproperty');
            $where3['userid'] = $userid;
            //  开启事务
            $Data->startTrans();
            $old = $Data->field($rest['brief'])->lock(true)->where($where3)->find();
            if ($old == null || $numbers == null) {
                //  操作失败  事务回滚
                $Data->rollback();
                $this->error("撤销失败");
            }
            $xnbin = $Data->where($where3)->setInc($rest['brief'], $rest['allnumber']);
            if ($xnbin == null) {
                //  操作失败  事务回滚
                $Data->rollback();
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
                //  操作失败  事务回滚
                $Data->rollback();
                $this->error("撤销失败");
            }
            // S删除转出申请单行信息
            $where2['id'] = $xnbrolloutid;
            $info2 = M('xnbrollout')->where($where2)->delete();
            if ($info2 == null) {
                //  操作失败  事务回滚
                $Data->rollback();
                $this->error("撤销失败");
            }

            $dataproperty = array(
                'userid' => $userid,
                'username' => $username,
                'xnb' => $rest['xnb'],
                'operatenumber' => $rest['allnumber'],
                'operatetype' => '转出撤销',
                'operaefront' => $bnumber,
                'operatebehind' => $bnumber + $rest['allnumber'],
                'time' => time(),
            );
            // 转出撤销 -- 财产明细
            $property = M('property')->add($dataproperty);
            if ($property == null) {
                //  操作失败  事务回滚
                $Data->rollback();
                $this->error("撤销失败");
            }
            $Data->commit();
            $this->success("撤销成功");
        } else {
            $this->error("撤销失败,非法操作！");
        }
    }

    //委托管理
    public function commissioned() {
        //交易市场
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('Market');
        //如果为空就返回第一个交易市场
        $market=  $market=="" ? $market_data[0]['id']:$market;

        $entrustwater_m=M('entrustwater');
        $xnb_data= $entrustwater_m
                    ->where(['currency_entrustwater.userid'=>session('user_wap.id'),'currency_entrustwater.market'=>$market])
                    ->field('currency_xnb.id,currency_xnb.name,currency_xnb.brief,currency_xnb.imgurl')
                    ->join('currency_xnb on currency_entrustwater.xnb=currency_xnb.id')
                    ->group('currency_xnb.id')
                    ->select();
        $this->assign('Market',$market);
        $this->assign('market_data',$market_data);
        $this->assign('xnb_data',$xnb_data);
        $this -> display();
    }

    public function commissioned_detail(){
        $market = $this->strFilter(I('Market'));
        $xnb    = $this->strFilter(I('xnb'));
        $markethouse_d = D('markethouse');

        //当虚拟币和市场不合法时跳到委托管理界面
        $chek_back=$markethouse_d->criterionid($market,$xnb);
        if ($chek_back!=true){
            $this->redirect('property/commissioned');
        }
        $this->display();
    }

    //成交记录
    public function demand() {
        $uid = session('user_wap.id');
        //交易区
        $market = M("markethouse") -> field("id, name") -> select();
        $marketid = I("marketid") ? I("marketid") : $market[0]['id'];
        $where_o['t.sell']=$uid;
        $where_o['t.buy']=$uid;
        $where_o['_logic']='or';
        $where['_complex'] = $where_o;
        $where['t.market'] = $marketid;
        $xnblist = M()
            -> table("currency_transactionrecords as t")
            -> join("left join currency_xnb as x on t.xnb = x.id")
            -> field('x.name, x.id, x.imgurl, x.brief')
            -> where($where)
            -> distinct(true)
            -> select();
        $this -> assign("marketid", $marketid);
        $this -> assign("market", $market);
        $this -> assign("xnblist", $xnblist);
        $this -> display();
    }

    //成交记录详情
//    public function demand_detail() {
//        $ofset = 20;
//        $uid = session('user_wap.id');
//        $xnbid = I("xnbid");
//        $where_o['buy'] = $uid;
//        $where_o['sell'] = $uid;
//        $where_o['_logic'] = 'or';
//        $where['_complex'] = $where_o;
//        $where['xnb'] = $xnbid;
//
//        $table = M("transactionrecords")
//            -> where($where)
//            -> field("buy, sell, price, number, time")
//            -> order("time desc")
//            -> limit(0, $ofset)
//            -> select();
//        $this -> assign("list", $table);
//        $this -> assign("uid", $uid);
//        $this -> display();
//    }
    //成交记录加载更多
    public function demand_detail_more() {
        $groupNumber = I("groupNumber") ? I("groupNumber") : 1;
        $ofset = 10;
        $uid = session('user_wap.id');
        $xnbid =I("xnbid");
        $where_o['buy'] = $uid;
        $where_o['sell'] = $uid;
        $where_o['_logic'] = 'or';
        $where['_complex'] = $where_o;
        $where['xnb'] = $xnbid;

        $table = M("transactionrecords")
            -> where($where)
            -> field("buy, sell, price, number, time")
            -> order("time desc")
            -> limit(($ofset * ($groupNumber - 1)), $ofset)
            -> select();
        if (empty($table)) {
            $this -> ajaxReturn(2);
        } else {
            $this -> ajaxReturn($table);
        }
    }
}