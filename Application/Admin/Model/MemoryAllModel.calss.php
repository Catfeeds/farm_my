<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 11:47
 */

namespace Admin\Model;


use Think\Model;

class MemoryAllModel extends Model
{

    public $id;

    public function addNullBonusAll(){
        $back = $this->add([
            'number'=>0,
            'time'=>time()
        ]);

        if (!$back){
            return false;
        }else{
            $this->id = $back;
            return $this;
        }
    }


    public function saveNullBonusAll($number){

        return $this->where(['id'=>$this->id])->save(['number'=>$number]);

    }

}