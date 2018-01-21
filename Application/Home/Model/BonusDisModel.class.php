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

class BonusDisModel extends Model
{

    //查询自己的父级所分享过的人数
    public function getPid($user_id) {
        $pid = M("users") -> field("pid") -> where("id = ". $user_id) -> find();

        return $pid['pid'];
    }

    public function getShares($id) {
        $num = M("users") -> field("count(id)") -> where("pid = ". $id) -> find();
    }
}