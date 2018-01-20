<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 下午 2:27
 */

namespace Home\Controller;


use Home\Model\IntegralListModel;
use Home\Model\IntegralModel;
use Home\Model\IntegralReleaseListModel;

class IntegralController extends HomeController
{

    /**
     * 用户积分期数
     */
    public function index(){

        $integralModel = new IntegralModel();

        $where = $page_where = [];

        $integralModel = $integralModel->getList($where,$page_where);

        $this->assign('page',$integralModel->show);

        $this->assign('data',$integralModel->data);

        $this->display();

    }


    /**
     * 复利详情
     */
   public function interest(){

       $id = I('id');

       $where['id'] = $page_where[] = $id;

       $IntegralListModel = new IntegralListModel();

       $IntegralListModel = $IntegralListModel->getList(['integral_id'=>$id],['id'=>$id]);

       $this->assign('page',$IntegralListModel->show);

       $this->assign('data',$IntegralListModel->data);

       $this->display();
   }


    /**
     * 释放详情
     */
   public function release(){

       $id = I('id');

       $where['id'] = $page_where[] = $id;

       $IntegralReleaseListModel= new IntegralReleaseListModel();

       $IntegralListModel = $IntegralReleaseListModel->getList(['integral_id'=>$id],['id'=>$id]);

       $this->assign('page',$IntegralListModel->show);

       $this->assign('data',$IntegralListModel->data);

       $this->display();

   }



}