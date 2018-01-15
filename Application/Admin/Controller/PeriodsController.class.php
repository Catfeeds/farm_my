<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 下午 2:11
 */

namespace Admin\Controller;

/**
 * 期数接口
 * Interface PeriodsController
 * @package Admin\Controller
 */
interface PeriodsController
{
    /**
     * 发放金额的计算
     * @return mixed
     */
    function getProvideNbr();

    /**
     * 修改发放次数
     * @return mixed
     */
    function saveNumbe();


}