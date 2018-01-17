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
use Think\Page;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class BuyController extends HomeController {
    private $cat_id = 1;
    private $search = "";
    private $Page;

    public function __construct() {
        parent::__construct();
        $cate = $this -> gettree();

        $this -> assign("cate", $cate);
    }

	//购买过程
    public function buy(){
        
    }

    //加入购物车
    public function cart() {
        
    }

    //结算确认订单
    public function confirm() {
        //不同类别的产品不能同时结算
        //生成订单
    }

    //确认收货
    public function receiving() {
        //改变订单状态
        //进入红包表
    }
}