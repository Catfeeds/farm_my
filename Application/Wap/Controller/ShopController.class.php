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
use Wap\Model\ProductModel;
use Common\Controller\CmcpriceController;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ShopController extends WapController {
    private $cat_id = 1;
    private $search = "";
    private $Page;

    public function __construct() {
        parent::__construct();

        // if (session('user')['id']==""){
        //     $this->redirect('Index/index');
        //     exit();
        // }
        
        $cate = $this -> gettree();

        $this -> assign("cate", $cate);
    }

	//系统首页
    public function index(){
        //产品列表
        $res = M() 
            -> table("currency_product as p")
            -> field("p.id, p.name, p.price, p.img, p.cat_id, pc.type, pc.name as class_name") 
            -> join("left join currency_procate as pc on pc.id = p.cat_id")
            -> where("3 > (select count(*) from currency_product as pro where p.cat_id = pro.cat_id and pro.sort > p.sort ) and p.status = 1")
            -> order("p.cat_id, p.sort")
            -> select();

        foreach ($res as $key => $value) {
            $res[$key]['price_show'] = $this -> getPriceShow($value['type'], $value['price'], $value['id']);
        }

        $data = [];
        foreach ($res as $key => $value) {
            // $data[$value['class_name']]["name"] = $value['class_name'];
            $data[$value['class_name']][] = $value;
            
        }
        // dump($data);
        $list = array();
        foreach ($data as $key => $value) {
            // dump($key);
            $list[$key] = ['name' => $key, 'cat_id' => $value[0]['cat_id']];
            for ($i=0; $i < ceil(count($value)); $i++) { 
                if (!empty(array_slice($value, $i * 3 ,3))) {
                    $list[$key]["array"][] = array_slice($value, $i * 3 ,3);
                }
            } 
        }
        // dump($list);
        //广告列表
        // $ads = M("shop_ads") -> field("url, name, desc, img, type") -> where("status", 1) -> select();

        return $list;
        // $this -> assign("ads", $ads);
        // $this -> assign("list", $list);
        // $this -> display();
    }

    //详情
    public function single($id) {
        $info = M() 
            -> table("currency_product as p")
            -> field("p.id, p.name, p.price, p.img, p.description, p.cat_id, pc.name as class_name, pc.type") 
            -> join("left join currency_procate as pc on pc.id = p.cat_id")
            -> where("p.id = ". $id) 
            -> find();
        
        $price_show = $this -> getPriceShow($info['type'], $info['price'], $info['id']);

        $this -> assign("price_show", $price_show);
        $this -> assign("info", $info);
        $this -> display();
    }

    //产品列表页
    public function lists() {
        $this -> cat_id   = $this -> strFilter( I( 'cat_id' ) ) ? $this -> strFilter( I( 'cat_id' ) ) : null;
        // $this -> search = $this -> strFilter( I( 'search' ) ) ? $this -> strFilter( I( 'search' ) ) : "";

        // $product = M("product");
        // $where = "p.status = 1";
        // if ($this -> cat_id != null) {
        //     $where .= " AND p.cat_id = ". $this -> cat_id;
        // } 

        // $count  = M()-> table("currency_product as p")->where($where)->count();// 查询满足要求的总记录数
        // $show   = $this  -> getPage( $count );

        // $res = M() 
        //     -> table("currency_product as p")
        //     -> field("p.id, p.name, p.price, p.img, p.cat_id, pc.type") 
        //     -> join("left join currency_procate as pc on pc.id = p.cat_id")
        //     -> where($where) 
        //     -> limit( $this -> Page -> firstRow.','. $this -> Page -> listRows ) 
        //     -> select();
        // // var_dump($res);

        // foreach ($res as $key => $value) {
        //     $res[$key]['price_show'] = $this -> getPriceShow($value['type'], $value['price'], $value['id']);
        // }

        // $list = array();
        // for ($i=0; $i < ceil(count($res)); $i++) { 
        //     if (!empty(array_slice($res, $i * 3 ,3))) {
        //         $list[] = array_slice($res, $i * 3 ,3);
        //     }
        // }

        $cat_name = M("procate") -> field("name") -> where("id = ".$this -> cat_id) -> find();

        $this -> assign("cat_name", $cat_name);
        // $this -> assign('page',$show);// 赋值分页输出
        // $this -> assign("list", $list);

        $this -> assign("cat_id", $this -> cat_id);

        $this -> display();
    }

    //产品列表页
    public function lists_more() {
        $groupNumber = I("groupNumber") ? I("groupNumber") : 1;
        // $status = I("status") ? I("status") : 0;
        $ofset = 2;
        $this -> cat_id   = $this -> strFilter( I( 'cat_id' ) ) ? $this -> strFilter( I( 'cat_id' ) ) : null;
        $this -> search = $this -> strFilter( I( 'search' ) ) ? $this -> strFilter( I( 'search' ) ) : "";

        $product = M("product");
        $where = "p.status = 1";
        if ($this -> cat_id != null) {
            $where .= " AND p.cat_id = ". $this -> cat_id;
        } 

        // $count  = M()-> table("currency_product as p")->where($where)->count();// 查询满足要求的总记录数
        // $show   = $this  -> getPage( $count );

        $res = M() 
            -> table("currency_product as p")
            -> field("p.id, p.name, p.price, p.img, p.cat_id, pc.type") 
            -> join("left join currency_procate as pc on pc.id = p.cat_id")
            -> where($where) 
            -> limit(($ofset * ($groupNumber - 1)), $ofset) 
            -> select();
        // var_dump($res);

        foreach ($res as $key => $value) {
            $res[$key]['price_show'] = $this -> getPriceShow($value['type'], $value['price'], $value['id']);
        }

        $list = array();
        for ($i=0; $i < ceil(count($res)); $i++) { 
            if (!empty(array_slice($res, $i * 3 ,3))) {
                $list[] = array_slice($res, $i * 3 ,3);
            }
        }
        $fal['cat_id'] = $this -> cat_id;
        if (empty($res)) {
            $fal['status'] = 2;
        } else {
            $fal['list'] = $list;
        }

        // $cat_name = M("procate") -> field("name") -> where("id = ".$this -> cat_id) -> find();

        // $this -> assign("cat_name", $cat_name);
        // $this -> assign('page',$show);// 赋值分页输出
        // $this -> assign("list", $list);
        // $this -> display();
        // $fal['cat_name'] = $cat_name;
        // $fal['list'] = $list;

        $this -> ajaxReturn($fal);
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

    private function getPage($count) {
        import('ORG.Util.Page');
        $rows = 9;
        $this -> Page = new Page($count, $rows, array('cat_id' => $this -> cat_id, 'search' => $this -> search));
        //array('cat_id' => $this -> cat_id, 'search' => $this -> search)
        //设置左右
        $this -> Page -> setConfig('prev', "&laquo;");
        $this -> Page -> setConfig('next', "&raquo;");
        $show = $this -> Page -> show();

        $ex_show = explode("<a class", $show);
        for ($i = 0; $i < count($ex_show); $i ++) {
            $ex_show[$i] = "<li><a class= " . $ex_show[$i] . "</li>";
        }
        unset($ex_show[0]);
        $show = implode("", $ex_show);
        return $show;
    }

    private function getPriceShow($type, $price, $id) {
        switch ($type) {
            case 1: //红包 展示需要多少人民币
                $price_show = $price;
                break;
            case 2: //报单 展示需要多少CMC和人民币
                //获取后台配置的CMC当前价格及报单属性
                $cfg = new CmcpriceController();
                $cmc_price = $cfg -> getPrice();
                $attr = M("product") 
                    -> field("cmc, cny")
                    -> where("id = ". $id)
                    -> find();
                // var_dump($attr);

                $price_show = array("cmc" => $attr['cmc'], "cny" => $attr['cny'] * $cmc_price);
                break;
            case 3: //重消 展示需要多少人民币
                $price_show = $price;
                break;
            default:
                # code...
                break;
        }

        return $price_show;
    }
}