<?php
return array(
	//'配置项'=>'配置值'
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/img',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
        '__ALERT__'     => __ROOT__ . '/Public/alert' ,
    ),
    // 配置邮件发送服务器
    'EMAIL_FROM_NAME'        => '恋狱',        // 发件人
    'EMAIL_SMTP'             => 'smtp.qq.com',  // smtp
    'EMAIL_USERNAME'         => '489303873@qq.com',        // 账号
    'EMAIL_PASSWORD'         => 'ellijmoxqkaabidd',        // 密码  注意: 163和QQ邮箱是授权码；不是登录的密码
    'EMAIL_SMTP_SECURE'      => 'ssl',          // 如果使用QQ邮箱；需要把此项改为  ssl
    'EMAIL_PORT'             => '465',          // 如果使用QQ邮箱；需要把此项改为  465

);