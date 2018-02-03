<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 11:47
 */

namespace Admin\Model;


use Think\Model;

class MemoryListModel extends Model
{

    /**
     * @param $all_id 发放期数的id
     * @param $number 发放的数量
     * @param $bonus_id 购买期数的id
     */

    public function addData($all_id,$number,$bonus_id){

       return $this->add([
            'all_id'=>$all_id,
            'number'=>$number,
            'bonus_id'=>$bonus_id,
            'time'=>time()
        ]);

    }

}