<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}


function jiami($data){
    $result=md5(md5(sha1($data)));
    return $result;
}

//查询二维码
function public_code() {
    $config = M("config") -> where("type = 5 and name = 'PUBLIC_CODE'") -> field("value") -> find();
    $public_code = $config['value'];

    return $public_code;
}

//是否登录
function havlogin() {
    if (session('user.id')) {
        return true;
    } else {
        return false;
    }
}

//关于我们
function about_us(){
    $about_us=M('text')
        ->where('status=1 and type=5')
        ->order('sort desc')
        ->limit(3)
        ->field('id,type,title')
        ->select();
    return $about_us;
}
//服务帮助
function service_help(){
    $service_help=M('text')
        ->where('status=1 and type=6')
        ->order('sort desc')
        ->limit(3)
        ->field('id,type,title')
        ->select();
    return $service_help;
}

//友情链接
function friend_link(){
    $friend_link=M('interlinkage')->where('status=1')->order('sort desc')->select();
    return $friend_link;
}
//市场合作
function market_cooperation() {
    $market_cooperation = M('config')
        -> where('id in(38, 42, 40, 39)')
        -> field('title, value')
        -> select();
    return $market_cooperation;
}
//正则检验 正数（不包括0）
function check_number($int){
    $back=array();
    preg_match("/^[1-9]\d*$|^[1-9]\d*\.\d{1,}|0\.\d*[1-9]\d{1,}$/",$int,$back);
    return $back[0];
}
/**
 * 功能：邮件发送函数
 * @param string $to 目标邮箱
 * @param string $subject 邮件主题（标题）
 * @param string $to 邮件内容
 * @return bool true
 */

//function sendMail($to, $title, $content) {
//    Vendor('PHPMailer.PHPMailerAutoload');
//    $mail = new PHPMailer(); //实例化
//    $mail->IsSMTP(); // 启用SMTP
//    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
//    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
//    $mail->Username = C('MAIL_USERNAME'); //发件人邮箱名
//    $mail->Password = C('MAIL_PASSWORD') ; //163邮箱发件人授权密码
//    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
//    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
//    $mail->AddAddress($to,"尊敬的客户");
//    $mail->WordWrap = 50; //设置每行字符长度
//    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
//    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
//    $mail->Subject =$title; //邮件主题
//    $mail->Body = $content; //邮件内容
//    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
//    return($mail->Send());
//}
function send_email($address,$subject,$content)
{
    $email_smtp = C('EMAIL_SMTP');
    $email_username = C('EMAIL_USERNAME');
    $email_password = C('EMAIL_PASSWORD');
    $email_from_name = C('EMAIL_FROM_NAME');
    $email_smtp_secure = C('EMAIL_SMTP_SECURE');
    $email_port = C('EMAIL_PORT');
    if (empty($email_smtp) || empty($email_username) || empty($email_password) || empty($email_from_name)) {
        return array("error" => 1, "message" => '邮箱配置不完整');
    }
//    Vendor('PHPMailer.phpmailer');
//    Vendor('PHPMailer.smtp');
    require_once './ThinkPHP/Library/Vendor/PHPMailer/class.phpmailer.php';
    require_once './ThinkPHP/Library/Vendor/PHPMailer/class.smtp.php';
    $phpmailer = new \Phpmailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $phpmailer->IsSMTP();
    // 设置设置smtp_secure
    $phpmailer->SMTPSecure = $email_smtp_secure;
    // 设置port
    $phpmailer->Port = $email_port;
    // 设置为html格式
    $phpmailer->IsHTML(true);
    // 设置邮件的字符编码'
    $phpmailer->CharSet = 'UTF-8';
    // 设置SMTP服务器。
    $phpmailer->Host = $email_smtp;
    // 设置为"需要验证"
    $phpmailer->SMTPAuth = true;
    // 设置用户名
    $phpmailer->Username = $email_username;
    // 设置密码
    $phpmailer->Password = $email_password;
    // 设置邮件头的From字段。
    $phpmailer->From = $email_username;
    // 设置发件人名字
    $phpmailer->FromName = $email_from_name;
    // 添加收件人地址，可以多次使用来添加多个收件人
    if (is_array($address)) {
        foreach ($address as $addressv) {
            $phpmailer->AddAddress($addressv);
        }
    } else {
        $phpmailer->AddAddress($address);
    }
    // 设置邮件标题
    $phpmailer->Subject = $subject;
    // 设置邮件正文
    $phpmailer->Body = $content;
    // 发送邮件。
    if (!$phpmailer->Send()) {
        $phpmailererror = $phpmailer->ErrorInfo;
        return array("error" => 1, "message" => $phpmailererror);
    } else {
        return array("error" => 0);
    }

}
//保留6位有效小数
function getEffective($string){
    $back= floatval(round($string,6));
    return $back>0 ? $back : 0;
}

//正数
function  positive($int){
   return preg_match("/^[1-9][0-9]*$/",$int);
}

function getUnit($transaction_allnumber){
    $length=strlen(round($transaction_allnumber,0));            //获取交易量的长度！
    if ($length>=5 && $length<=8){
        return $transaction_allnumber=round($transaction_allnumber/10000,2)."万";
    }
    if ($length>=9){
        return  $transaction_allnumber=round($transaction_allnumber/100000000,2).'亿';
    }

    return round($transaction_allnumber,2);
}
