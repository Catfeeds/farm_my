<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 下午 2:27
 */

namespace Home\Controller;


use Common\Controller\IntegraldepositController;
use Home\Model\IntegralDepositModel;
use Home\Model\IntegralListModel;
use Home\Model\IntegralModel;
use Home\Model\IntegralReleaseListModel;
use Home\Model\UserpropertyModel;
use Think\Exception;

class IntegralController extends HomeController
{

    /**
     * 用户积分期数
     */
    public function index(){
        $IntegralModel = new IntegralModel();

        $integral = $IntegralModel->where(['user_id'=>session('user')['id']])->field('sum(releases) as integral')->find();

        $this->assign('integral',$integral);

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


   /**
     * 积分提现
     */

   public function deposit(){

       $IntegralModel = new IntegralModel();

       if (IS_POST){

            $type = I('type');

            $number = I('number');

            $password = I('password');

            if (jiami($password) != session('user.dealpwd')){

                $this->error('交易密码不正确！');
                exit();

            }

            $integraldepositController = new IntegraldepositController();

            $back = $integraldepositController->setNumber($number);

            if (!$back){

                $this->error('你的余额不足！');
                exit();
            }

            $IntegralModel->startTrans();

            try{

                $back = $integraldepositController->getList();

                if (!$back){
                    throw new Exception($integraldepositController->getError());
                }

                $back = true;

                if ($type == 1){
                    $back = $integraldepositController->release_cny($type);
                }else{
                    $back = $integraldepositController->release_cmc($type);
                }

                if (!$back){
                    throw new Exception($integraldepositController->getError());
                }
                $IntegralModel->commit();

                $this->success('提现成功！');

            }catch (\Exception $e){

                $IntegralModel->rollback();

                $this->error($e->getMessage());

            }


        exit();
       }


       #提现流水
       $IntegralDepositModel = new IntegralDepositModel();

       $data = $IntegralDepositModel->getList(['user_id'=>session('user.id')]);

       $this->assign('page',$data->show);
       $this->assign('data',$data->data);


       $integral = $IntegralModel->where(['user_id'=>session('user')['id']])->field('sum(releases) as integral')->find();

       $this->assign('integral',$integral);

       $this->display();

   }





}