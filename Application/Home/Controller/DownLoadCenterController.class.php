<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class DownLoadCenterController extends HomeController {
    public function llqDown(){
        $this->display();
    }
    public function newDown(){
        $mail = M("config") -> where("id = 42") -> field("value") -> find();

        $this -> assign("mail", $mail['value']);
        $this->display();
    }
    public function walletDown(){
        $wallet = M()
            -> table("currency_xnbaddress as xa")
            -> join("left join currency_xnb as x on xa.xnbid = x.id")
            -> where("xa.status = 1")
            -> field("xa.*, x.name, x.imgurl, x.brief")
            -> select();
        $this -> assign("wallet", $wallet);
        $this -> display();
    }
    public function APPDownload(){
        //APP预览图片
        $appphoto = M("appphoto") -> where("status = 1") -> order("sort desc") -> field("imgurl") -> select();
        $this -> assign("appphoto", $appphoto);

        $app_img = M('config')->where([
            'name'=>['in',['APP_ANDROID_CODE','APP_IOS_CODE']]
        ])->field('name,value')->select();
        $app_url = [];
        foreach ($app_img as $v){
            $app_url[$v['name']]=$v['value'];
        }
        $this->assign('app_url',$app_url);
        $this->display();
    }
}