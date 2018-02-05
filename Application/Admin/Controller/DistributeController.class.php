<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/3 0003
 * Time: 上午 9:23
 */

namespace Admin\Controller;


class DistributeController extends AdminController
{
    /**
     * 推广配置
     */
    function index(){
        $distribute_m=M('distribute');
        if (IS_POST){
            $data['data']=I('data');
            if ($data['data']===""){
                $this->error('非法操作！');
                exit();
            }

            $sava_back=$distribute_m->where(array('id'=>1))->save($data);
            if ($sava_back===false){
                $this->error('保存失败！');
                exit();
            }
            $this->success('保存成功！');
            exit();
        }
        $set_data=$distribute_m->where(['id'=>1])->find();
        $set_data['data']=json_decode($set_data['data'],true);
        ksort($set_data['data']);
        $this->assign('data',$set_data['data']);
        $this->display();
    }


    /**
     *
     */
    function nameInfo(){
        $distribute_m=M('distribute');
        if (IS_POST){

            $data['data']=I('data');
            if ($data['data']===""){
                $this->error('非法操作！');
                exit();
            }

            $sava_back=$distribute_m->where(array('id'=>2))->save($data);
            if ($sava_back===false){
                $this->error('保存失败！');
                exit();
            }
            $this->success('保存成功！');
            exit();
        }
        $set_data=$distribute_m->where(['id'=>2])->find();
        $set_data['data']=json_decode($set_data['data'],true);
        ksort($set_data['data']);
        $this->assign('data',$set_data['data']);
        $this->display();
    }

}