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

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class SafetyController extends SessionController    {

    function indexqiu(){
        $ifsession=parent::__construct();
        if($ifsession){
            $this->ajaxReturn(true);
        }else{
            $this->ajaxReturn(false);
        }
    }
	//首页
    public function __construct(){
        parent::__construct();
        if(session('user.user_name') && session('user.id') ){
            return true;
        }else{
            $this->redirect('Index/index');
            exit();
        }
    }
//
    public function safeCenter(){
        $this->asd();
    }
    function asd(){
        $ifsession=parent::__construct();
        if($ifsession){
            $user['id']=session('user.id');
            $model=M('users');
            $redata=$model->where($user)->select();
            $this->assign("_list",$redata);
            $this->assign("name",$redata[0]['username']);
            $this->assign("status",$redata[0]['yanze']);
            $code=$this->random(6,1);
            session("send_code",$code);
            $qcode=session('send_code');
            $this->assign("code",$qcode);
            $bankmodel=M('bank');
            $where['userid']=session('user.id');
            $where['currency_bank.type']=array('eq',1);
            $data=$bankmodel->field('
                currency_banktype.bankname as bank,
                currency_bank.name as name,
                currency_bank.bankprov as bankprov,
                 currency_bank.bancity as bancity,
                 currency_bank.bankname as bankname,
                 currency_bank.bankcard as bankcard,
                  currency_bank.addtime as addtime,
                    currency_bank.id as id
            ')
                ->join('LEFT JOIN currency_banktype ON currency_bank.bank=currency_banktype.id')
                ->where($where)->select();
            $this->assign("data",$data);
            $typemodel=M('banktype');
            $bankwhere['status']=array('eq',1);
            $bankdata=$typemodel->field('bankname')->where($bankwhere)->select();
            $this->assign("type",$bankdata);
            $phone=$redata[0]['phone'];
            $yantime=M('realnamewater');
            $yan['userid']=session('user.id');
            $yan['status']=array("gt",0);
            $retest=$yantime->where($yan)->select();
            if($retest){
                $this->assign("time",$retest[0]['endtime']);
                $this->assign("photo",$retest);
            }
            $shenhe=M('realname');
            $yanshen['userid']=session('user.id');
            $retshen=$shenhe->where($yanshen)->select();
            $this->assign("shenhe",$retshen);
            if($phone){
                $mphone =substr_replace($phone, '****', 3, 4);
                $this->assign("phone",$mphone);
                $this->assign("shiphone",$phone);
            }else{
                $this->assign("phone","未进行手机认证");
            }
            $this->redata();
            $this->display();
        } else{
            redirect('index.php/Home/Index/index/');
        }
        
    }
    //修改密码
    public function changePw(){
        $this->asd();
    }
    //银行卡绑定
    public function cardBind(){
        $this->asd();
    }
    //银行卡管理
    public function cardManage(){
        $this->asd();
    }
    //找回密码
    public function findPw(){
        $this->asd();
    }
    //实名认证
    public function nameIdenty(){
        $this->asd();
    }
    //支付绑定
    public function payBind(){
        $bankmodel=M('bank');
        $where['userid']=session('user.id');
        $where['type']=array('eq',2);
        $data=$bankmodel->where($where)->select();
        $this->assign("bankdata",$data);
        $where['type']=array('eq',3);
        $dataa=$bankmodel->where($where)->select();
        $this->assign("bankwechat",$dataa);
        $this->asd();
    }
    //手机绑定
    public function phoneBind(){
        $this->asd();
    }
    //照片认证
    public function photoIdenty(){
        $this->asd();
    }
    /***
     *
     *修改登录密码
     */

   public function updatalogin(){
       $user['id']=session('user.id');
       $oldpwd=$this->strFilter(I('enter_pw'));
       $newpwd=$this->strFilter(I('new_enter_pw'));
       $newpwdtrue=$this->strFilter(I('re_new_enter_pw'));
       $model=M('users');
       $redata=$model->where($user)->select();
       $REG="/^[a-zA-Z]\w{5,17}$/";
       $REGold=preg_match($REG,$oldpwd);
       $REGnew=preg_match($REG,$newpwd);
       $REGnewtrue=preg_match($REG,$newpwdtrue);
       if($REGold==1  && $REGnew==1 && $REGnewtrue==1 ){
           if(jiami($oldpwd)==$redata[0]['password']){
               if ($newpwd==$newpwdtrue){
                   $data['password']=jiami($newpwd);
                   $save=$model->where($user)->save($data);
                   if ($save){
                       session(null);
                       $this->success("修改密码成功");
                   }else{
                       $this->error("修改密码失败");
                   }
               }else{
                   $this->error("两次密码不一致");
               }
           }else{
               $this->error("登录密码有误");
           }
       }else{
           $this->error("密码格式有误");
       }

   }
    /***
     *
     *修改交易密码
     */
    public function updatadeal(){
        $user['id']=session('user.id');
        $oldpwd=$this->strFilter(I('trade_pw'));
        $newpwd=$this->strFilter(I('new_trade_pw'));
        $newpwdtrue=$this->strFilter(I('re_new_trade_pw'));
        $model=M('users');
        $redata=$model->where($user)->select();
        $REG="/^\d{6}$/";
        $REGold=preg_match($REG,$oldpwd);
        $REGnew=preg_match($REG,$newpwd);
        $REGnewtrue=preg_match($REG,$newpwdtrue);
        if($REGold==1  && $REGnew==1 && $REGnewtrue==1 ) {
            if (jiami($oldpwd) == $redata[0]['dealpassword']) {
                if ($newpwd == $newpwdtrue) {
                    $data['dealpassword'] = jiami($newpwd);
                    $save = $model->where($user)->save($data);
                    if ($save) {
                        $username = session('user.user_name');
                        $id = session('user.id');
                        session('user', array('user_name' => $username, 'dealpwd' => $newpwd, 'id' => $id, 'expire' => 3600));
                        $this->success("修改交易密码成功");
                    } else {
                        $this->error("修改交易密码失败");
                    }
                } else {
                    $this->error("两次密码不一致");
                }
            } else {
                $this->error("交易密码有误");
            }
        }else{
            $this->error("交易密码格式有误");
        }
    }
    /***
     *
     *支付宝账号绑定
     */
    public function addalipay(){
        $card=I('alipay_user');
        $user['dealpassword']=I('alipay_password');
        $id['id']=session('user.id');
        $REG="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|^1(3|4|5|7|8)\d{9}$/";
        $REGpwd="/^\d{6}$/";
        $REGold=preg_match($REG,$card);
        $REGnew=preg_match($REGpwd,$user['dealpassword']);
        $model=M('users');
        $redata=$model->where($id)->select();
        if($REGold==1 && $REGnew==1) {
            if (jiami($user['dealpassword']) == $redata[0]['dealpassword']) {
                $data['userid'] = $redata[0]['id'];
                $data['username'] = $redata[0]['users'];
                $data['bankcard'] = $card;
                $data['type'] = 2;
                $data['addtime'] = time();
                $bankmodel = M('bank');
                $addbank = $bankmodel->add($data);
                if ($addbank) {
                    $this->success("支付宝绑定成功");
                } else {
                    $this->error("支付宝绑定失败");
                }
            } else {
                $this->error("交易密码错误");
            }
        }else if ($REGold==0 && $REGnew==1){
            $this->error("账号格式错误");
        }else if ($REGold==1 && $REGnew==0){
            $this->error("交易密码格式错误");
        }
    }
    /***
     *
     *微信账号绑定
     */
    public function addwechat(){
        $card=I('wechat_user');
        $user['dealpassword']=jiami($this->strFilter(I('wechat_password')));
        $id['id']=session("user.id");
        $REG="/^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}$|
        ^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|
        ^1(3|4|5|7|8)\d{9}$|^[1-9][0-9]{4,9}$/";
        $REGpwd="/^\d{6}$/";
        $REGold=preg_match($REG,$card);
        $REGnew=preg_match($REGpwd,$user['dealpassword']);
        $model=M('users');
        $redata=$model->where($id)->select();
        if($REGold==1 && $REGnew==1) {
            if (jiami($user['dealpassword']) == $redata[0]['dealpassword']) {
                $data['userid'] = $redata[0]['id'];
                $data['username'] = $redata[0]['users'];
                $data['bankcard'] = $card;
                $data['type'] = 3;
                $data['addtime'] = time();
                $bankmodel = M('bank');
                $addbank = $bankmodel->add($data);
                if ($addbank) {
                    $this->success("微信绑定成功");
                } else {
                    $this->error("微信绑定失败");
                }
            } else {
                $this->error("交易密码错误");
            }
        }else if($REGold==0 && $REGnew==1){
            $this->error("微信号格式错误");
        }else if($REGold==1 && $REGnew==0){
            $this->error("交易密码格式错误");
        }
    }
    /***
     *
     *短信接口
     */
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
                session("phone",array("mobile"=>$mobile,"mobile_code"=>$mobile_code));
                session("yanzenci",0);
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
                        $serf= $cishu+1;
                        session("yanzenci",$serf);
                        $this->error("手机验证码输入错误");
                    } else {
                        $model = M('users');
                        $id['id'] = session('user.id');
                        $data['phone'] = session('phone.mobile');
                        $data['phonetime'] = time();
                        $redata = $model->where($id)->save($data);
                        if ($redata) {
                            session("phone", array("mobile" => null, "mobile_code" => null));
                            session("yanzenci",null);
                            $this->success("手机验证成功");
                        } else {
                            $this->error("手机验证失败");
                        }
                    }
                }else{
                    $this->error("验证码失效请重新获取验证码");
                }

            }else{
                $this->error("手机号不正确，必须11位手机格式数字");
            }
   }
    /***
     *
     * 解除手机绑定
     *
     */
    function jiechu(){
        $sessionmobile=session('phone.mobile');
        $sessioncode=session('phone.mobile_code');
        $mobile=$this->strFilter(I('phone_num'));
        $mobile_code=$this->strFilter(I('cipher_code'));
        $REG="/^1(3|4|5|7|8)\d{9}$/";
        $REGold=preg_match($REG,$mobile);
        if($REGold==1){
            $cishu=session("yanzenci");
            if ($cishu<=5){
                if ($mobile != $sessionmobile or $mobile_code != $sessioncode or empty($mobile) or empty($mobile_code)) {
//               exit('手机验证码输入错误。');
                    $serf= $cishu+1;
                    session("yanzenci",$serf);
                    $this->error("手机验证码输入错误");
                } else {
                    $model = M('users');
                    $id['id'] = session('user.id');
                    $data['phone'] = null;
                    $data['endtime'] = time();
                    $redata = $model->where($id)->save($data);
                    if ($redata) {
                        session("phone", array("mobile" => null, "mobile_code" => null));
                        session("yanzenci",null);
                        $this->success("手机解除成功");
                    } else {
                        $this->error("手机解除失败");
                    }
                }
            }else{
                $this->error("验证码失效请重新获取验证码");
            }

        }else{
            $this->error("手机号不正确，必须11位手机格式数字");
        }


    }
    /***
     *
     *重置交易密码
     */
    function reset(){
        $model=M('users');
//        $idcard=$this->strFilter(I('idcard'));
        $code=$this->strFilter(I('cipher_code'),true,"未认证手机号码");
        $yanzheng_num=$this->strFilter(I('yanzheng_num'),true,"验证码不可为空");
        $id['id']=session('user.id');
        $redata=$model->where($id)->select();
        if($redata){
            $phone=$redata[0]['phone'];
            $recode=$code;
            $this->sms($phone,$recode,$yanzheng_num);
        }
    }
    function tijiao(){
        $model=M('users');
        $idcard=$this->strFilter(I('id_card'));
        $id['id']=session('user.id');
        $redata=$model->where($id)->select();
        $mobile=$redata[0]['phone'];
        $mobile_code=$this->strFilter(I('cipher_code_id'));
        $code=$this->random(6,1);
        session('send_code',$code);
        $sessionmobile=session('phone.mobile');
        $sessioncode=session('phone.mobile_code');
        //echo '<pre>';print_r($_POST);print_r($_SESSION);
        $cishu=session("yanzenci");

        if ($cishu<=5) {
            if ($mobile != $sessionmobile or $mobile_code != $sessioncode or empty($mobile) or empty($mobile_code)) {
//               exit('手机验证码输入错误。');
                $serf= $cishu+1;
                session("yanzenci",$serf);
                $this->error("手机验证码输入错误");
            } else {
                $model = M('users');
                $id['id'] = session('user.id');
                $data['dealpassword'] = "";
                $redata = $model->where($id)->save($data);
       
                if ($redata!==false) {
                    $username = session('user.user_name');
                    session("phone", array("mobile" => null, "mobile_code" => null));
//                session('user',array('user_name'=>$username,'dealpwd'=>"",'id'=>$id,'expire'=>3600));
//              session("user.dealpwd","");
//                $this->success("重置");
                    session("yanzenci",null);
                    $this->ajaxReturn(true);
                } else {
                    $this->error("重置失败");
                }
            }
        }else{
            $this->error("验证码失效请重新获取验证码");
        }
    }
    /***
     *
     *添加银行卡
     */
    function resetdeal(){
        $id['id']=session('user.id');
        $data['dealpassword']=I('new_trade_num');
        $quedeal=I('re_new_trade');
        $REGpwd="/^\d{6}$/";
        $REGnew=preg_match($REGpwd,$data['dealpassword']);
        $REGnewtrue=preg_match($REGpwd,$quedeal);
        if($REGnew==1 && $REGnewtrue==1){
            if ($data['dealpassword']==$quedeal){
                $deal['dealpassword']=jiami($data['dealpassword']);
                $model=M('users');
                $redata=$model->where($id)->save($deal);
                if ($redata){
                    $this->success("重置交易密码成功");
                }else{
                    $this->error("重置交易密码失败");
                }
            }else{
                $this->error("两次密码不一致");
            }
        }else if ($REGnew==0 && $REGnewtrue==1 || $REGnew==1 && $REGnewtrue==0){
            $this->error("密码格式错误");
        }
        
    }
    /***
     *
     *添加银行卡
     */
    function addbank(){
        $data['userid']=session('user.id');
        $data['username']=session('user.user_name');
        $id['id']=$data['userid'];
        $ressst=M('users')->field('username')->where($id)->find();
        $data['type']=1;
        $data['addtime']=time();
        $data['name']=$this->strFilter(I('remark_name'));
        $bank['bankname']=$this->strFilter(I('bank'),true,"开户银行不能为空");
        $bankmodel=M('banktype')->field('id')->where($bank)->find();
        $data['bank']=$bankmodel['id'];
        $data['bankprov']=$this->strFilter(I('bankprov'));
        $data['bancity']=$this->strFilter(I('bancity'));
        $data['bankname']=$ressst['username'];
        $data['bankaddr']=$bank['bankname'];
        $data['bankcard']=$this->strFilter(I('bank_number'));
        $deal=jiami($this->strFilter(I('bank_trade_pw')));
        $model=M('users');
        $id['id']=session('user.id');
        $redata=$model->where($id)->select();
        //26ac521a7eebe034ac380b4ea11729bd
        $bankmodel=M('bank');
        $where['userid']= $data['userid'];
        $restbank=$bankmodel->where($where)->select();
        if($restbank){
            $this->error("已经绑定了银行卡");
        }else{
            if ($deal==$redata[0]['dealpassword']){
                $bankmodel=M('bank');
                $return=$bankmodel->add($data);
                if ($return){
                    $this->success("添加成功");
                }else{
                    $this->error("添加失败");
                }
            }else{
                $this->error("交易密码错误");
            }
        }

    }
    /***
     *
     *删除银行卡
     */

    function deletebank(){
        $where['id']=$this->strFilter(I('id'));
        $deal=jiami($this->strFilter(I('deal')));
        $model=M('users');
        $id['id']=session('user.id');
        $redata=$model->where($id)->select();
        if ($deal==$redata[0]['dealpassword']){
            $bankmodel=M('bank');
            $delete=$bankmodel->where($where)->delete();
            if ($delete){
                $this->success("删除成功");
            }else{
                $this->error("删除失败");
            }
        }else{
            $this->error("支付密码错误");
        }
    }

    /***
     *
     *照片认证
     */
    public function file(){
        $modeluser=M('users');
        $id['id']=session('user.id');
        $username=$modeluser->where($id)->select();
        import('ORG.Net.UploadFile');
        
        //判断特殊字符
        $photo = $_FILES['photo'];
        $in = "%";
        foreach ($photo['name'] as $key => $value) {
            $tmparr = explode($in,$value);
            if (count($tmparr) > 1) {
                $this -> error("图片名称含有特殊字符");
            }
        }

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =      5242880 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
//                 $upload->rootPath  =     '/Publick/img'; // 设置附件上传根目录
        $upload->savePath  =  time().'_'.$this->random(6); // 设置附件上传（子）目录
        $upload->saveName='';

        // 上传文件
        $info   =   $upload->upload();

        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }

        if($info[0]['savename']===$info[1]['savename'] || $info[0]['savename']===$info[2]['savename'] || $info[1]['savename']===$info[2]['savename']){
            unlink($info['0']['savename']);
            unlink($info['1']['savename']);
            unlink($info['2']['savename']);
            unlink($upload->savePath);
            $this->error("上传有重复照片请刷新重试");
        }
        $topurl = $info['0']['savename'];
        $bankurl = $info['1']['savename'];
        $takeurl = $info['2']['savename'];
        $add_data['topurl']='Uploads/'.$info['0']['savepath'].$topurl;   //文件路径
        $add_data['bankurl']='Uploads/'.$info['1']['savepath'].$bankurl;
        $add_data['takeurl']='Uploads/'.$info['2']['savepath'].$takeurl;

        $add_data['userid']=session('user.id');
        $add_data['username']=$username[0]['username'];
        $add_data['addtime']=time();
        $add_data['users']=session('user.user_name');
        $add_data['idcard']=$username[0]['idcard'];
        $add_data['phone']=$username[0]['phone'];
        if ($add_data){
            $model=M('realname');
            $redata=$model->add($add_data);
            if ($redata){
                $data['yanze']=0;
                $sedata=$modeluser->where($id)->save($data);
                $this->success("认证审核中");
            }else{
                $this->error("审核失败");
            }
        }
    }

}