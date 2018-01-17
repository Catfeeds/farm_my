<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17 0017
 * Time: 下午 3:53
 */

namespace Admin\Model;


use Think\Model;

class IntegralListMode extends Model
{

    public function addData($integral_id,$all_id,$number,$repeats){
       return $this->add([
            'integral_id'=>$integral_id,
            'all_id'=>$all_id,
            'number'=>$number,
            'repeats'=>$repeats,
            'time'=>time()
        ]);
    }

}