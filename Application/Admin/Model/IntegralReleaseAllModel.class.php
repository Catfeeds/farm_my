<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20 0020
 * Time: 下午 4:13
 */

namespace Admin\Model;


use Think\Model;

class IntegralReleaseAllModel extends Model
{
    protected $id;

    public function getId(){
        return $this->id;
    }

    public function addNullIntegralReleaseAll(){
        $back = $this->add([
            'time'=>time()
        ]);

        if (!$back){
            return false;
        }else{
            $this->id = $back;
            return $this;
        }
    }


    public function saveNullIntegralReleaseAll($number){

        return $this->where(['id'=>$this->id])->save(['number'=>$number]);

    }
}