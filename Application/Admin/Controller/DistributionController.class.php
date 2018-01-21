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
        $bankrecevie = M("bankreceive");
        $banktype = M("banktype");
        $id = I("id") ? I("id") : "";

        $bank = $banktype -> where("status = 1")  -> field("id, bankname") -> select();
        $bankcard =$bankrecevie -> field("id, bank, bankcard, sort, payee") -> order("sort desc") -> where("status = 1") -> select();

        //如果当前没有默认地址，则设第一个地址为默认地址
        $default_count = $bankrecevie -> field("sort") -> where("sort = 1") -> count();
        if ($bankcard && $default_count <= 0) {
            $res = $bankrecevie -> where("id = ". $bankcard[0]['id']) -> save(array("sort" => 1));
            if (!$res) {
                $this -> error("设置默认失败");
            }
        }
        //添加、修改地址
        if ($_POST) {
            $data['bank'] = I("bankid");
            $data['bankcard'] = I("bankcard");
            $data['payee'] = $this -> strFilter(I("payee")) ? $this -> strFilter(I("payee")) : "";
            $data['addtime'] = time();
            $data['endtime'] = time();
            $data['status'] = 1;
            if ($id != "") {
                $res = $bankrecevie -> where(array("id" => $id)) -> save($data);
                $msg = "修改";
            } else {
                $res = $bankrecevie -> add($data);
                $msg = "添加";
            }
            if ($res) {
                $this -> success($msg. "成功");
            } else {
                $this -> error($msg. "失败");
                exit;
            }
        }
        $this -> assign("bankcard", $bankcard);
        $this -> assign("bank", $bank);
        $this -> display();
    }

    //点击删除人民币收款地址
    public function delete() {
        $id = I("id");

        if (M("bankreceive") -> where("id = ". $id) -> delete()) {
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
            $bankedit = M("bankreceive") -> where(array("id" => $id)) -> field("id, bank, bankcard, payee") -> find();
            echo json_encode($bankedit);
        }
    }
}
