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
class AddressController extends HomeController {
    public function __construct(){
        parent::__construct();
        if (session('user')['id']==""){
            $this->redirect('Index/index');
            exit();
        }
    }

    //收货地址列表
    public function index() {
        //默认地址
        $default = M()
            -> table("currency_shop_address as sa")
            -> field("sa.id, sa.name, sa.mobile, sa.address, sc.city_name as province, scc.city_name as city , sccc.city_name as area, sa.status")
            -> join("left join currency_shop_city as sc on sc.id = sa.province")
            -> join("left join currency_shop_city as scc on scc.id = sa.city")
            -> join("left join currency_shop_city as sccc on sccc.id = sa.area")
            // -> where("sa.user_id = ". session('user')['id']. " AND sa.status = 1")
            -> where("sa.user_id = ". session('user')['id'])
            -> find();

        //地址列表
        $city_list = M()
            -> table("currency_shop_address as sa")
            -> field("sa.id, sa.name, sa.mobile, sa.address, sc.city_name as province, scc.city_name as city , sccc.city_name as area, sa.status")
            -> join("left join currency_shop_city as sc on sc.id = sa.province")
            -> join("left join currency_shop_city as scc on scc.id = sa.city")
            -> join("left join currency_shop_city as sccc on sccc.id = sa.area")
            -> where("sa.user_id = ". session('user')['id'])
            -> select();

        //城市
        $city = $this -> city(0);

        $this -> assign("default", $default);
        $this -> assign("list", $city_list);
        $this -> assign("city", $city);
        $this -> display();
    }

    //添加收货地址
    public function update() {
        $id       =  $this->strFilter(I("id"))       ? I("id")       : null;
        $province =  $this->strFilter(I("province")) ? I("province") : null;
        $city     =  $this->strFilter(I("city"))     ? I("city")     : null;
        $area     =  $this->strFilter(I("area"))     ? I("area")     : null;
        $address  =  $this->strFilter(I("address"))  ? I("address")  : null;
        $name     =  $this->strFilter(I("name"))     ? I("name")     : null;
        $mobile   =  $this->strFilter(I("mobile"))   ? I("mobile")   : null;

        if ($province == null || $city == null || $area == null || $address == null || $name == null || $mobile == null) {
            $this->error("*为必填项");
        } else {
            $data['province'] = $province;
            $data['city']     = $city;
            $data['area']     = $area;
            $data['address']  = $address;
            $data['name']     = $name;
            $data['mobile']   = $mobile;
            $data['user_id']  = session('user')['id'];
            $data['status']   = 2;

            if ($id != null) {
                $data['id'] = $id;
                $res = M("shop_address") -> save($data);
            } else {
                $res = M("shop_address") -> add($data);
            }

            if(!$res){
                $this->error(D('shop_address')->getError());
            }else{
                $this->success($res>1?'新增成功':'更新成功', U('index'));
            }
        } 
    }

    //选择城市
    public function choosecity($id = 0) {
        if ($id != 'choose') {
            $city = $this -> city($id);
        }
        
        $this->ajaxReturn(['city' => $city]);
    }

    //城市
    public function city($id = 0) {
        $city = M("shop_city") -> field("id, city_name") -> where("pid = ". $id) -> select();

        return $city;
    }
}
