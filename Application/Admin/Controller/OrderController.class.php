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
use Common\Controller\BonusController;

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
            -> order("o.time desc")
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
            -> field("o.number as order_number, o.user_id, p.id, p.number as product_number, pc.type") 
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_procate as pc on pc.id = p.cat_id")
            -> where("o.id = ". $id)
            -> find();
        // dump($product);
        if ($product['order_number'] > $product['product_number']) {
            $this -> error("商品库存不足");
        } else {
            $res1 = M("product") -> save(['id' => $product['id'], "number" => ($product['product_number'] - $product['order_number'])]);

            if ($res1) {
                $res2 = M("shop_order") -> save(['id' => $id, "status" => 2]);
                if ($res2) {
                    //发放
                    switch ($product['type']) {
                        case '1': //红包
                            $price = M("shop_order") 
                                -> table("currency_shop_order as o")
                                -> field("o.total_money, o.number, o.order, p.out, p.price, u.pid, u.id") 
                                -> join("left join currency_product as p on p.id = o.product_id")
                                -> join("left join currency_users as u on o.user_id = u.id")
                                -> where("o.id = ". $id) 
                                -> find();
                            $data['outs'] = $price['out'];
                            $data['provide'] = 0;
                            $data['time'] = time();
                            $data['user_id'] = $product['id'];
                            $bonus_dis = new BonusController();
                            if ($price['number'] > 1) {
                                $data['number'] = $price['price'];
                                for ($i=0; $i < $price['number']; $i++) { 
                                    $data1[] = $data;
                                    
                                }
                                $res = M("bonus") -> addAll($data1);

                                if ($res) {
                                    for ($i=0; $i < $price['number']; $i++) { 
                                        $bonus_dis -> setUser(['id' => $price['id'], 'pid' => $price['pid']]);
                                        $bonus_dis -> setMoney($price['price']);
                                        $bonus_dis -> setOrder($price['order']);
                                        $res_dis = $bonus_dis -> getParent();
                                        if ($res_dis != true) {
                                            $this -> error($bonus_dis -> getError());
                                            exit();
                                        }
                                    } 
                                }

                            } else {
                                $data['number'] = $price['price'];
                                $res = M("bonus") -> add($data);
                                if ($res) {
                                    $bonus_dis -> setUser(['id' => $price['id'], 'pid' => $price['pid']]);
                                    $bonus_dis -> setMoney($price['price']);
                                    $bonus_dis -> setOrder($price['order']);
                                    $res_dis = $bonus_dis -> getParent();
                                    if ($res_dis != true) {
                                        $this -> error($bonus_dis -> getError());
                                        exit();
                                    }
                                }
                            }

                            break;
                        case '2': //报单
                            $data1['user_id'] = $product['user_id'];
                            $data1['repeats'] = 0;
                            $data1['water'] = 0;
                            $data1['time'] = time();
                            $data1['interest'] = 0;
                            $data1['releases'] = 0;
                            $data1['time_end'] = strtotime("-0 year -6 month -0 day");
                            //购买时CMC的价格

                            $info = M()
                                -> table("currency_shop_order as o")
                                -> field("o.number, p.price, o.cmc, p.integral")
                                -> join("left join currency_product as p on p.id = o.product_id")
                                -> where("o.id = ". $id)
                                -> find();

                            if ($info['number'] > 1) {
                                for ($i=0; $i < $info['number']; $i++) { 
                                    $data[] = array_merge(['number' => $info['integral'], 'number_all' => $info['integral'], 'price' => $info['cmc']], $data1);
                                }
                                $res = M("integral") -> addAll($data);
                            } else {
                                $data['price'] = $info['cmc'];
                                $data['number'] = $info['integral'];
                                $data['number_all'] = $info['integral'];
                                $data = array_merge($data1, $data);
                                $res = M("integral") -> add($data);
                            }
                             // var_dump($data);
                            break;
                        case '3': //重消
                            $this -> success("确认收货");
                        default:

                            break;
                    }

                    if ($res) {
                        $this -> success("发货成功");
                    } else {

                        $this -> error("发货失败，请稍后重试1");
                    }
                } else {
                    M("product") -> rollback();
                    $this -> error("发货失败");
                }
            } else {
                $this -> error("发货失败");
            }
        }
    }
}
