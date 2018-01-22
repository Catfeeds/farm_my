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

    /**
     * 直推人数
     * @param $id
     * @return mixed
     */
    public function countChild($id){
        return  $this->where(['pid'=>$id])->count();
    }

    /**
     * 计算团队人数
     * @param $id
     */
    public function countChild_all($id,&$number=0){

        $data = $this->where(['pid'=>$id])->field('id,pid')->select();

        if (!empty($data[0]['id'])){

            foreach ($data as $k=>$v){
                $number +=1;
                $this->countChild_all($v['id'],$number);
            }

        }

    }


}