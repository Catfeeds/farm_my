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
            $host = "http://sms.market.alicloudapi.com";
            $path = "/singleSendSms";
            $method = "GET";
            $mobile_code = $this->random(6, 1);
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
            if (empty($mobile)) {
                $this->error("手机号码不能为空");
                exit();
            }
            $session = session('send_code');
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
                $this->success("短信已发送");
            } else {

                $this->error("短信发送失败，请刷新页面后重试");
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