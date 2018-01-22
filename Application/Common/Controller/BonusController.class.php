<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/21 0021
 * Time: 下午 5:20
 */

namespace Common\Controller;

use Common\Model\UsersModel;
use Home\Model\UserpropertyModel;
use Think\Controller;
use Think\Exception;

class BonusController extends Controller
{

    private $user= [];

    private $register = 0;

    private $cfg=[];

    private $money;

    private $errot;

    /**
     * @return mixed
     */
    public function getErrot()
    {
        return $this->errot;
    }

    /**
     * @param mixed $errot
     */
    public function setErrot($errot)
    {
        $this->errot = $errot;
    }

    /**
     * @return mixed
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * @param mixed $money
     */
    public function setMoney($money)
    {
        $this->money = $money;
    }

    /**
     * @return array
     */
    public function getCfg()
    {
        return $this->cfg;
    }

    /**
     * @param array $cfg
     */
    public function setCfg($cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * @return int
     */
    public function getRegister()
    {
        return $this->register;
    }

    /**
     * @param int $register
     */
    public function setRegister($register)
    {
        $this->register = $register;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param array $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }



    public function getParent() {

        $bonus = M("bonus_distribution");

        $list = $bonus -> find();

        $data = json_decode($list['data'],true);

        $this->setCfg($data);

        $count = count($data);

        $UsersModel = new UsersModel();

        $UsersModel->startTrans();
        try{
            for ($i = 0;$i < $count;$i++){

                $Parent = $UsersModel->getPid($this->user['id']);

                if(empty($Parent['id'])){
                    break;
                }

                $this->setUser($Parent);

                $this->setRegister($i);

                $back = $this->bonus();

                if (!$back){
                    throw new Exception($this->errot);
                }

            }

            $UsersModel->commit();
            return true;

        }catch (\Exception $e){
            $UsersModel->rollback();
            return false;
        }



    }

    /**
     * 红包发放
     */
    public function bonus(){

        $numpeople = $this->cfg[$this->register]['numpeople'];
        $percentage = $this->cfg[$this->register]['percentage'];

        $UsersModel = new UsersModel();

        $countChild = $UsersModel->countChild($this->user['id']);

        #如果满足条件
        if ($countChild>=$numpeople){
           $money = $this->money*$percentage/100;

           $UserpropertyModel = new UserpropertyModel();

           $back = $UserpropertyModel->setChangeMoney(1,$money,$this->user['id'],'红包分成',2);

           if (!$back){
               $this->errot = $UserpropertyModel->getError();
               return false;
           }
        }

        return true;

    }


}