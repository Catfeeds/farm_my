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

    protected $id;

    public function getId(){
        return $this->id;
    }

    public function addNullBonusAll(){
        $back = $this->add([
            'data'=>0,
            'time'=>time()
        ]);

        if (!$back){
            return false;
        }else{
            $this->id = $back;
            return $this;
        }
    }


    public function saveNullBonusAll($data){

        return $this->where(['id'=>$this->id])->save(['data'=>json_encode($data)]);

    }

}