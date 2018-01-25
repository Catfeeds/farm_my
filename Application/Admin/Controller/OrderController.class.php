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
    public $error = "";

    public function index() {
        $status = I("status") ? I("status") : "";
        $search = I("search") ? I("search") : "";

        $map = [];
        $map1 = [];
        if ($search != "" && $search != 0) {
            $map1['o.order'] = $search;
            $map1['u.users'] = $search;
            $map1['_logic'] = "OR";
        }
        if ($status != "" && $status != 0) { 
            $map['o.status'] = $status;
            if (!empty($map1)) {
                $map['_complex'] = $map1;
            }
        }
        $count = M("shop_order o") -> where($map) -> count();
        $Page = new Page($count, 15, array('status'=>$status, 'search' => $search));
        $show = $Page -> show();
        // dump($count);
        // dump(M("shop_order")->getLastsql());

        $list = M()
            -> table("currency_shop_order as o")
            -> field("o.id, o.order, o.number, o.total_money, o.time, o.status, p.name, p.img, pc.type, u.users")
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_procate as pc on p.cat_id = pc.id")
            -> join("left join currency_users as u on o.user_id = u.id")
            -> order("o.time desc")
            -> where($map)
            -> limit($Page -> firstRow, $Page -> listRows)
            -> select();

        $this -> assign("page", $show);
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
            $product_m = M("product");
            $product_m -> startTrans();

            $res1 = M("product") -> save(['id' => $product['id'], "number" => ($product['product_number'] - $product['order_number'])]);

            if ($res1) {
                //发货
                if ($this -> send($product['type'], $id)) {
                    $product_m -> commit();
                    $this -> success("发货成功");
                } else {
                    $product_m -> rollback();
                    if ($this -> error != "") {
                        $this -> error($this -> error);
                    } else {
                        $this -> error("发货失败");
                    }
                }
            } else {
                $this -> error("发货失败");
            }
        }
    }

    //发货
    private function send($type, $id) {
        $shop_order_m = M("shop_order");
        $shop_order_m -> startTrans();

        $return = false;

        $res_shop_order = $shop_order_m -> save(['id' => $id, "status" => 2]);

        if ($res_shop_order) {
            //发红包或积分
            if ($this -> release($type, $id)) {

                $shop_order_m -> commit();
                $return = true;
            } else {
                $shop_order_m -> rollback();
            }
        } else {
            $this -> error .= "发货失败，改变订单状态失败";
        }

        return $return;
    }

    //发红包或积分
    private function release($type, $id) {
        $price = M() 
            -> table("currency_shop_order as o")
            -> field("o.total_money, o.number, o.order, o.cmc, p.integral, p.out, p.price, u.pid, u.id") 
            -> join("left join currency_product as p on p.id = o.product_id")
            -> join("left join currency_users as u on o.user_id = u.id")
            -> where("o.id = ". $id) 
            -> find();

        $return = false;

        switch ($type) {
            case '1': //红包
                $data['outs'] = $price['out'];
                $data['provide'] = 0;
                $data['time'] = time();
                $data['user_id'] = $price['id'];
                $data['number'] = $price['price'];

                $bonus_dis = new BonusController();

                $bonus_m = M("bonus");
                $bonus_m -> startTrans();

                if ($price['number'] > 1) {
                    
                    for ($i=0; $i < $price['number']; $i++) { 
                        $data1[] = $data;
                    }

                    $res = $bonus_m -> addAll($data1);

                    if ($res) {
                        $m = 0;
                        for ($i=0; $i < $price['number']; $i++) { 
                            $dis = $this -> distribution($bonus_dis, $price['id'], $price['pid'], $price['price'], $price['order']);
                            if ($dis != true) {
                                break;
                            }
                            $m ++;
                        } 
                        if ($m >= $price['number'] - 1) {
                            $bonus_m -> commit();
                            $return = true;
                        } else {
                            $bonus_m -> rollback();
                        }
                    } else {
                        $this -> error .= "批量发放红包失败";
                    }

                } else {
                    $res = $bonus_m -> add($data);
                    if ($res) {
                        $dis = $this -> distribution($bonus_dis, $price['id'], $price['pid'], $price['price'], $price['order']);
                        if ($dis != true) {
                            $bonus_m -> rollback();
                        } else {
                            $bonus_m -> commit();
                            $return = true;
                        }
                    } else {
                        $this -> error .= "发放红包失败";
                    }
                }

                break;
            case '2': //报单
                $data1['user_id'] = $price['id'];
                $data1['repeats'] = 0;
                $data1['water'] = 0;
                $data1['time'] = time();
                $data1['interest'] = 0;
                $data1['releases'] = 0;
                $data1['time_end'] = strtotime("-0 year -6 month -0 day");

                $integral_m = M("integral");

                if ($price['number'] > 1) {
                    for ($i=0; $i < $price['number']; $i++) { 
                        $data[] = array_merge(['number' => $price['integral'], 'number_all' => $price['integral'], 'price' => $price['cmc']], $data1);
                    }

                    $res = $integral_m -> addAll($data);
                    if ($res) {
                        $return = true;
                    } else {
                        $this -> error = "批量发放积分失败";
                    }
                } else {
                    $data['price'] = $price['cmc'];
                    $data['number'] = $price['integral'];
                    $data['number_all'] = $price['integral'];
                    $data = array_merge($data1, $data);
                    $res = $integral_m -> add($data);
                    if ($res) {
                        $return = true;
                    } else {
                        $this -> error .= "发放积分失败";
                    }
                }
                 // var_dump($data);
                break;
            case '3': //重消
                $return = true;
                // $this -> success("确认收货");
            default:

                break;
        }

        return $return;
    }

    //红包分销
    private function distribution($bonus_dis, $id, $pid, $price, $order) {
        $bonus_dis -> setUser(['id' => $id, 'pid' => $pid]);
        $bonus_dis -> setMoney($price);
        $bonus_dis -> setOrder($order);
        $res_dis = $bonus_dis -> getParent();
        if ($res_dis != true) {
            $this -> error .= $bonus_dis -> getError();
        }

        return $res_dis;
    }
}
