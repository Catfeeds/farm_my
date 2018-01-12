<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Wap\Controller;

use Think\Controller;
use Wap\Model\MarkethouseModel;
use Wap\Model\XnbModel;

class WxreturnController extends WapController{
    //首页
    public function index() {
        //虚拟币列表
        $code=I('code');
        $open=$this->weixintwo($code);
        if($open){
            $where['wxuser']=$open['openid'];
            $bort=M('users')->where($where)->select();
            $addr=session("addr");
            if($bort){
                session('user_wap', array('user_name' => $bort[0]['users'], 'password' => $bort[0]['password'], 'dealpwd' => $bort[0]['dealpassword'], 'id' => $bort[0]['id'], 'expire' => time() + 3600));
                session("openid",$open['openid']);
                $this -> redirect($addr);
            }else{
                session("openid",$open['openid']);
                $this -> redirect($addr);
            }
        }
        
    }
}
