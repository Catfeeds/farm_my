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
use Common\Controller\Sms;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class PhotomaController extends HomeController
{
    //登录日志
    public function checkphoto()
    {
        ob_clean();
        $Verify = new \Think\Verify();
        $Verify->entry();
    }

    function sms($phonee = "", $mimi = "", $yanzheng_num = "")
    {
        $phone = $this->strFilter(I('phone_num')) ? $this->strFilter(I('phone_num')) : $phonee;
        $mi = $this->strFilter(I('cipher_code')) ? $this->strFilter(I('cipher_code')) : $mimi;
        $yanzheng_num = $this->strFilter(I('yanzheng_num')) ? $this->strFilter(I('yanzheng_num')) : $yanzheng_num;
        if (check_verify($yanzheng_num, "")) {
            if ($phone == null) {
                $this -> error("手机号不能为空", 2);
            } else {
                $sms = new Sms();

                if (!preg_match("/^1[34578]\d{9}$/", $phone)) {                    //正则匹配手机号是否正确
                    $this -> error("请输入正确的手机号", 2);
                } else {
                    $cfg = $sms -> getSmsCfg();                              //获取配置

                    $code_number = rand(100000, 999999);                                   //获取随机验证码

                    set_time_limit(0);
                    header('Content-Type: text/plain; charset=utf-8');

                    $response = $sms -> sendSms($phone, $code_number, $cfg);
                    
                    if ($response -> Code == "OK") {                                        //发送成功，存入redis
                        $expire = $cfg['expire'] * 60;                                      //查询验证码过期时间

                        session("yanzenci",5);
                        session("phone",array("mobile"=>$phone,"mobile_code"=>$code_number));

                        $this -> success("发送成功", 1);
                    } elseif ($response -> Code == "isv.MOBILE_NUMBER_ILLEGAL") {
                        $this -> error("请输入正确的手机号", 2);         //发送失败，根据返回码返回提示
                    } else {
                        $this -> error("发送失败", 2);
                    }
                }
            }
        } else {
            $this->error("请输入正确验证码");
        }

    }

    function random($length = 6, $numeric = 0)
    {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

    function reg()
    {
        $code = $this->random(6, 1);
        session('send_code', $code);
        $sessionmobile = session('phone.mobile');
        $sessioncode = session('phone.mobile_code');
        $mobile = $this->strFilter(I('phone_num'));
        $mobile_code = $this->strFilter(I('cipher_code'));
        $REG = "/^1(3|4|5|7|8)\d{9}$/";
        $REGold = preg_match($REG, $mobile);
        if ($REGold == 1) {
            $cishu = session("yanzenci");
            if ($cishu <= 5) {
                if ($mobile != $sessionmobile or $mobile_code != $sessioncode or empty($mobile) or empty($mobile_code)) {
//               exit('手机验证码输入错误。');
                    $serf = $cishu + 1;
                    session("yanzenci", $serf);
                    $this->error("手机验证码输入错误");
                } else {
                    session("phone", array("mobile" => null, "mobile_code" => null));
                    session("yanzenci", null);
                    $this->success("手机号验证成功");
                }
            } else {
                $this->error("验证码失效请重新获取验证码");
            }
        } else {
            $this->error("手机号不正确，必须11位手机格式数字");
        }
    }
    function rmbout($mobile_code){
        $code = $this->random(6, 1);
        session('send_code', $code);
        $sessioncode = session("phone.mobile_code");
        $cishu = session("yanzenci");
        session("yanzenci", null);
        if ($cishu <= 5) {
            if ($mobile_code != $sessioncode or empty($mobile_code)) {
                $serf = $cishu + 1;
                session("yanzenci", $serf);
                $this->error("手机验证码输入错误");
            } else {
                session("phone", array("mobile" => null, "mobile_code" => null));
                session("yanzenci", null);
//                    $this->success("手机号验证成功");
                return true;
            }
        } else {
            $this->error("验证码失效请重新获取验证码");
        }
    }
}