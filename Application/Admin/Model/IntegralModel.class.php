<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17 0017
 * Time: 下午 3:53
 */

namespace Admin\Model;


use Think\Model;

class IntegralModel extends Model
{
    /**
     * 获取总条数
     * @param $where
     * @return mixed
     */
    public function getCount($where){

        return $this->where($where)->count();

    }

    /**
     * 分页获取数据
     * @param $where
     * @param $page
     * @param int $number_this
     * @return mixed
     */

    public function getDataPage($where,$page,$number_this=0){

        $number_this = $number_this == 0 ? C('COUNT') : $number_this;

        $start = $page*C('COUNT');

        $end   = $page*C('COUNT')+$number_this;

        return $this->where($where)->limit($start,$end)->select();

    }


    public function Release($id,$number,$repeats,$all_id,IntegralListMode $ntegralListMode){

        #发放金额
        $back = $this->where(['id'=>$id])->save(['number'=>$number,'repeats'=>$repeats]);

        if (!$back){
            $this->error='发放失败';
            return false;
        }

        #发放记录
        return $ntegralListMode->addData($id,$all_id,$number,$repeats);

    }


}