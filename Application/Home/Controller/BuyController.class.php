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

        if (session('user')['id']==""){
            $this->redirect('Index/index');
            exit();
        }

        $cate = $this -> gettree();

        $this -> assign("cate", $cate);
    }

	//购买过程
    public function buy(){
        //将产品信息提交到确认订单页面
        $product_id = $this -> strFilter(I("product_id")) ? I("product_id") : null;
        if ($product_id != null) {
            $product_info = M()
                -> table("currency_product as p")
                -> field("p.id, p.name, p.img, p.price, pc.type")
                -> join("left join currency_procate as pc on p.cat_id = pc.id")
                -> where("p.id = ". $product_id)
                -> find();
        }


        //查询是否有默认地址，没有则选择地址
        $default = M()
            -> table("currency_shop_address as sa")
            -> field("sa.id, sa.name, sa.mobile, sa.address, sc.city_name as province, scc.city_name as city , sccc.city_name as area, sa.status")
            -> join("left join currency_shop_city as sc on sc.id = sa.province")
            -> join("left join currency_shop_city as scc on scc.id = sa.city")
            -> join("left join currency_shop_city as sccc on sccc.id = sa.area")
            -> where("sa.user_id = ". session('user')['id']. " AND sa.status = 1")
            -> find();

        $this -> assign("info", $product_info);
        $this -> assign("default", $default);
        $this -> display();
    }

    //加入购物车
    public function cart() {
        
    }

    //结算确认订单
    public function confirm() {
        //不同类别的产品不能同时结算
        //不同类别的产品结算方式不同
        //生成订单
        $product_id = $this -> strFilter(I("product_id")) ? I("product_id") : null;
        $type = $this -> strFilter(I("type")) ? I("type") : null;
        $number = $this -> strFilter(I("number")) ? I("number") : null;
        $ship_id = $this -> strFilter(I("ship_id")) ? I("ship_id") : null;

        $this -> display();
    }

    //支付页面
    public function pay() {

    }

    //确认收货
    public function receiving() {
        //改变订单状态
        //进入红包表
    }

    /*文章类型*/
    public function gettree($id = 0, $field = true){
        /* 获取当前分类信息 */
        $table=M('procate');
        if($id){
            $info = $table->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('eq', 1));
        $list = $table->field($field)->where($map)->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['child'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }
}