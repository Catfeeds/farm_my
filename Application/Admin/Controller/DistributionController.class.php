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
 * 行为控制器
 * @author huajie <banhuajie@163.com>
 */
class DistributionController extends AdminController {
    //三级分销
    public function index() {
        $bonus = M("bonus_distribution");
        $id = I("id") ? I("id") : "";

        $list = $bonus -> select();

        //添加、修改地址
        if ($_POST) {
            $data['name'] = $this -> strFilter(I("name")) ? $this -> strFilter(I("name")) : "";
            $data['numpeople'] = $this -> strFilter(I("numpeople")) ? $this -> strFilter(I("numpeople")) : "";
            $data['percentage'] = $this -> strFilter(I("percentage")) ? $this -> strFilter(I("percentage")) : "";
            if ($id != "") {
                $res = $bonus -> where(array("id" => $id)) -> save($data);
                $msg = "修改";
            } else {
                $res = $bonus -> add($data);
                $msg = "添加";
            }
            if ($res) {
                $this -> success($msg. "成功");
            } else {
                $this -> error($msg. "失败");
                exit;
            }
        }
        $this -> assign("bonus", $list);
        $this -> display();
    }

    //点击删除人民币收款地址
    public function delete() {
        $id = I("id");

        if (M("bonus_distribution") -> where("id = ". $id) -> delete()) {
            $this -> success("删除成功");
        } else {
            $this -> error("删除失败");
        }
    }

    //修改
    public function edit() {
        $id = $this -> strFilter(I("id")) ? $this -> strFilter(I("id")) : "";
        //查询要修改的地址信息
        if ($id != "") {
            $bankedit = M("bonus_distribution") -> where(array("id" => $id)) -> field("id, name, numpeople, percentage") -> find();
            echo json_encode($bankedit);
        }
    }
}
