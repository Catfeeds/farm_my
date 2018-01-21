<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;

class IntegralModel extends Model
{
    private $old_allintegral;
    private $price;

    public function __construct($user_id, $price)
    {
        parent::__construct();
        $this->old_allintegral = $this->getAllIntegral($user_id);
        $this->price = $price;
    }

    //获取积分
    private function getintgral($user_id)
    {
        $num = $this->lock(true)->where("number > 0 AND user_id = " . $user_id)->field('id, number')->order("time")->find();

        return $num;
    }

    //改变积分数量
    private function setintgral($id, $number)
    {
        $res = $this->save(['id' => $id, 'number' => $number]);

        return $res;
    }

    //查询该用户最早的积分
    //判断积分数量
    //若积分数量大于价钱，减去这个积分数量
    //若积分数量小于价钱，将该积分减至0，价钱是积分减去所剩值
    public function lessintgral($user_id, $price)
    {

        //查询最早的积分
        $old_num = $this->getintgral($user_id);
        if ($this->old_allintegral < $price) {
            return "积分不足";
            exit();
        }

        //判断
        if ($old_num['number'] >= $price) {
            $new_num = $old_num['number'] - $price;
            $res = $this->setintgral($old_num['id'], $new_num);
        } else {
            $new_num = $price - $old_num['number'];
            $res1 = $this->setintgral($old_num['id'], 0);
            $res = $this->lessintgral($user_id, $new_num);
        }

        return $res;
    }

    //获取所有积分
    public function getAllIntegral($user_id)
    {
        $sum = $this->field("sum(number) as allinte")->where("user_id = " . $user_id)->find();

        return $sum['allinte'];
    }


    public $show;

    public $data;

    public function getList($where, $page_where)
    {

        $count = $this->where($where)->count();

        $Page = new Page($count, 13, $page_where);

        $this->show = $Page->show();

        $this->data = $this->where($where)
            ->limit($Page->firstRow, $Page->listRows)
            ->select();

        return $this;

    }

}