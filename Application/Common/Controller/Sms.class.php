<?php
namespace Common\Controller;

ini_set("display_errors", "on");

require_once 'public/Sms/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
// use think\Db;
use think\Model;

// 加载区域结点配置
Config::load();


// use Common\Model\Sms as SmsModel;
// // use app\common\controller\Response;
// use think\cache\driver\Redis;
// use think\Controller;
// use think\Request;


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials : true");

/**
 * 短信类
 * @author banana
 */
class Sms {
    protected $sms_model;
    protected $response;
    protected $request;
    protected $redis;

    // public function _initialize()
    // {
    //     parent::_initialize();
    //     // $this -> sms_model  = new SmsModel();
    //     // $this -> response   = new Response();
    //     // $this -> request    = Request::instance();
    //     // $this -> reids      = new Redis();
    // }

    static $acsClient = null;

    public function __construct() {

    }
    
    /**
     * 取得AcsClient
     *
     * @access public
     * @param  string $accessKeyId
     * @param  string $accessKeySecret
     * @return DefaultAcsClient
     */
    public static function getAcsClient($accessKeyId = null, $accessKeySecret = null) {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        // $accessKeyId = "LTAIoUGP5b4hbuZm"; // AccessKeyId

        // $accessKeySecret = "wxTtlUuICx4MFkwCleDkcDyGno7qUr"; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     *
     * @access public
     * @param  int $mobile_number 手机号 
     * @param  int $code_number 验证码
     * @param  array $cfg 配置
     * @return stdClass
     */
    public static function sendSms($mobile_number, $code_number, $cfg = array()) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($mobile_number);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($cfg['sign']);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($cfg['template']);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "code"=>$code_number,
            "product"=>"dsd"
        ), JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
        $request->setOutId($cfg['outid']);

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        $request->setSmsUpExtendCode($cfg['extendcode']);

        // 发起访问请求
        $acsResponse = static::getAcsClient($cfg['access_key_id'], $cfg['access_key_secret'])->getAcsResponse($request);

        return $acsResponse;

    }

    /**
     * 短信发送记录查询
     * @return stdClass
     */
    public static function querySendDetails() {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->setPhoneNumber("12345678901");

        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
        $request->setSendDate("20170718");

        // 必填，分页大小
        $request->setPageSize(10);

        // 必填，当前页码
        $request->setCurrentPage(1);

        // 选填，短信发送流水号
        $request->setBizId("yourBizId");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }

    /**
     * 获取短信配置
     *
     * @access public 
     * @return array 
     */
    public function getSmsCfg() {
        $cfg = M("sms_cfg")
            -> field("sign, template, outid, extendcode, access_key_id, access_key_secret, expire")
            -> find();

        return $cfg;
    }

    // /**
    //  * 发送
    //  *
    //  * @access public
    //  * @param  string $mobile_number 手机号
    //  * @return json
    //  */
    // public function send($mobile_number = null) {
    //     if ($mobile_number == null) {
    //         $this -> error("手机号不能为空", 2);
    //     } else {
    //         if (!preg_match("/^1[34578]\d{9}$/", $mobile_number)) {                    //正则匹配手机号是否正确
    //             $this -> error("请输入正确的手机号", 2);
    //         } else {
    //             $cfg = $this -> getSmsCfg();                              //获取配置

    //             $code_number = rand(100000, 999999);                                   //获取随机验证码

    //             set_time_limit(0);
    //             header('Content-Type: text/plain; charset=utf-8');

    //             $response = $this -> sendSms($mobile_number, $code_number, $cfg);
                
    //             if ($response -> Code == "OK") {                                        //发送成功，存入redis
    //                 $expire = $cfg['expire'] * 60;                                      //查询验证码过期时间

    //                 // $this -> reids -> set($mobile_number,$code_number,$expire);

    //                 $this -> success("发送成功", 1);
    //             } elseif ($response -> Code == "isv.MOBILE_NUMBER_ILLEGAL") {
    //                 $this -> error("请输入正确的手机号", 2);         //发送失败，根据返回码返回提示
    //             } else {
    //                 $this -> error("发送失败", 2);
    //             }
    //         }
    //     }
        
    // }


    // /**
    //  * 验证
    //  *
    //  * @access public
    //  * @param  string $mobile_number 手机号码
    //  * @param  string $code_number   验证码
    //  * @return json
    //  */
    // public function check($mobile_number, $code_number) {
    //     $data = $this -> request -> param();

    //     if ($mobile_number == null) {
    //         return $this -> response -> index("手机号不能为空", 1);
    //     } else {
    //         if (!isset($code_number) || $code_number == null) {                 //判空
    //             return $this -> response -> index("验证码不能为空", 2);
    //         } else {
    //             $code_number_send = $this -> reids -> get($mobile_number);

    //             if ($code_number_send == false) {                               //判断是否超时
    //                 return $this -> response -> index("验证码已失效，请重新发送", 2);
    //             } else {
    //                  if ($code_number_send != $code_number) {
    //                     return $this -> response -> index("验证码输入错误", 2);
    //                 } else {
    //                     $this -> reids -> rm($mobile_number);                   //删除保存的验证码
    //                     return $this -> response -> index("验证码正确", 1);
    //                 }                   
    //             }
    //         }
    //     }
    // }
}