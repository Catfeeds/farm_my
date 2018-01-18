<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/13 0013
 * Time: 下午 3:38
 */

namespace Home\Model;


use Think\Model;

class UsersModel extends Model
{
    function getUserData($id,$field){

        return $this->where(['id'=>$id])->field('id,users,'.$field)->find();

    }

}