<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/21 0021
 * Time: 上午 11:48
 */

namespace Common\Controller;


use Admin\Model\RepeatCfgModel;

class CmcpriceController
{

    /**
     * 返回cmc的单价
     */
   static function getPrice(){

        $repeatCfgModel = new RepeatCfgModel();

        return $repeatCfgModel->getCfg('cmc');

    }


}