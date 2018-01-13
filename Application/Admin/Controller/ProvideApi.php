<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 2018/1/6
 * Time: 16:45
 */

namespace Admin\Controller;

/**
 * 发放api
 * Class ProvideApi
 * @package Admin\Controller
 */
use Admin\Model\Bonus;
use Home\Model\UserpropertyModel;
use Think\Exception;

const TOKEN = 'nsjkfhsdjkhfu';

const COUNT = 1000;

class ProvideApi{

    #红包的发放
    public function bonus(){

        $bonus_m  =  new Bonus();

        $where = [];

        $count = $bonus_m->getCount($where);

        $bonus_m->startTrans();
        try{

            for ($i = 0; $i<$count/C('Count');$i++){

                $data = $bonus_m->getDataPage($where);

                foreach ($data as $k=>$v){

                    #返回金额

                    #判断是否出局

                    #返回用户金额
                    $userproperty = new UserpropertyModel();  //home 模块的 Userproperty

                    $back = $userproperty->setChangeMoney(1,);

                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }

                }


            }

            $bonus_m->commit();

        }catch (\Exception $e){

            $this->error($e->getMessage());

            $bonus_m->rollback();
        }



    }

}