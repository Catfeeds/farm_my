<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 下午 2:41
 */

namespace Home\Model;


use Think\Model;
use Think\Page;

class IntegralDepositModel extends Model
{
    public $show;

    public $data;

    public function getList($where,$page_where){

        $count = $this->where($where)->count();

        $Page = new Page($count,13,$page_where);

        $this->show = $Page->show();

        $this->data = $this->where($where)
            ->limit($Page->firstRow,$Page->listRows)
            ->select();

        return $this;

    }

}