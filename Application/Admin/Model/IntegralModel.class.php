<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17 0017
 * Time: 下午 3:53
 */

namespace Admin\Model;


use Home\Model\UserpropertyModel;
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

    /**
     * 发放复利
     * @param $id
     * @param $number
     * @param $all_id
     * @param IntegralListModel $integralListModel
     * @return bool
     */
    public function Release_interest($id,$number,$all_id,IntegralListModel $integralListModel){

        #发放金额
        $back = $this->where(['id'=>$id])->setInc('number',$number);

        if (!$back){
            $this->error='发放失败';
            return false;
        }

        #已复利金额
        $back = $this->where(['id'=>$id])->setInc('interest',$number);

        if (!$back){
            $this->error='发放失败';
            return false;
        }



        #修改发放次数
        $back = $this->where(['id'=>$id])->setInc('water',1);

        if (!$back){
            $this->error='发放失败';
            return false;
        }

        #发放记录
        $back =  $integralListModel->addData($id,$all_id,$number);

        if (!$back){
            $this->error = '发放记录生成失败！';
            return false;
        }



        return true;
    }

    /**
     * 积分释放
     */
    public function Release_info($id,$user_id,$all_id,$number,IntegralReleaseListModel $integralReleaseListModel){
        #扣除本余额
        $back =$this->where(['id'=>$id])->setDec('number',$number);

        if (!$back){
            $this->error = '积分扣除失败.'.$id;
            return false;
        }

        #修改释放字段
        $back =$this->where(['id'=>$id])->setInc('releases',$number);
        if (!$back){
            $this->error = '释放失败.'.$id;
            return false;
        }



//        #添加用户释放积分账户
//        $userpropertyModel = new UserpropertyModel();
//        $back = $userpropertyModel->setChangeMoney(4,$number,$user_id,'积分释放',2);
//
//        if (!$back){
//            $this->error = '积分账户变动失败.'.$id;
//            return false;
//        }

        #添加释放记录
        $back =$integralReleaseListModel->add([
            'integral_id'=>$id,
            'all_id'=>$all_id,
            'number'=>$number,
            'time'=>time()
        ]);

        if (!$back){
            $this->error = '积分发放记录.'.$id;
            return false;
        }

        return true;
    }


}