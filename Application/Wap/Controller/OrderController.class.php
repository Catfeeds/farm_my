<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Wap\Controller;
use OT\DataDictionary;
use Think\Page;
use Home\Model\ProductModel;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class OrderController extends WapController {
    private $cat_id = 1;
    private $search = "";
    private $Page;

    public function __construct() {
        parent::__construct();

        if (session('user_wap')['id']==""){
            $this->redirect('Index/index');
            exit();
        }
    }

    //列表
    public function index(){
        $where = ['o.user_id' => session("user_wap")['id']];

        if (!empty(I('date')) && !empty(I('dates')))  {
            $where = ['o.time'=>[ ['egt',strtotime(I('date'))],['elt',strtotime(I('dates'))+86400] ] ];
            $page_where['date'] = I('date');
            $page_where['dates'] = I('dates');
        }

        $count = M("shop_order o") -> where($where) -> count();

        $Page = new Page($count, 10, $page_where);
        $show = $Page -> show();

        $list = M()
            -> table("currency_shop_order as o")
            -> field("o.id, o.order, o.number, o.total_money, o.time, o.status, o.product_name, o.product_img, o.product_price, o.product_type, p.name, p.img, p.price, pc.type")
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_procate as pc on p.cat_id = pc.id")
            -> where($where)
            -> order("o.time desc")
            -> limit($Page -> firstRow, $Page -> listRows)
            -> select();

        // dump($list);

        // $this -> ajaxReturn($list);
        $this -> assign("list", $list);
        $this -> display();
    }	//列表
    public function index_more(){
        $where = ['o.user_id' => session("user_wap")['id']];

        if (!empty(I('date')) && !empty(I('dates')))  {
            $where = ['o.time'=>[ ['egt',strtotime(I('date'))],['elt',strtotime(I('dates'))+86400] ] ];
            $page_where['date'] = I('date');
            $page_where['dates'] = I('dates');
        }

        $count = M("shop_order o") -> where($where) -> count();

        $Page = new Page($count, 10, $page_where);
        $show = $Page -> show();

        $list = M()
            -> table("currency_shop_order as o")
            -> field("o.id, o.order, o.number, o.total_money, o.time, o.status, o.product_name, o.product_img, o.product_price, o.product_type, p.name, p.img, p.price, pc.type")
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_procate as pc on p.cat_id = pc.id")
            -> where($where)
            -> order("o.time desc")
            -> limit($Page -> firstRow, $Page -> listRows)
            -> select();

        // dump($list);

        $this -> ajaxReturn($list);
        // $this -> display();
    }

    //详情
    public function single($id) {
        $info = M("product") -> field("id, name, price, img, description") -> where("id = ". $id) -> find();

        $this -> assign("info", $info);
        $this -> display();
    }

}