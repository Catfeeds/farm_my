<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Page;

/**
 * 后台产品控制器
 * @author huajie <banhuajie@163.com>
 */
class OrderController extends AdminController {
    public function index() {
        $list = M()
            -> table("currency_shop_order as o")
            -> field("o.id, o.order, o.number, o.total_money, o.time, o.status, p.name, p.img, pc.type")
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_procate as pc on p.cat_id = pc.id")
            -> select();

        $this -> assign("list", $list);

        $this -> display();
    }

    public function detail() {
        $id=I('id');
        $info = M()
            -> table("currency_shop_order as o")
            -> field("o.id, o.order, o.number, o.total_money, o.time, o.status, p.name, p.img, pc.type, u.users, sa.id as ship_id")
            -> join("left join currency_users as u on o.user_id = u.id")
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_procate as pc on p.cat_id = pc.id")
            -> join("left join currency_shop_address as sa on o.ship_id = sa.id")
            -> where("o.id = ". $id)
            -> find();

        $address = M()
            -> table("currency_shop_address as a")
            -> field("a.name as ship_name, a.mobile, a.address, sc.city_name as province, scc.city_name as city, sccc.city_name as area")
            -> join("left join currency_shop_city as sc on sc.id = a.province")
            -> join("left join currency_shop_city as scc on scc.id = a.city")
            -> join("left join currency_shop_city as sccc on sccc.id = a.area")
            -> where("a.id = ". $info['ship_id'])
            -> find();

        $this -> assign("address", $address);
        $this -> assign('info', $info);
        $this -> display();
    }

    public function ship() {
        $id = I("id") ? I("id") : null;

        //减少商品库存
        $product = M() 
            -> table("currency_shop_order as o")
            -> field("o.number as order_number, p.id, p.number as product_number") 
            -> join("left join currency_product as p on p.id = o.product_id")
            -> where("o.id = ". $id)
            -> find();
        // dump($product);
        if ($product['order_number'] > $product['product_number']) {
            $this -> error("商品库存不足");
        } else {
            $res = M("product") -> save(['id' => $product['id'], "number" => ($product['product_number'] - $product['order_number'])]);

            if ($res) {
                $res2 = M("shop_order") -> save(['id' => $id, "status" => 2]);
                if ($res2) {
                    $this -> success("发货成功");
                } else {
                    $this -> error("发货失败");
                }
            } else {
                $this -> error("发货失败");
            }
        }
    }
}
