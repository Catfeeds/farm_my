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
/**
 * 前台首页控制器
 * 主要获取首页聚合数据 
 */
class AddressController extends WapController {
    public function __construct(){
        parent::__construct();
        // if (session('user')['id']==""){
        //     $this->redirect('Index/index');
        //     exit();
        // }
    }

    //收货地址列表
    public function index() {
        $where = ['sa.user_id' => session("user_wap")['id']];

        //地址列表
        $city_list = M()
            -> table("currency_shop_address as sa")
            -> field("sa.id, sa.name, sa.mobile, sa.address, sc.city_name as province, scc.city_name as city , sccc.city_name as area, sa.status")
            -> join("left join currency_shop_city as sc on sc.id = sa.province")
            -> join("left join currency_shop_city as scc on scc.id = sa.city")
            -> join("left join currency_shop_city as sccc on sccc.id = sa.area")
            -> where($where)
            -> order("sa.id desc")
            -> select();

        $this -> assign("list", $city_list);

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
            $data['user_id']  = session('user_wap')['id'];
            $data['status']   = 2;

            if ($id != null || $id != "") {
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

    public function add() {
        $id = I("id") ? I("id") : null;

        if ($id != null) {
            $city_list = $this -> getAddress($id);
            
            $this -> assign("city_list", $city_list);
        }
        
        $city = $this -> city(0);
        $this -> assign("city", $city);
        // dump($city);
        $this -> display();
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

    //修改
    public function getAddress($id) {

        $city_list = M()
            -> table("currency_shop_address as sa")
            -> field("sa.id, sa.name, sa.mobile, sa.address, sc.id as province_id, sc.city_name as province, scc.id as city_id, scc.city_name as city, sccc.id as area_id, sccc.city_name as area, sa.status")
            -> join("left join currency_shop_city as sc on sc.id = sa.province")
            -> join("left join currency_shop_city as scc on scc.id = sa.city")
            -> join("left join currency_shop_city as sccc on sccc.id = sa.area")
            -> where(['sa.id'  => $id])
            -> find();

        $city_list1 = M("shop_city")
            -> field("id, city_name")
            -> where("pid = ". $city_list['province_id'])
            -> select();

        $area_list = M("shop_city")
            -> field("id, city_name")
            -> where("pid = ". $city_list['city_id'])
            -> select();

        $city_list['city_list'] = $city_list1;
        $city_list['area_list'] = $area_list;

        return $city_list;
    }

    //删除
    public function delete($id) {
        $res = M("shop_address")
            -> where("id = ".$id)
            -> delete();
        if ($res) {
            $this -> success("删除成功");
        } else {
            $this -> error("删除失败");
        }
    }

    //默认地址
    public function setDefault() {
        $id = I("id");
        $res = M("shop_address")
            -> save(['id' => $id, "status" => 1]);
        if ($res !== false) {
            $this -> success("设置成功");
        } else {
            $this -> error("设置失败");
        }
    }
}
