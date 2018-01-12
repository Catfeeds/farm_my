<?php
/**
 * Created by PhpStorm.
 * User: 48930
 * Date: 2017/7/24
 * Time: 16:51
 */


function jiami($data){
    $result=md5(md5(sha1($data)));
    return $result;
}


function check_verify($code, $id = 1){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

function sendemail($address,$subject,$content)
{
    $email_smtp = 'smtp.qq.com';
    $email_username = '489303873@qq.com';
    $email_password ='ellijmoxqkaabidd';
    $email_from_name = '恋狱';
    $email_smtp_secure ='ssl';
    $email_port ='465';
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

//正则检验 正数（不包括0）
function check_number($int){
    $back=array();
//    preg_match("/^[1-9]\d*$|^[1-9]\d*\.\d{1,}|0\.\d*[1-9]{1,}$/",$int,$back);
    preg_match("/^[1-9]\d*$|^[1-9]\d*\.\d*[1-9]{1,6}$|0\.\d*[1-9]{1,6}$/",$int,$back);
    return $back[0];
}
