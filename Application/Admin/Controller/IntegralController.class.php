<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19 0019
 * Time: 上午 10:27
 */

namespace Admin\Controller;

use Think\Page;

class IntegralController extends AdminController
{


    /**
     * 用户积分列表
     */
     public function integralLst(){

         $integral_m = M('integral');

         $where = [];

         $page_where = [];

         if (!empty(I('users'))){
             $where['currency_users.users'] = I('users');
             $page_where['users'] = I('users');
         }

         if (!empty(I('start_time')) && !empty(I('end_time'))){
             $where['currency_integral.time'] =[ ['egt',strtotime(I('start_time'))] ,['elt',strtotime(I('end_time'))+86400]];
             $page_where['start_time'] = I('start_time');
             $page_where['start_time'] = I('end_time');
         }


         $count = $integral_m->where($where)
             ->field('currency_integral.*,currency_users.users')
             ->join('left join  currency_users on currency_integral.user_id = currency_users.id')
             ->count();

         $Page = new Page($count,15);

         $show = $Page->show();

         $data = $integral_m->where($where)
                            ->field('currency_integral.*,currency_users.users')
                            ->join('left join  currency_users on currency_integral.user_id = currency_users.id')
                            ->limit($Page->firstRow,$Page->listRows)
                            ->order('currency_integral.time desc')
                            ->select();

         $this->assign('page',$show);
         $this->assign('data',$data);

         $this->display();

     }


     /**
      * 发放列表
      */
     public function release(){

         $integral_all_m = M('integral_all');

         $page_where = $where = [];

         if ( !empty(I('start_time')) && !empty(I('end_time'))){
             $where['time']=[['egt',strtotime(I('start_time'))],['elt',strtotime(I('end_time'))+86400]];
             $page_where['start_time'] = I('start_time');
             $page_where['end_time'] = I('end_time');
         }

         $count =  $integral_all_m->where($where)->count();

         $Page = new Page($count,15,$page_where);

         $show = $Page->show();

         $data = $integral_all_m->where($where)->limit($Page->firstRow,$Page->listRows)->order('time desc')->select();


         $this->assign('page',$show);
         $this->assign('data',$data);

         $this->display();
     }

    /**
     * 发放详情
     */
     public function list_release(){

         $id = I('id');

         $integral_list_m = M('integral_list');

         $where['currency_integral_list.all_id'] = $page_where['id'] = $id;

         if (!empty(I('users'))) $page_where['users'] = $where['currency_users.users'] =  I('users');

         $count = $integral_list_m->where($where)
                                  ->field('currency_integral_list.*,currency_users.users')
                                  ->join('left join currency_integral on currency_integral.id=currency_integral_list.integral_id')
                                  ->join('left join currency_users on currency_users.id = currency_integral.user_id')
                                  ->count();

         $Page = new Page($count,15,$page_where);

         $data = $integral_list_m->where($where)
                                 ->field('currency_integral_list.*,currency_users.users')
                                 ->join('left join currency_integral on currency_integral.id=currency_integral_list.integral_id')
                                 ->join('left join currency_users on currency_users.id = currency_integral.user_id')
                                 ->limit($Page->firstRow,$Page->listRows)
                                 ->select();

         $show = $Page->show();

         $this->assign('page',$show);

         $this->assign('data',$data);

         $this->display();
     }



}