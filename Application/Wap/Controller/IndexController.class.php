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

class IndexController extends WapController{
    //首页
    public function index() {
        //虚拟币列表

        $market_m=M('markethouse');
//        $market_data=$market_m->select();
//        $market=I('Market');

//        $market=  $market=="" ? $market_data[0]['id']:$market;  //如果为空就返回第一个交易市场

//        $xnb_data=$this->getXnbTede($market); //获取虚拟币交易信息

        //公告
        $notice = M("text")
                    -> where("type = 2 and status = 1 and header = 1")
                    -> field("id, title, type")
                    -> order("addtime desc")
                    -> limit(1)
                    -> find();

        //广告
        $ad = M("advertisement") -> where("status = 1") -> field("id, name, url, img") -> select();

        //行内资讯
        $news = M("text")
            -> where("(type = 3 or type = 4) and status = 1 and header = 1")
            -> field("id, title, type, addtime")
            -> order("addtime desc")
            -> limit(5)
            -> select();

        //客服、邮箱
        $number = M("config") -> where("id = 40 or id = 42") -> field("value") -> select();
        $data['tel'] = $number[0]['value'];
        $data['mail'] = $number[1]['value'];

        $this -> assign("ad", $ad);
//        $this -> assign('market_data',$market_data);
//        $this -> assign('xnb_data',$xnb_data);
        $this -> assign("new_notice", $notice);
        $this -> assign("arlist", $news);
        $this -> assign("number", $data);
//        $this -> assign("Market", $market);
        $this -> display();
    }

    //获取虚拟币交易信息
    public function getXnbTede($market){
        if (positive($market)!=1){    //正在判断市场是否合法
            $this->error('非法字符');
        }

        $nxb_d= D('xnb');
        $market_d= D('markethouse');
        $transactionrecords_m=M('transactionrecords');

        $market_data=$market_d->getMarkethouse($market);
        $market_data['xnb']=json_decode($market_data['xnb']);

        $xnb_data=$nxb_d
                    ->where(['currency_xnb.id'=>['in',$market_data['xnb']],'currency_xnb.status'=>['eq',1]])
                    ->field(' currency_xnb.id,
                           currency_xnb.name,
                           currency_xnb.brief,
                           currency_xnb.imgurl,
                           sum(currency_transactionrecords.number) as smum_number,
                           avg(currency_transactionrecords.price) as avg_price
                          ')
                    ->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb')
//                    ->order('currency_transactionrecords.time desc')
                    ->group('currency_xnb.id')
                    ->select();

        $time_1= strtotime(date('Y-m-d',time()));   //今天0点的时间


        foreach ($xnb_data as $n=>&$i){
            //最高最低价
            $transac_data=$transactionrecords_m
                ->where(array(
                    'xnb'=>$i['id'],
                    'time'=>['egt',$time_1],
                    'market'=>$market
                ))
                ->field('max(price),min(price),xnb')
                ->find();
            $i['max(price)']=$transac_data['max(price)'];
            $i['min(price)']=$transac_data['min(price)'];

            //昨天收盘价
            $oldprice=$transactionrecords_m
                ->where(array(
                    'xnb'=>$i['id'],
                    'time'=>[['egt',$time_1-86400],['lt',$time_1]],
                    'market'=>$market
                ))
                ->order('time desc')
                ->field('price')
                ->find();
            $i['oldprice']=$oldprice['price'];

            //最新价
            $new_price=$transactionrecords_m->field('price,id,time')->where(['xnb'=>$i['id'],'market'=>$market])->order('time desc,id desc')->find();
          
            $i['new_price']=$new_price['price'];

        }

        return $xnb_data;

        //日涨跌=（最新价-昨日收盘价格）/昨日收盘价格
        exit();
    }

  
}
