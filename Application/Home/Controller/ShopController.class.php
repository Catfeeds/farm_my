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
class ShopController extends HomeController {
    private $cat_id = 1;
    private $search = "";
    private $Page;

    public function __construct() {
        parent::__construct();
        $cate = $this -> gettree();

        $this -> assign("cate", $cate);
    }

	//系统首页
    public function index(){
        //产品列表
        $res = M("product") -> field("id, name, price, img") -> select();

        $list = array();
        for ($i=0; $i < ceil(count($res)); $i++) { 
            if (!empty(array_slice($res, $i * 3 ,3))) {
                $list[] = array_slice($res, $i * 3 ,3);
            }
        }

        //广告列表
        $ads = M("shop_ads") -> field("url, name, desc, img, type") -> where("status", 1) -> select();


        $this -> assign("ads", $ads);
        $this -> assign("list", $list);
        $this -> display();
    }

    //详情
    public function single($id) {
        $info = M("product") -> field("id, name, price, img, description") -> where("id = ". $id) -> find();

        $this -> assign("info", $info);
        $this -> display();
    }

    //产品列表页
    public function lists() {
        $this -> cat_id   = $this -> strFilter( I( 'cat_id' ) ) ? $this -> strFilter( I( 'cat_id' ) ) : null;
        $this -> search = $this -> strFilter( I( 'search' ) ) ? $this -> strFilter( I( 'search' ) ) : "";

        $product = M("product");
        $where = "status = 1";
        if ($this -> cat_id != null) {
            $where .= " AND cat_id = ". $this -> cat_id;
        } 

        $count  = $product->where($where)->count();// 查询满足要求的总记录数
        $show   = $this  -> getPage( $count );

        $res = $product -> field("id, name, price, img, cat_id") -> where($where) -> limit( $this -> Page -> firstRow.','. $this -> Page -> listRows ) -> select();

        $list = array();
        for ($i=0; $i < ceil(count($res)); $i++) { 
            if (!empty(array_slice($res, $i * 3 ,3))) {
                $list[] = array_slice($res, $i * 3 ,3);
            }
        }

        $cat_name = M("procate") -> field("name") -> where("id", $this -> cat_id) -> find();

        $this -> assign("cat_name", $cat_name);
        $this -> assign('page',$show);// 赋值分页输出
        $this -> assign("list", $list);
        $this -> display();
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

}