<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Admin\Model\ShopCfgModel;
use Think\Db;
use Think\Exception;
use User\Api\UserApi;
// use Think\Page;
class ShopCfgController extends AdminController {
    /**
     * 运营配置
     */
    public function index(){

        $repeat_cfg_m =  new ShopCfgModel();

        if (IS_POST){

            $data = [
                ['key'=>'cny','data'=>I('cny')],
                ['key'=>'repeat','data'=>I('repeat')],
            ];

            $repeat_cfg_m->startTrans();

            try{

                $back = $repeat_cfg_m->where('1')->delete();

                if (!$back){

                    throw new Exception('保存失败！1');
                }
                $back = $repeat_cfg_m->addAll($data);
                if (!$back){

                    throw new Exception('保存失败！2');
                }

                $repeat_cfg_m->commit();

                $this->success('保存成功！');



            }catch (\Exception $e){

                $repeat_cfg_m->callback();

                $this->error($e->getMessage());

            }
            exit();
        }
        

        $data= $repeat_cfg_m->select();

        $back_data = [];

        foreach ($data as $k=>$v){

            $back_data[$v['key']] = $v['data'];

        }

        $this->assign('data',$back_data);

        $this->display();
    }

}
