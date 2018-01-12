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
use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class SessionController extends HomeController    {

	//首页
    public function __construct(){
        parent::__construct();
        if(session('user.user_name') && session('user.id') ){
            return true;
        }else{
            return false;
        }
    }
    function ifsession(){
        if(session('user.user_name') && session('user.id') ){
            $this->ajaxReturn(true);
            exit();
        }else{
            $this->ajaxReturn(false);
            exit();
        }
    }


}