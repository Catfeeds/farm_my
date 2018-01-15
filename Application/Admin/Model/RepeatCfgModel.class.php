<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 10:18
 */

namespace Admin\Model;


use Think\Model;

class RepeatCfgModel extends Model
{

    public function getCfg($key){
        $data =  $this->where(['key'=>$key])->find();
        return $data['data'];
    }


}