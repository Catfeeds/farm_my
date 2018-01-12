<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class RegisterController extends HomeController {

    public function register(){
        $code=$this->random(6,1);
        session("send_code",$code);
        $qcode=session('send_code');
        $this->assign("code",$qcode);
        $this->redata();
        $this->display();
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
    function reguser(){
        $model=M('users');
        $phone['users']=$this->strFilter(I('users'));
        $redata=$model->where($phone)->select();
        if($redata){
            $this->error("用户已存在，<a href='Index.php/Home/Index/index'>请登录</a>");
        }else{
            $this->success("账号可以注册");
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
                                session('user', array('user_name' => $username, 'password' => $data['password'], 'dealpwd' =>  $data['dealpassword'], 'id' => $redata,'agent'=>2, 'expire' => time() + 3600));
//                                session('screennames',$rset[0]['screennames']);
                                session('truename',$data['username']);
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
                                session('user', array('user_name' => $email_name, 'password' => $data['password'], 'dealpwd' =>  $data['dealpassword'], 'id' => $redata,'agent'=>2, 'expire' => time() + 3600));
//                                session('screennames',$rset[0]['screennames']);
                                session('truename',$data['username']);
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
    function confirm(){
        $yanze=I('yanze');
        $user=I('user');
        $sessionemail=session("email_code.code");
        if ($yanze==$sessionemail){
            session("email_user",array("name"=>$user));
        }else{
            $this->error("邮箱未验证");
            return false;
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
    function regemailname(){
        $model=M('users');
        $email=I('email');
        $data['users']=$email;
        $rest=$model->where($data)->select();
        if ($rest){
            $this->error("用户已存在");
        }else{
            $this->success("用户可注册");
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
    function regemail(){
        $email=I('email');
        $user=jiami($email);
        $yanzheng_num=$this->strFilter(I('yanzheng_num'));
        $REG="/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/";
        $REGold=preg_match($REG,$email);
        if(check_verify($yanzheng_num,"")) {
            if ($REGold == 1) {
                $ss = $this->random(6, 1);
                session("email_code", array("code" => $ss));
                if (send_email("$email", '【农畜商品交易品台】邮箱验证码', "您好，您的验证码是 $ss ，请输入验证码比对后进行下一步")) {
                    $this->success("验证码已发送");
                } else {
                    $this->error("验证码发送失败");
                }
            } else {
                $this->error("请输入正确的邮箱");
            }
        }else{
            $this->error("请输入正确的验证码");
        }
    }
    function findTradePw(){
        $code=$this->random(6,1);
        session("send_code",$code);
        $qcode=session('send_code');
        $this->assign("code",$qcode);
        $this->display();
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
                        $date['loginci'] = 3;
                        $date['status'] = 1;
                        $redata = $model->where($data)->save($date);
                        if ($redata) {
                            $this->success("找回密码成功");
                        } else {
                            $this->error("找回密码失败");
                        }
                    }else{
                        $this->error("用户不存在");
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
                        $this->error("用户不存在");
                    }
                }
            }
        }
    }
    function pwdemail(){
        $email=I('email');
        $yanze=$this->strFilter(I('yanze'));
        $redata=session("email_user.name");
        if ($redata!=null && jiami($email)==$redata){
            $model=M('users');
            $data['users']=$email;
             $rest= $model->where($data)->select();
           if($rest){
                if(check_verify($yanze,"")){
                    $this->success("验证成功");
                }else{
                    $this->error("请输入正确验证码");
                }
           }else{
               $this->error("该用户不存在");
           }
        }else{
            $this->error("邮箱验证失败");
        }
    }
    function is_idcard($id)
    {
        $id = strtoupper($id);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if(!preg_match($regx, $id)){
            return FALSE;
        }
        if(15==strlen($id)){
            //检查15位
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
            @preg_match($regx, $id, $arr_split);
            //检查生日日期是否正确
            $dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth)){
                return FALSE;
            }else{
                return TRUE;
            }
        }else{
            //检查18位
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth)) {
                //检查生日日期是否正确
                return FALSE;
            }else{
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $sign = 0;
                for ( $i = 0; $i < 17; $i++ ) {
                    $b = (int) $id{$i};
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n = $sign % 11;
                $val_num = $arr_ch[$n];
                if ($val_num != substr($id,17, 1)) {
                    return FALSE;
                } //phpfensi.com
                else{
                    return TRUE;
                }
            }
        }

    }
    function regidcard(){
        $idcard=I('idcard');
        $ids=$this->is_idcard($idcard);
        if($ids){
            $this->success("身份证有效");
        }else{
            $this->error("身份证无效，请输入正确身份证号码");
        }
    }
    
}