<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 上午 10:17
 */

namespace Home\Model;


use Think\Model;
use Think\Page;

class BonusListModel extends Model
{

    public $show;

    public $data;

    /**
     * 获取用户已经释放的金额
     */
    public function getReleaseAll(){

      return  $this->where(['currency_bonus.user_id'=>session('user')['id']])
          ->field('sum(currency_bonus_list.repeats) as repeats ,sum(currency_bonus_list.number) as number')
          ->join('left join currency_bonus on currency_bonus.id = currency_bonus_list.bonus_id')
          ->find();

    }


    public function getReleaseList($where,$page_where){
        $count = $this->where($where)->count();

        $Page = new Page($count,13,$page_where);

        $this->show = $Page->show();

        $this->data = $this->where($where)
                            ->limit($Page->firstRow,$Page->listRows)
                            ->select();

        return $this;

    }

}