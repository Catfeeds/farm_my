<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 10:46
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;
class BonusAllModel extends Model
{
    private $id;


    public $data;

    public $show;

    public $page_where;


    public function getId(){
        return $this->id;
    }

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


    public function saveNullBonusAll($number,$all_repeat){

        return $this->where(['id'=>$this->id])->save(['number'=>$number,'repeats'=>$all_repeat]);

    }



    public function lists($where){

        $count =  $this->where($where)->count();

        $Page = new Page($count,15,$this->page_where);

        $this->show = $Page->show();

        $this->data = $this->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)->select();

        return $this;
    }



}