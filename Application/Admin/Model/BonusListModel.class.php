<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 10:46
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class BonusListModel extends Model
{


    public $show;
    public $data;
    public $where_page;
    /**
     * @param $all_id 发放期数的id
     * @param $number 发放的cny
     * @param $repeats 发放的重消
     * @param $bonus_id  购买期数的id
     * @return mixed
     */

    public function addList($bonus_id,$number,$repeats,$all_id){

        return $this->add([
                    'all_id'=>$all_id,
                    'number'=>$number,
                    'repeats'=>$repeats,
                    'bonus_id'=>$bonus_id,
                    'time'=>time()
                ]);
    }

    public function lists($where){

            $count  = $this ->where($where)
                    ->join('left join currency_bonus on currency_bonus.id = currency_bonus_list.all_id')
                    ->join('left join currency_users on currency_bonus.user_id = currency_users.id')
                    ->count();

            $Page = new Page($count,15);

            $this->show = $Page->show();

            $this->data = $this ->where($where)
                  ->field('currency_bonus_list.*,currency_users.users')
                  ->limit($Page->firstRow.','.$Page->listRows)
                  ->join('left join currency_bonus on currency_bonus.id = currency_bonus_list.bonus_id')
                  ->join('left join currency_users on currency_bonus.user_id = currency_users.id')
                  ->select();
            return $this;

    }


}