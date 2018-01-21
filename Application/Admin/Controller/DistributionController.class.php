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

        $list = $bonus -> find();

        //添加、修改地址
        if (IS_POST) {

            $data = json_encode(I('data'));
            $back = $bonus->where(['id'=>1])->save(['data'=>$data]);
            if (!$back){
                $this->error('保存失败');
            }

            $this->success('保存成功');

            exit();

        }

        $data = json_decode($list['data'],true);
        $this -> assign("data", $data);
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
