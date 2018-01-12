<?php
namespace Wap\Controller;

use Think\Controller;

class RegisterController extends WapController {
    //用户注册
    public function Register() {


        if (!empty(session('onethink_home.invit'))){
            $this->assign('pid',session('onethink_home.invit'));
        }


        $this -> display();
    }
    
    //  虚拟币转出生成验证码
    function smss()  
    {
        $phone = $this->strFilter(I('phone_num'));
        //获取手机号
        $mobile = $phone;//$_POST['phone_num'];
        if (empty($mobile)) {
            $this->error("手机号码不能为空");
            exit();
        }
        $host = "http://sms.market.alicloudapi.com";
        $path = "/singleSendSms";
        $method = "GET";
        $mobile_code = $this->random(6, 1);
        session("phone", array("mobile" => $mobile, "mobile_code" => $mobile_code));
        $appcode = "f0a8a55f3c0042859fc4cd4cf7037e7f";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "ParamString={\"no\":\"$mobile_code\"}&RecNum=$phone&SignName=冰火科技&TemplateCode=SMS_71185231";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $jie = curl_exec($curl);
        $add = strstr($jie, '{"success":true}');
        session('send_code', null);
        if ($add) {
            session("yanzenci", 0);
            session("phone", array("mobile" => $mobile, "mobile_code" => $mobile_code));
            $date['status'] = 1;
            $this->success("短信已发送");
        } else {
            $this->error("短信发送失败，请刷新页面后重试");
            $date['status'] = 0;
        }
    }

    //   转出验证码判断时
    function rmbout2($mobile_code){
        $code = $this->random(6, 1);
        session('send_code', $code);
        $sessioncode = session("phone.mobile_code");
        if ( session("yanzenci") <= 5) {
            if ($mobile_code != $sessioncode or empty($mobile_code)) {
                $serf = session("yanzenci") + 1;
                session("yanzenci", $serf);
                $this->error("手机验证码输入错误");
            } else {
                session("phone", array("mobile" => null, "mobile_code" => null));
                session("yanzenci", null);
                return true;
            }
        } else {
            $this->error("验证码失效请重新获取验证码");
        }
    }
    
    
    function sms($phonee="",$mimi="",$yanzheng_num=""){

        $phone=$this->strFilter(I('phone_num'))?$this->strFilter(I('phone_num')):$phonee;
        $mi=$this->strFilter(I('cipher_code'))?$this->strFilter(I('cipher_code')):$mimi;
        $yanzheng_num=$this->strFilter(I('yanzheng_num'))?$this->strFilter(I('yanzheng_num')):$yanzheng_num;
        if(check_verify($yanzheng_num,"")){
            $host = "http://sms.market.alicloudapi.com";
            $path = "/singleSendSms";
            $method = "GET";
            $mobile_code = $this->random(6,1);
            $appcode = "f0a8a55f3c0042859fc4cd4cf7037e7f";
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys = "ParamString={\"no\":\"$mobile_code\"}&RecNum=$phone&SignName=冰火科技&TemplateCode=SMS_71185231";
            $bodys = "";
            $url = $host . $path . "?" . $querys;
//获取手机号
            $mobile = $phone;//$_POST['phone_num'];
//获取验证码
            $send_code = $mi;//$_POST['cipher_code'];
//生成的随机数
            if(empty($mobile)){
                $this->error("手机号码不能为空");
                exit();
            }
            $session=session('send_code');
//            if(empty($session) or $send_code!=$session){
//                $this->error("请求超时，请刷新页面后重试");
//                exit();
//            }
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            if (1 == strpos("$".$host, "https://"))
            {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            $jie=curl_exec($curl);

            $add=strstr($jie,'{"success":true}');
            session('send_code',null);
            if($add){
                session("yanzenci",5);
                session("phone",array("mobile"=>$mobile,"mobile_code"=>$mobile_code));
                $this->success("短信已发送");
            }else{

                $this->error("短信发送失败，请刷新页面后重试");
            }
        }else{
            $this->error("请输入正确验证码");
        }

    }
    function random($length = 6 , $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }
    function yaoqianma($serf){
        $modelserf=M('users')->field('invit')->select();
        foreach($modelserf as $v){
            if($v['invit']==$serf){
                $serf=$this->random(8);
                $this->yaoqianma($serf);
            }
        }
        return $serf;
    }
    function reg(){
        $code=$this->random(6,1);
        session('send_code',$code);
        $sessionmobile=session('phone.mobile');
        $sessioncode=session('phone.mobile_code');
        $mobile=$this->strFilter(I('phone_num'));
        $mobile_code=$this->strFilter(I('cipher_code'));
        $REG="/^1(3|4|5|7|8)\d{9}$/";
        $REGold=preg_match($REG,$mobile);
        if($REGold==1) {
            $cishu=session("yanzenci");
            if ($cishu<=5){
                if ($mobile != $sessionmobile or $mobile_code != $sessioncode or empty($mobile) or empty($mobile_code)) {
//               exit('手机验证码输入错误。');
                    $serf= $cishu-1;
                    session("yanzenci",$serf);
                    $this->error("手机验证码输入错误");
                } else {
                    session("phone", array("mobile" => null, "mobile_code" => null));
                    session("yanzenci",null);
                    $this->success("手机号验证成功");
                }
            }else{
                $this->error("验证码失效请重新获取验证码");
            }
        }else{
            $this->error("手机号不正确，必须11位手机格式数字");
        }
    }
    function zhuce(){
        $model=M('users');
        $username=$this->strFilter(I('phone_num'));
        //登录密码
        $cipher_code=$this->strFilter(I('cipher_code'));
        $confirm_code=$this->strFilter(I('confirm_code'));
        $yaoqing_code_num=$this->strFilter(I('yaoqing_code_num'));
        //交易密码
        $set_trade_pw=$this->strFilter(I('set_trade_pw'));
        $reset_trade_pw=$this->strFilter(I('reset_trade_pw'));
        $data['username']=$this->strFilter(I('real_name'));
        $data['idcard']=$this->strFilter(I('id_num'));
        $yanzheng_num=$this->strFilter(I('yanzheng_num'));

        //邮箱
        $email_name=I('email_name');
        $email_password=$this->strFilter(I('email_password'));
        $re_email_password=$this->strFilter(I('re_email_password'));
        $yaoqing_code_email=$this->strFilter(I('yaoqing_code_email'));
        if($username=="" && $username==null && $email_name=="" && $email_name==null){
            $this->error("账号不可为空");
        }
        if($username!="" && $username!=null && $email_name=="" && $email_name==null){
            $phone['users']=$username;
            $redata=$model->where($phone)->select();
            if($redata){
                $this->error("用户已存在，请登录");
            }else{
                $REG="/^1(3|4|5|7|8)\d{9}$/";
                $REGuser=preg_match($REG,$username);
                $REGpwd="/^[a-zA-Z]\w{5,17}$/";
                $REGold=preg_match($REGpwd,$cipher_code);
                $REGnew=preg_match($REGpwd,$confirm_code);
                $REGdeal="/^\d{6}$/";
                $REGdealold=preg_match($REGdeal,$set_trade_pw);
                $REGdealnew=preg_match($REGdeal,$reset_trade_pw);
                if($REGuser==1 && $REGold==1 && $REGnew==1 && $REGdealold==1 && $REGdealnew==1){
                    if($REGold==$REGnew){
                        if($REGdealold==$REGdealnew){
                            $data['users']=$username;
                            $data['password']=jiami($cipher_code);
                            $data['dealpassword']=jiami($set_trade_pw);
                            $data['yanze']=-1;
                            $data['addtime']=time();
                            $serf=$this->random(6);
                            $sserf=$this->yaoqianma($serf);
                            $data['invit']=$sserf;
                            $where['invit']=$yaoqing_code_num;
                            $data['wxuser']=session("openid");
                            $data['pid'] = session('onethink_home.invit');
                            $pid=$model->field('id')->where($where)->select();
                            if ($pid){
                                $data['pid']=$pid[0]['id'];
                            }
                            $redata=$model->add($data);
                            $id['id']=$redata;
                            $detail = $model->where($id)->select();
                            $wallet=M('userproperty');
                            $addone['userid']=$redata;
                            $addone['username']=$detail[0]['users'];
                            $resultr=$wallet->add($addone);
                            if ($redata){
                                session('user_wap', array('user_name' => $username, 'password' => jiami($cipher_code), 'dealpwd' => jiami($set_trade_pw), 'id' => $redata, 'expire' => time() + 3600));
                                $this->success("注册成功");
                            }else{
                                $this->error("注册失败");
                            }
                        }
                    }
                }
            }

        }else if($username=="" && $username==null && $email_name!="" && $email_name!=null) {
            $phone['users'] = $email_name;
            $redata = $model->where($phone)->select();
            if ($redata) {
                $this->error("用户已存在，请登录");
            } else {
                $REG = "/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
                $REGuser = preg_match($REG, $email_name);
                $REGpwd = "/^[a-zA-Z]\w{5,17}$/";
                $REGold = preg_match($REGpwd, $email_password);
                $REGnew = preg_match($REGpwd, $re_email_password);
                $REGdeal = "/^\d{6}$/";
                $REGdealold = preg_match($REGdeal, $set_trade_pw);
                $REGdealnew = preg_match($REGdeal, $reset_trade_pw);
                if ($REGuser == 1 && $REGold == 1 && $REGnew == 1 && $REGdealold == 1 && $REGdealnew == 1) {
                    if ($REGold == $REGnew) {
                        if ($REGdealold == $REGdealnew) {
                            $data['users'] = $email_name;
                            $data['password'] = jiami($email_password);
                            $data['dealpassword'] = jiami($set_trade_pw);
                            $data['addtime']=time();
                            $data['yanze'] = -1;
                            $serf = $this->random(6);
                            $sserf = $this->yaoqianma($serf);
                            $data['invit'] = $sserf;
                            $where['invit'] = $yaoqing_code_email;
                            $pid = $model->field('id')->where($where)->select();
                            if ($pid) {
                                $data['pid'] = $pid[0]['id'];
                            }
                            $redata = $model->add($data);
                            $id['id'] = $redata;
                            $detail = $model->where($id)->select();
                            $wallet = M('userproperty');
                            $addone['userid'] = $redata;
                            $addone['username'] = $detail[0]['users'];
                            $resultr = $wallet->add($addone);
                            if ($resultr) {
                                $this->success("注册成功");
                            } else {
                                $this->error("注册失败");
                            }
                        }
                    }
                }
            }
        }
    }
    function yanzhengma(){
        $Verify = new \Think\Verify();
        $Verify->entry();
    }
    function regemail(){
        $email=I('email');
        $yanzheng_num=$this->strFilter(I('yanzheng_num'));
        $REG="/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
        $REGold=preg_match($REG,$email);
        if(check_verify($yanzheng_num,"")) {
            if ($REGold == 1) {
                $ss = $this->random(6, 1);
                session("email_code", array("code" => $ss));
                if (sendemail("$email", '【农畜交易平台】邮箱验证码', "您好，您的验证码是 $ss ，请输入验证码比对后进行下一步")) {
                    $this->success("验证码已发送");
                } else {
                    $this->error("邮箱未验证");
                }
            } else {
                $this->error("请输入正确的邮箱");
            }
        }else{
            $this->error("请输入正确的验证码");
        }
    }
    function regemailnamezhaohui(){
        $model=M('users');
        $email=I('email');
        $data['users']=$email;
        $rest=$model->where($data)->select();
        if ($rest){
            $this->success("用户可使用");
        }else{
            $this->error("用户不在");
        }
    }
    function pwdpind(){
        $data['users']=$this->strFilter(I('phone_num'));
        $code=$this->strFilter(I('cipher_code'));
        $yanzheng_num=$this->strFilter(I('yanzheng_num'));
        $model=M('users');
        $rest=$model->field('phone')->where($data)->select();
        if($rest){
            $phone=$rest[0]['phone'];
            $this->sms($phone,$code,$yanzheng_num);
        }else{
            $this->error("该用户不存在");
        }
    }
    function emailreue(){
        $email=$this->strFilter(I('emailnum'));
        $redata=session("email_code.code");
        if ($redata!=null && $email==$redata){
            $this->success("邮箱验证成功");
        }else{
            $this->error("邮箱验证失败");
        }
    }
    function returnpwd(){
        $model=M('users');
        $username=$this->strFilter(I('phone_num'));
        //登录密码
        $cipher_code=$this->strFilter(I('new_pw'));
        $confirm_code=$this->strFilter(I('re_new_pw'));
        //邮箱
        $email_name=I('email_name');
        if($username=="" && $username==null && $email_name=="" && $email_name==null){
            $this->error("账号不可为空");
        }
        if($username!="" && $username!=null && $email_name=="" && $email_name==null){
            $REG="/^1(3|4|5|7|8)\d{9}$/";
            $REGuser=preg_match($REG,$username);
            $REGpwd="/^[a-zA-Z]\w{5,17}$/";
            $REGold=preg_match($REGpwd,$cipher_code);
            $REGnew=preg_match($REGpwd,$confirm_code);
            if($REGuser==1 && $REGold==1 && $REGnew==1 ){
                if($REGold==$REGnew){
                    $data['users']=$username;
                    $seft=$model->where($data)->select();
                    if($seft) {
                        $where['password'] = 0;
                        $model->where($data)->save($where);
                        $date['password'] = jiami($cipher_code);
                        $date['loginci'] = 6;
                        $date['status'] = 1;
                        $redata = $model->where($data)->save($date);
                        if ($redata) {
                            $this->success("找回密码成功");
                        } else {
                            $this->error("找回密码失败");
                        }
                    }else{
                        $this->error("该用户不存在");
                    }
                }
            }
        }else if($username=="" && $username==null && $email_name!="" && $email_name!=null){
            $REG="/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
            $REGuser=preg_match($REG,$email_name);
            $REGpwd="/^[a-zA-Z]\w{5,17}$/";
            $REGold=preg_match($REGpwd,$cipher_code);
            $REGnew=preg_match($REGpwd,$confirm_code);
            if($REGuser==1 && $REGold==1 && $REGnew==1 ){
                if($REGold==$REGnew){
                    $data['users']=$email_name;
                   $seft=$model->where($data)->select();
                    if($seft){
                        $where['password']=0;
                        $model->where($data)->save($where);
                        $date['password']=jiami($cipher_code);
                        $date['loginci']=6;
                        $date['status']=1;
                        $redata=$model->where($data)->save($date);
                        if ($redata){
                            $this->success("找回密码成功");
                        }else{
                            $this->error("找回密码失败");
                        }
                    }else{
                        $this->error("该用户不存在");
                    }
                }
            }
        }
    }

    function reguser(){
        $model=M('users');
        $phone['users']=$this->strFilter(I('users'));
        $redata=$model->where($phone)->select();
        if($redata){
            $this->error("用户已存在，<a href='index.php/Wap/Profile/profile'>请登录</a>");
        }else{
            $this->success("账号可以注册");
        }

    }
}