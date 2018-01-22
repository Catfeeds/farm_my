<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/21 0021
 * Time: 下午 6:37
 */

namespace Common\Model;


use Think\Model;

class UsersModel extends Model
{

    public function getPid($id){

       return $this->where(['id'=>$id])->field('id,pid')->find();

    }

    public function countChild($id){
        return  $this->where(['pid'=>$id])->count();
    }

}