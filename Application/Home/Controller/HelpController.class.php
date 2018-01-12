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
class HelpController extends HomeController {
    public function help(){
        $side_list = M("text") -> where( 'type = 6 and status = 1') -> field("id, title") -> select();

        $current_id = I('id') ? I('id') : 42;
        $help = M("text") -> where('id = '. $current_id) -> find();

        $this -> assign("help", $help);
        $this -> assign("current_id", $current_id);
        $this -> assign("side_list", $side_list);
        $this->display();
    }
}