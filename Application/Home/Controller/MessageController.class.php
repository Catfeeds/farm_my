<?php
/**
 * Created by PhpStorm.
 * User: DENG
 * Date: 2017/7/15
 * Time: 15:06
 */
namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class MessageController extends SessionController    {




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
    $sessioncode=session('phone.mobile_code');
    $mobile_code=$this->strFilter(I('cipher_code'));
    
        if ( $mobile_code != $sessioncode or empty($mobile_code)) {
//               exit('手机验证码输入错误。');
            $this->error("手机验证码输入错误");
        } else {
            session("phone", array("mobile" => null, "mobile_code" => null));
            session("yanzheng",array('yanzheng'=>1));
//            $this->success("手机号验证成功");
        }

    }


}