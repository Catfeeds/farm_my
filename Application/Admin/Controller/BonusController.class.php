<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/18 0018
 * Time: 下午 2:24
 */

namespace Admin\Controller;


use Admin\Model\BonusAllModel;
use Admin\Model\BonusListModel;
use Admin\Model\BonusModel;
use Think\Controller;
use Think\Page;

class BonusController extends AdminController
{

    /**
     * 用户已购买红包列表
     */
    public function bonuslist(){

        $where = [];

        $bonusModel = new BonusModel();

        if (!empty(I('users'))) {
            $where['currency_users.users']= I('users');
            $bonusModel->page_where['users'] = I('users');
        }
        if (!empty(I('start_time')) && !empty(I('end_time')) ){
            $where['currency_bonus.time']= [ ['EGT',strtotime(I('start_time'))] , ['ELT',strtotime(I('end_time'))] ];
            $bonusModel->page_where['start_time']  = I('start_time');
            $bonusModel->page_where['end_time']  = I('end_time');
        }


        $bonusModel = $bonusModel->lists($where);

        $this->assign('page',$bonusModel->show);
        $this->assign('data',$bonusModel->data);

        $this->display();
    }

    /**
     * 发放期数
     */
    public function all_list(){
        $where = [];
        $bonusAllModel = new BonusAllModel();
        $bonusAllModel =  $bonusAllModel->lists($where);

        $this->assign('page',$bonusAllModel->show);
        $this->assign('data',$bonusAllModel->data);

        $this->display();
    }


    /**
     * 发放详情
     */
    public function release_list(){
        $id = I('id');

        $where= ['currency_bonus_list.all_id'=>$id];

        $user = I('users');


        $bonusListModel = new BonusListModel();

        if (!empty($user)) {
            $where['currency_users.users'] = $user;
            $bonusListModel->where_page['users']=$user;
        }

        $bonusListModel->where_page['id']=$id;

        $bonusListModel = $bonusListModel->lists($where);


        $this->assign('page',$bonusListModel->show);

        $this->assign('data',$bonusListModel->data);


        $this->display();
    }

    /**
     * 红包提成记录
     */

    public function deduct(){

        $bonus_deduct_m = M('bonus_deduct');

        $count = $bonus_deduct_m->where()->count();

        $page = new Page($count,15);

        $page = $page->show();

        $data = $bonus_deduct_m ->where()
                                ->field('pu.users as puser,cu.users as cuser,currency_bonus_deduct.*')
                                ->join('left join currency_users as pu on pu.id = currency_bonus_deduct.user_id')
                                ->join('left join currency_users as cu on cu.id = currency_bonus_deduct.child_id')
                                ->select();


        $this->assign('page',$page);
        $this->assign('data',$data);

        $this->display();
    }



}