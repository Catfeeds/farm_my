<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 10:46
 */

namespace Admin\Model;


use Think\Model;

class BonusListModel extends Model
{

    public function addList($all_id,$number){

        return $this->add([
                    'all_id'=>$all_id,
                    'number'=>$number,
                    'time'=>time()
                ]);
    }

}