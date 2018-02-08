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
use Admin\Model\BonusAllModel;
use Admin\Model\BonusModel;
use Admin\Model\IntegralAllModel;

use Admin\Model\IntegralListModel;
use Admin\Model\IntegralModel;
use Admin\Model\IntegralReleaseAllModel;
use Admin\Model\IntegralReleaseListModel;
use Admin\Model\MemoryAllModel;
use Admin\Model\MemoryListModel;
use Admin\Model\MemoryModel;
use Admin\Model\RepeatCfgModel;
use Admin\Model\XnbModel;
use Common\Model\UsersModel;
use Home\Model\UserpropertyModel;
use function MongoDB\BSON\toJSON;
use Think\Controller;
use Think\Exception;

const TOKEN = 'sjkfhsdjkhfu';

const COUNT = 1000;

class ProvideApiController  extends Controller{



    public function __construct()
    {
        parent::__construct();
        if (empty(I('token')) || I('token')!=TOKEN){
            exit(TOKEN);
        }
    }


    public function start(){
        $this->bonus();
        $this->integral();
        $this->ReleaseXnb();
        $this->integral_release();
    }


    #红包的发放
    public function  bonus(){

        $bonus_m  =  new BonusModel();

        $repeatCfgModel = new RepeatCfgModel();

        $usersModel = new UsersModel();

        #日返率
        $date_back = array_sort($repeatCfgModel->getCfg('date_back'));

        #红包的重消配置
        $repeat_paper = $repeatCfgModel->getCfg('repeat_paper');

        #红包扣税率
        $revenue = $repeatCfgModel->getCfg('revenue');

        #本次cny放数总发
        $all_money = 0;

        #本次重消放数总发
        $all_repeat = 0;

        $where = 'provide < outs';

        $count = $bonus_m->getCount($where);

        #没有数据的情况
        if (!$count){
            return false;
        }

        $bonus_m->startTrans();
        try{

            $bonusAllModel =  new BonusAllModel();
            $nullBonusAll = $bonusAllModel->addNullBonusAll();
            if (!$nullBonusAll){
                throw new Exception('发放失败！');
            }

            for ($i = 0; $i<$count/C('Count');$i++){

                $data = $bonus_m->getDataPage($where,$i);

                foreach ($data as $k=>$v){

                    $child_number = 0;

                    $usersModel->countChild_all($v['user_id'],$child_number);

                    #应返金额(购买数量*日返金额)
                    $money = $v['number']*$this->getBonusCfg($date_back,$data);

                    #判断出局金额与应返金额的关系

                    $money = $money+$v['provide'] <= $v['outs'] ? $money : $v['outs']-$v['provide'];

                    #扣除税收
                    $that_revenue = ($money*$revenue);

                    $money = $money - $that_revenue;

                    #红包的重消金额
                    $all_repeat +=$repeat_money = $money*$repeat_paper;

                    #发放的cny
                    $money = $money - $repeat_money;

                    $all_money += $money;

                    #返回用户金额
                    $userproperty = new UserpropertyModel();  //home 模块的 Userproperty

                    #发放用户cny
                    $back = $userproperty->setChangeMoney(1,$money,$v['user_id'],'红包分红',2);

                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }


                    #发放用户重消
                    $back = $userproperty->setChangeMoney(3,$repeat_money,$v['user_id'],'红包分红',2);
                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }

                    #生成发放流水，并且修改本次已发放金额
                    $back = $bonus_m->saveData($v['id'],$money,$that_revenue,$repeat_money,$nullBonusAll->getId());
                    if (!$back){
                        throw new Exception($userproperty->getError());
                    }

                }


            }

            #成功后修改本次发放总数
            $back = $nullBonusAll->saveNullBonusAll($all_money,$all_repeat);

            if (!$back){

                throw new Exception('发放失败！');

            }

            $bonus_m->commit();

        }catch (\Exception $e){

            $this->error($e->getMessage());

            $bonus_m->rollback();
        }

    }

    #锁定资产的发放
    public function  ReleaseXnb(){

        $memoryModel =   new \Admin\Model\MemoryModel();

        $where = ['time_end'=>['EGT',time()],'balance'=>['GT',0]];


        $xnbModel = new XnbModel();

        $userpropertyModel = new UserpropertyModel();

        $memoryListModel = new MemoryListModel();

        $memoryAllModel = new MemoryAllModel();

        $NullBonusAll = $memoryAllModel->addNullBonusAll();

        $count = $memoryModel->getCount($where);

        if ($count<=0){
            return false;
        }

        #虚拟币返回配置
        $back_cfg=[];

        #xnb总返金额
        $back_all= [];

        for ($i = 0; $i<$count/C('Count');$i++){

            $data = $memoryModel->getDataPage($where,$i);

            foreach ($data as $k=>$v){

                #应返金额
                $money = $memoryModel->Release($v,$back_cfg,$xnbModel,$userpropertyModel,$memoryListModel,$NullBonusAll->getId());
                #改虚拟币的应返金额
                $back_all[$v['xnb_id']] += $money;

            }

        }
        #修改发放总数
        $NullBonusAll->saveNullBonusAll($back_all);

    }

    #积分的复利
    public function  integral(){

        $integralModel = new IntegralModel();

        $repeatCfgModel = new RepeatCfgModel();

        $water = $repeatCfgModel->getCfg('water');


        $where = ['number'=>['gt',0],'water'=>['lt',$water]];


        $number_all = 0;

        $repeats_all = 0;


        $integralListModel = new IntegralListModel();

        $integralAllModel = new IntegralAllModel();

        $NullIntegralAll= $integralAllModel->addNullIntegralAll();

        $count = $integralModel->getCount($where);

        if ($count<=0){
            return $this->success('本期发放数量为0');
        }

        $integral_cfg = $repeatCfgModel->getCfg('integral');

        $integralModel->startTrans();

        try{
            for ($i = 0; $i<$count/C('Count');$i++){

               $data = $integralModel->getDataPage($where,$i);


               foreach ($data as $k=>$v){

//                #应返的金额
//                $number_all += $number = $v['number'] *(1+$integral_cfg);
//                #应返的重消
//                $repeats_all+= $repeats = $v['repeats'] *(1+$integral_cfg);

                   #应返的金额
                   $number_all += $number = $v['number'] *$integral_cfg;

                   $back =$integralModel->Release_interest($v['id'],$number,$NullIntegralAll->getId(),$integralListModel);

                   if (!$back){
                        throw  new Exception($integralModel->getError());
                   }
               }

            }

            $NullIntegralAll->saveNullIntegralAll($number_all,$repeats_all);
            $integralModel->commit();
            $this->success('发放成功！');
       }catch (\Exception $e){
            $integralModel->rollback();
            $this->error($e->getMessage());
       }

    }

    #积分的释放
    public function integral_release(){

        #在1号才执行
        if ( date('d',time()) !='01' ){
            return $this->success('不在发放日期');
        }

        #判断本月是否已经发放
        $data = M('integral_release_all')->where( ['time'=> ['egt',strtotime(date('Y-m-d',time()))] ] )->find();

        if (!empty($data['id'])){

          return  $this->success('本月已释放');

        }

        $integralModel = new IntegralModel();

        $repeatCfgModel = new RepeatCfgModel();

        $where = ['number'=>['gt',0],'time_end'=>['elt',time()]];



        $number_all = 0;

        $integralReleaseListModel = new IntegralReleaseListModel();

        $integralReleaseAll = new IntegralReleaseAllModel();

        $NullIntegralReleaseAll = $integralReleaseAll->addNullIntegralReleaseAll();

        $count = $integralModel->getCount($where);

        if ($count<=0){
           return $this->success('本期发放数量为0');
        }
        $release_cfg = $repeatCfgModel->getCfg('water_release');

        $integralModel->startTrans();



        try{
            for ($i = 0; $i<$count/C('Count');$i++){

                $data = $integralModel->getDataPage($where,$i);

                foreach ($data as $k=>$v){

                    #应返的金额
                     $number = $v['number'] *$release_cfg;

                     $number_all += $number = $number > $v['number'] ? $v['number'] : $number;


                     $back =$integralModel->Release_info($v['id'],$v['user_id'],$NullIntegralReleaseAll->getId(),$number,$integralReleaseListModel);

                     if (!$back){
                         throw  new Exception($integralModel->getError());
                     }

                }

            }

            $back = $NullIntegralReleaseAll->saveNullIntegralReleaseAll($number_all);
            if (!$back){
                throw new Exception('期数保存失败！');
            }
            $integralModel->commit();
            $this->success('发放成功！');
        }catch (\Exception $e){
            $integralModel->rollback();
            $this->error($e->getMessage());
        }

    }



    private function getBonusCfg(array $cfg,$count){

        foreach($cfg as $k=>$v){

            #判断是否满足直推人数
            if ($count >= $v['numpeople']){

                    #返回日返率
                    return $v['percentage']/100;

            }

        }

        return false;
    }


}