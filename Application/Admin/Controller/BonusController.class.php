<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 下午 3:47
 */

namespace Admin\Controller;
use Admin\Model\RepeatCfgModel;
use Think\Controller;
use Home\Model\UserpropertyModel;

/**
 * 红包发放类
 * Class BonusController
 * @package Admin\Controller
 */
class BonusController  extends Controller
{

    private $list; //本条数据
                 /**
                ----user_id  用户id
                ----number   购买数量
                ----provide  已分红
                ----out      出局金额
                ----time     购买时间
                 */

    private $data_back_cfg;

    private $data_back;//返回给用户的金额

    public function setList($list){
        $this->list = $list;
        return $this;
    }


    public function _initialize(){
        $repeatCfg =  new RepeatCfgModel();
        $this->data_back_cfg = $repeatCfg->getCfg('date_back');
    }

    /**
     * 应返金额
     *
     * @return mixed
     */
    public function getBackMoney(){

        $back_money = $this->list['number'] * $this->data_back_cfg;

        return  $this->data_back = $back_money + $this->list['provide'] <= $this->list['out'] ? $this->list['out'] : $this->list['provide'];

    }


    /**
     * 返回用户金额
     * @return bool
     */
    function sendUserMoney(){
        #返回用户金额
        $userproperty = new UserpropertyModel();  //home 模块的 Userproperty

        #发放用户金额
       return  $back = $userproperty->setChangeMoney(1,$this->data_back,$this->list['user_id'],'红包分红',2);
    }


}