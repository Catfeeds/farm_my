<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/21 0021
 * Time: 上午 10:38
 */

namespace Common\Controller;

use Admin\Model\RepeatCfgModel;
use Home\Model\UserpropertyModel;

class IntegraldepositController
{


    /**
     * 提现金额
     * @var int
     */
    protected $number;

    protected $error;

    private $integral_m;

    /**
     * 总价值 （数量 * 单价）+=（数量 * 单价）
     * @var int
     */
    private $list = 0;


    function __construct()
    {
        $this->integral_m  =  M('integral');
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        #判断是否合法

        $releases = $this->integral_m->where(['user_id'=>session('user.id')])->field('sum(releases) as releases')->find();

        if ($number>$releases['releases']){
            return false;
        }

        $this->number = $number;

        return true;
    }


    /**
     * 获取提取提取的单价和数量
     */
    public function getList(){

        $number =  $this->number;

        for ($i=0;$i < $number;){

            $data = $this->integral_m->lock(true)->where(['user_id'=>session('user.id'),'releases'=>['gt',0]])->order('time')->find();

            $money = $number > $data['releases'] ? $data['releases'] : $number;

            $number -= $money;

            #扣除本次的提出的数量
            $back = $this->integral_m->where(['id'=>$data['id']])->setDec('releases',$money);

            if (!$back){
                $this->error = '扣除释放金额失败！'.$data['id'];
                return false;
            }

            $this->list += $money*$data['price'];

        }

        return $this;

    }


    /**
     * 获取手续费金额
     */
    public function getPoundage(){

        $price_old = $this->list/$this->number;

        $price_new = CmcpriceController::getPrice();

        $price = $price_new-$price_old;

        #大于0收取手续费
        if ( $price > 0 ){

            $repeatCfgModel = new RepeatCfgModel();

            $poundage_cfg = $repeatCfgModel->getCfg('poundage');

            return $poundage = $price * $poundage_cfg * $this->number;

        }
        return 0;

    }


    /**
     * 用户提取cmc
     */
    public function release_cmc($type){


        $userpropertyModel = new UserpropertyModel();

        $poundage = $this->getPoundage();

        #提取的cmc = 数量 -(手续费/当前价格)
        $money = $this->number - ($poundage/CmcpriceController::getPrice()) ;


        $back = $userpropertyModel->setChangeMoney(62,$money,session('user.id'),'提取积分',2);

        if (!$back){

            $this->error = $userpropertyModel->getError();

            return false;
        }

        $back = $this->water($money,$poundage,$type);

        if (!$back){

            return false;
        }


        return true;


    }


    /**
     * 用户提取cny
     */

    public function release_cny($type){

        $userpropertyModel = new UserpropertyModel();

        $poundage = $this->getPoundage();

        #提取的金额 = （数量 * 当前价格）- 手续费
        $money = ($this->number*CmcpriceController::getPrice()) - $poundage;

        $back = $userpropertyModel->setChangeMoney(1,$money,session('user.id'),'提取积分',2);

        if (!$back){

            $this->error = $userpropertyModel->getError();

            return false;
        }

        $back = $this->water($money,$poundage,$type);

        if (!$back){

            return false;
        }

        return true;

    }


    /**
     * 提现记录
     */

    public function water($number,$poundage,$type){
        $back =  M('integral_deposit')->add([
            'user_id'=>session('user.id'),
            'number_all'=>$this->number,
            'number'=>$number,
            'poundage'=>$poundage,
            'type'=>$type,
            'time'=>time()
      ]);

      if (!$back){
          $this->error='记录生成失败！';
          return false;
      }

      return $back;

   }







}