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
use Admin\Model\BonusAllModel;
use Admin\Model\BonusModel;
use Admin\Model\RepeatCfgModel;
use Home\Model\UserpropertyModel;
use function Sodium\add;
use Think\Exception;

const TOKEN = 'nsjkfhsdjkhfu';

const COUNT = 1000;

class ProvideApi{

    #红包的发放
    public function bonus(){

        $bonus_m  =  new BonusModel();

        $repeatCfgModel = new RepeatCfgModel();

        #日返率
        $date_back = $repeatCfgModel->getCfg('date_back');

        #本次总发放数
        $all_money = 0;

        $where = [''=>''];

        $count = $bonus_m->getCount($where);

        $bonus_m->startTrans();
        try{

            $bonusAllModel =  new BonusAllModel();
            $nullBonusAll = $bonusAllModel->addNullBonusAll();
            if (!$nullBonusAll){
                throw new Exception('发放失败！');
            }

            for ($i = 0; $i<$count/C('Count');$i++){

                $data = $bonus_m->getDataPage($where);

                foreach ($data as $k=>$v){

                    #应返金额(购买数量*日返金额)
                    $money = $v['number']*(int)$date_back;

                    #判断出局金额与应返金额的关系

                    $money = $money >= $v['out'] ? $money : $v['out'];

                    $all_money += $money;

                    #返回用户金额
                    $userproperty = new UserpropertyModel();  //home 模块的 Userproperty

                    #发放用户金额
                    $back = $userproperty->setChangeMoney(1,$money,$v['user_id'],'红包分红',2);

                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }

                    #生成发放流水
                    $back = $bonus_m->saveData($v['id'],$money);
                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }


                }


            }

            #成功后修改本次发放总数
            $back = $nullBonusAll->saveNullBonusAll($all_money);

            if (!$back){

                throw new Exception('发放失败！');

            }

            $bonus_m->commit();

        }catch (\Exception $e){

            $this->error($e->getMessage());

            $bonus_m->rollback();
        }
        
    }

}