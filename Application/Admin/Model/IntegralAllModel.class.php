<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17 0017
 * Time: 下午 3:53
 */

namespace Admin\Model;


use Think\Model;

class IntegralAllModel extends Model
{

    protected $id;

    public function getId(){
        return $this->id;
    }

    public function addNullIntegralAll(){
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


    public function saveNullIntegralAll($number,$repeats){

        return $this->where(['id'=>$this->id])->save(['number'=>$number,'repeats'=>$repeats]);

    }

}