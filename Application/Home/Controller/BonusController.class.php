<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 上午 10:11
 */

namespace Home\Controller;


use Home\Model\BonusListModel;
use Home\Model\BonusModel;

class BonusController extends HomeController
{

    /**
     * 用户红包首页
     */
    public function index(){

        #查询用户红包释放的总数（重消和cny）
        $BonusListModel = new BonusListModel();

        $this->assign('release',$BonusListModel->getReleaseAll());
        $where = $page_where = array();

        if (!empty(I('date')) && !empty(I('dates')))  {
            $where = ['time'=>[ ['egt',strtotime(I('date'))],['elt',strtotime(I('dates'))+86400] ] ];
            $page_where['date'] = I('date');
            $page_where['dates'] = I('dates');
        }

        $where['user_id'] = session('user')['id'];

        $bonusModel =  new BonusModel();

        $bonusModel = $bonusModel->getList($where,$page_where);

        $this->assign('data',$bonusModel->data);

        $this->assign('page',$bonusModel->show);

        $this->display();

    }


    /**
     *发放详情
     */
    public function indexInfo(){

        $id = I('id');

        $bonusListModel = new BonusListModel();

        $where['bonus_id'] = $page_where['id'] = $id;

        if (!empty(I('date')) && !empty(I('dates')))  {
            $where = ['time'=>[ ['egt',strtotime(I('date'))],['elt',strtotime(I('dates'))+86400] ] ];
            $page_where['date'] = I('date');
            $page_where['dates'] = I('dates');
        }

        $page_where['tag'] = 10;
        $bonusListModel = $bonusListModel->getReleaseList($where,$page_where);


        $this->assign('page',$bonusListModel->show);
        $this->assign('data',$bonusListModel->data);

        $this->display();
    }


}