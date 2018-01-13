<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;
use Think\Page;
class MotionController extends AdminController {

   public function index(){

       import('ORG.Util.Page');// 导入分页类
       $Data = M('keepmoney');// 实例化Data数据对象  date 是你的表名
//       $brief=$this->strFilter(I('brief'));
//       $map['brief'] = array('like',"%".$brief."%");
        //查出虚拟币
       $xnb_list = M("xnb") -> field("id, name, brief") -> where("id <> 1") -> select();
       //搜索
       $xnbid = $this -> strFilter(I('xnbid')) ? $this -> strFilter(I('xnbid')) : "";
       $name = $this -> strFilter(I('name')) ? $this -> strFilter(I('name')) : "";
       $type = $this -> strFilter(I('type')) ? $this -> strFilter(I('type')) : "";
       $map_1['currency_keepmoney.userid'] = $name;
       $map_1['currency_keepmoney.username'] = array("like", "%". $name ."%");
       $map_1['_logic'] = "OR";
       $map['_complex'] = $map_1;
       if ($xnbid != "") {
           $map['currency_keepmoney.xnb'] = $xnbid;
       }
       switch ($type) {
           case 1:$map['currency_keepmoney.type'] = "买入虚拟币";break;
           case 2:$map['currency_keepmoney.type'] = "卖出虚拟币";break;
           default:break;
       }
       $count = $Data -> where($map) -> count();// 查询满足要求的总记录数 $map表示查询条件
       $Page = new Page($count,15, array('name' => $name, 'xnbid' => $xnbid, 'type' => $type));// 实例化分页类 传入总记录数 传入状态；
       $show = $Page->show();// 分页显示输出

       // 进行分页数据查询
       $list = $Data
           -> join("currency_xnb on currency_keepmoney.xnb = currency_xnb.id")
           -> where($map)
           -> field("currency_keepmoney.*, currency_xnb.name")
           -> order('currency_keepmoney.time desc')
           -> limit($Page->firstRow.','.$Page->listRows)
           -> select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
       $this -> assign('xnb_list', $xnb_list);
       $this->assign('data',$list);// 赋值数据集
       $this->assign('page',$show);// 赋值分页输出
       $this->display(); // 输出模板


   }

    public function setup(){
        $keeppushing_m=M('keeppushing');
        if (IS_POST){
            $keep_back=$keeppushing_m->find();
            $data['storey']=I('data');
            if ($data['storey']===""){
                $this->error('非法操作！');
                exit();
            }
            if ($keep_back['id']==""){
                $add_back=$keeppushing_m->add($data);
                if ($add_back==false){
                    $this->error('保存失败！');
                    exit();
                }
            }else{
                $sava_back=$keeppushing_m->where(array('id'=>$keep_back['id']))->save($data);
                if ($sava_back===false){
                    $this->error('保存失败！');
                    exit();
                }
            }
            $this->success('保存成功！');
            exit();
        }
        $set_data=$keeppushing_m->find();
        $set_data['storey']=json_decode($set_data['storey'],true);
        ksort($set_data['storey']);
        $this->assign('data',$set_data['storey']);
        $this->display();
    }

    function repeat(){
        $repeat_cfg_m =  M('repeat_cfg');
        if (IS_POST){
            $id= I('id');
            $data = I('repeat');
            $data = $data ? $data/100 : 0;
            $back = true;
            if ($id){
                    $back =  $repeat_cfg_m->save(['id'=>$id,'data'=>$data]);
            }else{
                    $back =  $repeat_cfg_m->add(['data'=>$data]);
            }

            if ($back){
                $this->success('修改成功！');
            }else{
                $this->error('修改失败！');
            }

            exit();
        }
        

        $data= $repeat_cfg_m->find();
        $data = $data ? $data : '0.00';

        $this->assign('id',$data['id']);
        $this->assign('data',$data['data']*100);

        $this->display();
    }

}
