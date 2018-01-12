<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Page;

/**
 * 后台内容控制器
 * @author huajie <banhuajie@163.com>
 */
class AddtransactionController extends AdminController {
    public 	function index(){
        $id['id']=array('neq',1);
        $xnb=M('xnb')->field('brief,id,name')->where($id)->select();

        $this->assign("xnb",$xnb);
        $mark=M('markethouse')->field('id,name')->select();

        $this->assign("mark",$mark);
        $this->display();
    }
    public function join(){
        $data['xnb']=$this->strFilter(I('xnb'));
//        $data['type']=$this->strFilter(I('type'));//买卖状态  后期恢复
        $data['market']=$this->strFilter(I('mark'));
        $id['id']=$data['xnb'];
//        standardmoney
        $mark['id']=$data['market'];
        $rest=M('markethouse')->field('standardmoney')->where($mark)->find();
        $data['standardmoney']=$rest['standardmoney'];
        $xnb=M('xnb')->field('market')->where($id)->find();
        $min=I('minprice');
        $max=I('maxprice');
        $minnum=I('minnum');
        $maxnum=I('maxnum');
        $time=strtotime(I('time'));
        $maxtime=$this->strFilter(I('maxtime'))*60;
        $model=M('transactionrecords');
        $allnum=$this->strFilter(I('allnum'));
        $ars=array();
        for ($j=0;$j<$allnum;$j++){
            $ars[]=$time+rand(0,$maxtime);
            sort($ars);
        }
        function randomFloat($min = 0, $max = 1) {
            return $min + mt_rand() / mt_getrandmax() * ($max - $min);
        }
        for ($i=0;$i<$allnum;$i++){
            $data['time']=$ars[$i];
            $data['type']=rand(1,2);//测试随机为买或者卖；后期删除
            $data['price']=round(randomFloat($min, $max),6);
            $data['number']=round(randomFloat($minnum, $maxnum),6);
//            $data['market']=$xnb['market'];
//            var_dump($data);
            $model->add($data);
        }
        $this->success("添加成功");
    }

	
}
