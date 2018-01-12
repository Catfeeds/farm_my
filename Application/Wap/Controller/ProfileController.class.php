<?php
namespace Wap\Controller;

use Think\Controller;

class ProfileController extends WapController {
    public function __construct(){
        parent::__construct();
        if(session('user_wap.user_name')=="" && session('user_wap.id')=="" ){
            $this -> redirect('Public/login');
        }
    }
    //个人中心 我
    public function profile() {

            $user['currency_users.id']=session("user_wap.id");
            $rest=M('users')->field('
                currency_users.id as id,
                 currency_users.username as username,
                  currency_users.users as users,
                  currency_userproperty.cny as cny
            ')->join('LEFT JOIN currency_userproperty ON  currency_users.id=currency_userproperty.userid')
                ->where($user)->select();
            //用户总资产
            $money = new PropertyDataController;
            $allmoney = $money -> getUserPropert();

            $this -> assign("allmoney", $allmoney['allpropertys']);
            $this->assign("data",$rest);
            $this->display();

    }

    //意见反馈
    public function opinionTickling() {
        $this -> display();
    }
    //意见反馈提交
    public function opinionPost() {
        $uid = $this -> strFilter(I("uid")) ? $this -> strFilter(I("uid")) : "";
        $text = $this -> strFilter(I("text")) ? $this -> strFilter(I("text")) : "";
        $time = time();
        if ($uid != "" && $text != "") {
            $data['uid'] = $uid;
            $data['text'] = $text;
            $data['time'] = $time;
            $res = M("opinion") -> add($data);
            if ($res) {
                $this -> success("发送成功");
            } else {
                $this -> success("发送失败");
            }
        } else {
            $this -> error("发送失败");
        }
    }
    //意见回复
    public function opinionReply() {
        $uid = session('user_wap.id');
        $replylist = M("opinion") -> where("uid = $uid") -> field("text, reply, time") -> order("time desc") -> select();

        $this -> assign("replylist", $replylist);
        $this -> display();
    }

    //红包
    public function redPacket() {
        $this -> display();
    }

    //设置
    public function setting() {
        $user['id']=session("user_wap.id");
        $rest=M('users')->field('
               phone,yanze
            ')
            ->where($user)->select();
        $this->assign("data",$rest);
        $this->display();
    }

    //登录
    public function login() {
        $this -> display();
    }
    //找回密码
    public function retrievePassword() {
        $this -> display();
    }
    //费用说明
    public function costExplain() {
        $where['id'] = ['neq',1];
        $xnb = M('xnb')->where($where)->select();

        $where['id'] = ['eq',1];
        $xnb2 = M('xnb')->where($where)->select(); //  人民币交易

        $this->assign('xnb',$xnb);
        $this->assign('name',$xnb2[0]['name']);
        $this->assign('jc',$xnb2[0]['brief']);
        $this->assign('img',$xnb2[0]['imgurl']);
        $this ->display();
    }
    public function costExplain2()  // 虚拟币
    {
        $where['id'] = ['neq',1];
        $xnb = M('xnb')->where($where)->select();
        $this->ajaxReturn($xnb);
    }
    public function costExplain3()  // 人民币
    {
        $rebtx = M('cnyconfigure')->select();
        $this->ajaxReturn($rebtx);
    }


    //我的经理人
    public function handler() {
        $users_m=M('users');
        $allmoney=0;
        if (IS_POST){
            $qq=$this->strFilter(I('qq'));
            $wx=$this->strFilter(I('wx'));
            $back=$users_m->where(['id'=>session('user_wap')['id']])->save(['qq'=>$qq,'wx'=>$wx,'agent'=>1]);
            if ($back==false){
                $this->error('申请失败！');
                exit();
            }
            $this->success('申请成功！');
            exit();
        }
        //金额的计算
        $agent=$users_m->field('agent,invit')->where(['id'=>session('user_wap')['id']])->find();
        if ($agent['agent']==1){
            $keepmoney= M('keepmoney');
            $transactionrecords_m=M('transactionrecords');
            $allpaice=$keepmoney->where(['userid'=>session('user_wap')['id']])->field('xnb,sum(number)')->group('xnb')->select();
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
        $allmenber=$users_m->where(['pid'=>session('user_wap')['id']])->count();
        //模板地址，用户推存码
        $config=M('config');
        $config_data=$config->where(['title'=>'专属地址'])->find();


        $this->assign('config',$config_data['value']);//配置地址
        $this->assign('agent',$agent['agent']);
        $this->assign('invit',$agent['invit']);
        $this->assign('allmoney',$allmoney);
        $this->assign('allmenber',$allmenber);
        $this -> display();
    }
    //经理人已经邀请的个数
    public function handlerInviter() {
//        $users_m= M('users');
//        $transactionrecords_m=M('transactionrecords');
////        $mypid=$users_m->where(['id'=>session('user')['id']])->field('invit,pid')->find();
//        $allchildren=[];
//
//        $allchild=$users_m
//            ->where(['currency_users.pid'=>session('user_wap')['id']])
//            ->field('
//            currency_users.id as currency_users_id,
//            currency_users.addtime as currency_users_addtime,
//            currency_users.users as currency_users_users,
//            currency_keepmoney.number as currency_keepmoney_number,
//            currency_keepmoney.xnb as currency_keepmoney_xnb,
//            sum(currency_keepmoney.number)
//            ')
//            ->join('left join currency_keepmoney on currency_users.id=currency_keepmoney.childid')
//            ->group('currency_users_id,currency_keepmoney_xnb')->select();
//
//
//        foreach ($allchild as $k=>$v){
//            $allchildren[$v['currency_users_users']][]=$v;  //将相同的username放在一个数组
//        }
//
//        foreach ($allchildren as $k=>&$v){
////            if (count($v)>1){ //将每种虚拟币折合算出i
//            foreach ($v as $i=>&$u){
//                $u['allmoney']=0;
//                if ($u['currency_keepmoney_xnb']==1){
//                    $u['allmoney']+=$u['sum(currency_keepmoney.number)'];
//                }else{
//                    //不是人民币的情况下，将其转化为本位币
//                    $price=$transactionrecords_m->where(['xnb'=>$u['currency_keepmoney_xnb']])->field('id,time,price,standardmoney')->order('time desc')->find();
//                    //得到虚拟币的本位币总量
//                    $xnb_all=$price['price']*$u['sum(currency_keepmoney.number)'];
//                    if ($price['standardmoney']==1){  //判断该本位币是否是虚拟币
//                        $u['allmoney'] += $xnb_all;
//                    }else{                             //不是人民币的情况下，将其转化为人民币
//                        $price_s= $transactionrecords_m->where(['xnb'=>$price['standardmoney']])->field('id,time,price,standardmoney')->order('time desc')->find();
//                        $xnb_all=$price_s['price']*$xnb_all;
//                        $u['allmoney']+=$xnb_all;
//                    }
//                }
//                $v['allmoney']+=$u['allmoney'];
//            }
//            $v['addtime']=$v[0]['currency_users_addtime'];
//            $v['users']=$v[0]['currency_users_users'];
//        }
//        $this->assign('allchild',$allchildren);
        $this -> display();
    }
    //佣金明细
    public function handlerProfit() {
//        $keepmoney= M('keepmoney');
//        $data=$keepmoney
//            ->where(['userid'=>session('user_wap')['id']])
//            ->field('currency_keepmoney.username,
//                   currency_keepmoney.type as currency_keepmoney_type,
//                   currency_keepmoney.number as currency_keepmoney_number,
//                   currency_keepmoney.time as currency_keepmoney_time,
//                   currency_xnb.name as currency_xnb_name,
//                   currency_keepmoney.childname
//                    ')
//            ->join('left join currency_xnb on currency_keepmoney.xnb = currency_xnb.id')
//            ->order('currency_keepmoney.time desc')
//            -> limit(0, 10)
//            ->select();
//
//        $this->assign('data',$data);
        $this -> display();
    }
    public function handlerProfit_more() {
        $groupNumber = I('groupNumber') ? I('groupNumber') : 1;
        $ofset = 5;
        $keepmoney= M('keepmoney');
        $data=$keepmoney
            ->where(['userid'=>session('user_wap')['id']])
            ->field('currency_keepmoney.username,
                   currency_keepmoney.type as currency_keepmoney_type,
                   currency_keepmoney.number as currency_keepmoney_number,
                   currency_keepmoney.time as currency_keepmoney_time,
                   currency_xnb.name as currency_xnb_name,
                   currency_keepmoney.childname
                    ')
            ->join('left join currency_xnb on currency_keepmoney.xnb = currency_xnb.id')
            ->order('currency_keepmoney.time desc')
            -> limit(($ofset * ($groupNumber - 1)), $ofset)
            ->select();
        if(empty($data)) {
            $this -> ajaxReturn(2);
        } else {
            $this -> ajaxReturn($data);
        }
    }
    public function handlerInviter_more() {
        $groupNumber = I('groupNumber') ? I('groupNumber') : 1;
        $offset = 5;
        $users_m= M('users');
        $transactionrecords_m=M('transactionrecords');
//        $mypid=$users_m->where(['id'=>session('user')['id']])->field('invit,pid')->find();
        $allchildren=[];

        $allchild=$users_m
            ->where(['currency_users.pid'=>session('user_wap')['id']])
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
            $v['addtime']=date('Y-m-d H:m:s', $v[0]['currency_users_addtime']);
            $v['users']=$v[0]['currency_users_users'];
        }
        $array_allchildren = array_slice($allchildren, $offset * ($groupNumber - 1), $offset * $groupNumber);
        if (empty($array_allchildren)) {
            $this -> ajaxReturn(2);
        } else {
            $this -> ajaxReturn($array_allchildren);
        }
    }
    function logins()
    {

        $username = $this-> strFilter(I('name'),true,"账号或密码错误");
        $password = $this-> strFilter(I('pass'),true,"账号或密码错误");
        $w['users'] = $username;
        $rset = M("users")->where($w)->select();
        $wid['usreid'] = $rset[0]['id'];
        $id = $rset[0]['id'];
        $dealpwd = $rset[0]['dealpassword'];
        if (!$rset) {
            $this->error('账号密码错误！');
            return false;
        } else if ($rset[0]['password'] == jiami($password)) {

            if ($rset[0]['status'] == -1) {
                $this->error('用户已被删除');
            }else if($rset[0]['status'] == -2 && $rset[0]['loginci'] == 0){
                $this->error("您的账户密码错误次数太多已冻结，请去找回密码");
            } else {
                if ($rset[0]['status'] == 0) {
                    $this->error('用户已被禁用');
                } else {
                    //seeion
                    session('user_wap', array('user_name' => $username, 'password' => $password, 'dealpwd' => $dealpwd, 'id' => $id,'agent'=>$rset[0]['rset'], 'expire' => time() + 3600));
                    session('screennames',$rset[0]['screennames']);
                    session('truename_wap',$rset[0]['username']);
                    $why = M('userproperty')->where($wid)->select();
                    $cny=$why[0]['cny'];//用户金额
                    $sid=$rset[0]['id'];//用户id
                    $ww=$rset[0]['users'];//用户名
                    $this->assign('wo',$ww);
                    $this->assign('vo',$sid);
                    $this->assign('cny',$cny);
                    $this->resetnumber();
                    if(!$rset[0]['wxuser']){
                        $ere['wxuser']=session("openid");
                        $model=M('users')->where("id=$id")->save($ere);
                    }

                    $this->success('登录成功');
//                    redirect('index.php/Wap/Profile/profile/');
                }
            }
        } else {
            $logins=$rset[0]['loginci'];
            $idss['id']=$rset[0]['id'];
            $jialogin=$logins-1;
            if($logins>0){
                $resdata['loginci']=$jialogin;
                $shenyu=M('users')->where($idss)->save($resdata);
                $this->error("密码错误！您还有 $jialogin 次机会");
            }else if($logins<=0){
                $resdata['status']=-2;
                $shenyus=M('users')->where($idss)->save($resdata);
                $this->error("您的账户密码错误次数太多已冻结，请去找回密码");
            }

        }
    }
    function lift(){//注销
        $_SESSION = array();    //3、清楚客户端sessionid
        if(isset($_COOKIE['username'])) {   setcookie('username','',time()-3600,'/'); };
        //3、清楚客户端sessionid
        if(isset($_COOKIE['password'])) {   setcookie('password','',time()-3600,'/'); }
        $data=1;
        $this->ajaxReturn($data);
    }
}