<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 上午 11:47
 */

namespace Admin\Model;


use Home\Model\UserpropertyModel;
use Think\Model;

class MemoryModel extends Model
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
     * 释放功能
     * @param $list 存储信息
     *         ----- balance 余额
     *        ------ number_all储存总数据
     *        ------ xnb_id 虚拟币id
     */

    public function Release (array $list,array &$back_cfg,XnbModel $xnbModel,UserpropertyModel $userpropertyModel,MemoryListModel $memoryListModel,$all_id){

        #获取该虚拟币日返率
        $back_cfg[$list['brief']] = $back_cfg[$list['brief']] ? $back_cfg[$list['brief']] :$xnbModel->getXnb_info($list['xnb_id'],'memory_back');

        $money = $back_cfg[$list['brief']] * $list['number_all'];

        #用户应返金额
        $money = $money<= $list['balance'] ? $money : $list['balance'];

        #扣除用户金额
        $back = $userpropertyModel->setChangeMoney($list['xnb_id'],$money,$list['user_id'],'资产释放',2);

        if (!$back){
            $this->error = $userpropertyModel->getError();
            return false;
        }

        #扣除本期余额

        $back =  $this->where(['id'=>$list['id']])->setDec(['balance',$money]);

        if (!$back) {
            $this->error = '扣除余额失败！';
            return false;
        }

        #生成发放记录
        $back = $memoryListModel->addData($all_id,$money,$list['id']);
        if (!$back) {
            $this->error = '扣除余额失败！';
            return false;
        }

        #成功！发放用户发放金额
        return  $money;

    }


}