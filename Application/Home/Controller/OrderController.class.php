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
use Home\Model\ProductModel;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class OrderController extends HomeController {
    private $cat_id = 1;
    private $search = "";
    private $Page;

    public function __construct() {
        parent::__construct();

        if (session('user')['id']==""){
            $this->redirect('Index/index');
            exit();
        }
    }

	//
    public function index(){
        $list = M()
            -> table("currency_shop_order as o")
            -> field("o.id, o.order, o.number, o.total_money, o.time, o.status, p.name, p.img, p.price, pc.type")
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_procate as pc on p.cat_id = pc.id")
            -> where("o.user_id = ". session("user")['id'])
            -> select();

        $this -> assign("list", $list);
        $this -> display();
    }

    //详情
    public function single($id) {
        $info = M("product") -> field("id, name, price, img, description") -> where("id = ". $id) -> find();

        $this -> assign("info", $info);
        $this -> display();
    }

}