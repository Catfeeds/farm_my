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

    private $error;

    private $child_id;

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
        $this->child_id= $user['id'];
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

                $Parent = $UsersModel->getPid($this->user['pid']);

                if(empty($Parent['id'])){
                    break;
                }

                $this->setUser($Parent);

                $this->setRegister($i);

                $back = $this->bonus($UsersModel,new UserpropertyModel());

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
    public function bonus(UsersModel $usersModel,UserpropertyModel $userpropertyModel){

        $numpeople = $this->cfg[$this->register]['numpeople'];

        $percentage = $this->cfg[$this->register]['percentage'];

        $countChild = $usersModel->countChild($this->user['id']);

        #如果满足条件
        if ($countChild>=$numpeople){

           $money = $this->money*$percentage/100;

           $back = $userpropertyModel->setChangeMoney(1,$money,$this->user['id'],'红包分成',2);

           if (!$back){
               $this->errot = $userpropertyModel->getError();
               return false;
           }

           #红包分层记录
            $back = M('bonus_deduct')->add([
                'user_id'=>$this->user['id'],
                'child_id'=>$this->child_id,
                'number'=>$money,
                'time'=>time()
            ]);

           if (!$back){

               $this->error='提成记录生成失败！';

               return false;
           }

        }

        return true;

    }


}