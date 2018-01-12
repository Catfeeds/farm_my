<?php
namespace Wap\Controller;
use Think\Controller;
use Com\Wechat;
use Com\WechatAuth;
define("APPID", "wx3062a21cc236fb33");
define("SECRET", "3f2ab9ef74360df6274d04250a3d1d9a");
define("TOKEN", "GwuUWm4bg9OjsRqD");
/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class WapController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->redata();
        $iswechar=I('wechat');
        if($iswechar=="key"){
            $addr=I('addr');
            $free=I('free');
            $act=$addr."/".$free;
            session("addr",$act);
            $code=$this->weixinone();
            exit();
        }
    }
    function ifsess(){
        if(session('user_wap.user_name') && session('user_wap.id') ){
            $this->ajaxreturn(true) ;
        }else{
            $this->ajaxreturn(false) ;
        }
    }
    function iflogin(){
        if(session('user_wap.user_name') && session('user_wap.id') ){
            return true;
        }else{
            return false;
        }
    }
    function weixinone(){
        $APPID='wxeca8a26c1ee3be41';
        $REDIRECT_URI=urlencode('http://www.mm-oo.com/Wap`Wxreturn`index');
        $scope='snsapi_base';
        //$scope='snsapi_userinfo';//需要授权
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$APPID&redirect_uri=$REDIRECT_URI&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        header("Location:".$url);
    }
    function weixintwo($code){
        $APPID='wxeca8a26c1ee3be41';
        $secret="b3dce2704b5288a37f5cd30d46fb3fc7";
//        $REDIRECT_URI=urlencode('http://www.mm-oo.com/Wap');
        $scope='snsapi_base';
        //$scope='snsapi_userinfo';//需要授权
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$APPID&secret=$secret&code=$code&grant_type=authorization_code ";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURL_SSLVERSION_SSL, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        $response=json_decode($data,true);
        return $response;
    }
    protected function redata(){
        $this->assign("session",count(session('user_wap')));
        $data=session('user_wap.user_name');
        $this->assign('id',$data);
    }
    protected function strFilter($str,$type=false,$error="含有非法字符请重输"){
        if($type){
            if($str==""){
                $this->error($error);
            }
        }
        $reg=" /\ |\￥|\……|\、|\‘|\’|\；|\：|\【|\】|\（|\）|\！|\·|\-|\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        //允许通过的特殊字符   。，《 》 “ ”
        $REGold=preg_match($reg,$str);
        if($REGold==1){
            $this->error($error);
        }else{
            return $str;
        }
    }
    function resetnumber(){
        $REG="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|^1(3|4|5|7|8)\d{9}$/";
        $username = I('name');
        $REGold=preg_match($REG,$username);
        if($REGold==1){
            $loginusername = $username;
        }else{
            $this->error("账号错误");
            exit();
        }
        $w['users'] = $loginusername;
        $rsetlogin = M("users")->where($w)->select();
        $loginci['loginci']=3;
        $loginciid['id']=$rsetlogin[0]['id'];
        $reslogin=M('users')->where($loginciid)->save($loginci);
    }
}