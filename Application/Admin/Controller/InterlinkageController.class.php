<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;

class InterlinkageController extends AdminController {

   public function index(){
      $this->display();
   }
    public function curr_statistics(){
         //所有币种
         $xnb = M("xnb") -> field("id, name, brief") -> where("id <> 1") -> select();
         $xnbid = I("id") ? I("id") : $xnb[0]['id'];
          if (I("id")) {
              $xnbname = M("xnb") -> field("name, brief") -> where("id = ". $xnbid) -> find();
              $this -> assign("xnbname", $xnbname);
          }
         //文章总数
         $count_contents = M("text") -> where("xnbid = ". $xnbid) -> count();
         //累计交易额
         $count_record = M("transactionrecords") -> where("xnb = ". $xnbid) -> sum("allmoney");
         if ($count_record == '') {
            $count_record = 0;
         }
         //币种成交量
         $count_number = M("transactionrecords") -> where("xnb = ". $xnbid) -> sum("number");
         if ($count_number == "") {
            $count_number = 0;
         }
         //注册人数
         $count_users_usual = M("users") -> where("status = 1") -> count();
         $count_users_all = M("users") -> count();
         $count_users_freeze = $count_users_all - $count_users_usual;

         $this -> assign("xnbid", $xnbid);
         $this -> assign("users", $count_users_all);
         $this -> assign("users_usual", $count_users_usual);
         $this -> assign("users_freeze", $count_users_freeze);
         $this -> assign("number", $count_number);
         $this -> assign("allmoney", $count_record);
         $this -> assign("contents", $count_contents);
         $this -> assign("xnb", $xnb);
      $this->display();
   }
   //转出手续费部分未完成 连表查询
   public function market_statistics(){
         //所有市场
         $market = M("markethouse") -> field("id, name") -> select();
         $marketid = I('id') ? I('id') : $market[0]['id'];
         //累计成交次数
         $count = M("transactionrecords") -> where("buy is not null and market = ". $marketid) -> count();
         //累计成交量
         $number = M("transactionrecords") -> where("buy is not null and market = ". $marketid) -> sum("number");
         if ($number == "") {
            $number = 0;
         }
         //累计成交额
         $allmoney = M("transactionrecords") -> where("buy is not null and market = ". $marketid) -> sum("allmoney");
         if ($allmoney == "") {
            $allmoney = 0;
         }
         //累计手续费
         $poundage_buy = M("transactionrecords") -> where("buy is not null and market = ". $marketid) -> sum("buypoundage");
         if ($poundage_buy == "") {
            $poundage_buy = 0;
         }
         $poundage_sell = M("transactionrecords") -> where("buy is not null and market = ". $marketid) -> sum("sellpoundage");
         if ($poundage_sell == "") {
            $poundage_sell = 0;
         }
         $poundage_all = $poundage_sell + $poundage_buy;
         //转出手续费 查出这个市场的xnbid后根据xnbid查出转出手续费
         $xnbid = M("markethouse") -> where("id = ". $marketid) -> field("xnb") -> find();
         $xnbid = substr($xnbid['xnb'], 1, strlen($xnbid['xnb']));
         $xnbid = substr($xnbid, 0, strlen($xnbid) - 1);
         $poundage_rollout = M("xnbrolloutwater") -> where("xnb in (". $xnbid .")") -> sum("poundage");
         if ($poundage_rollout == "") {
            $poundage_rollout = 0;
         }
         $this -> assign("poundagerollout", $poundage_rollout);
         $this -> assign("marketid", $marketid);
         $this -> assign("poundageall", $poundage_all);
         $this -> assign( "poundagebuy",  $poundage_buy );
         $this -> assign( "poundagesell", $poundage_sell );
         $this -> assign( "allmoney",     $allmoney );
         $this -> assign( "number",       $number );
         $this -> assign( "count",        $count );
         $this -> assign( "market",       $market );
         $this -> display();
   }
   public function sys_scan(){
      //注册人数
      $count_users = M("users") -> count();
      //文章总数
      $count_contents = M("text") -> count();
      //人民币总计 用户资产总计加上冻结资产
      $count_cny_users = M("userproperty") -> sum("cny");
      $count_cny_freeze = M("carryapply") -> sum("money");
      $count_cny = $count_cny_users + $count_cny_freeze;
      //累计交易额
      $count_trade = M("transactionrecords") -> sum("allmoney");

      $this -> assign("trade", $count_trade);
      $this -> assign("cny", $count_cny);
      $this -> assign("contents", $count_contents);
      $this -> assign("users", $count_users);
      $this -> display();
   }
   public function echarts(){
      $Date_2 = date('Y-m-d H:i:s', strtotime('-1 month'));
      $day = strtotime($Date_2);
      $map['addtime'] = array('gt',$day);
      $rest=M('users')->field('addtime')->where($map)->select();
      $data=array();
      foreach($rest as $v){
         $data[]= date('Y-m-d ',$v['addtime']);
      }
      $pan= array_unique($data);
      $pan=array_values($pan);
      $sef=array();
      for($i=0;$i<count($data);$i++){
         for($j=0;$j<count($pan);$j++){
            if($data[$i]==$pan[$j]){
               $sef[$pan[$j]][]=count($i);
            }
         }
      }
      $this->ajaxReturn($sef);


   }
   public function echart(){
      $Date_2 = date('Y-m-d H:i:s', strtotime('-1 month'));
      $day = strtotime($Date_2);
      $map['endtime'] = array('gt',$day);
      $map['status'] = array('eq',1);
      $rest=M('rechargewater')->field('
        money,
        endtime
        ')->where($map)->select();
      $data=array();
      foreach($rest as $v){
         $data['day'][]= date('Y-m-d ',$v['endtime']);
         $data['chong'][]=$v['money'];
      }
      $pan= array_unique($data['day']);
      $pan=array_values($pan);
      $sef=array();
      $set=array();
      for($i=0;$i<count($data['day']);$i++){
         for($j=0;$j<count($pan);$j++){
            if($data['day'][$i]==$pan[$j]){
               $sef[$pan[$j]][]=$data['chong'][$i];
               for ($k=0;$k<count($sef[$pan[$j]]);$k++){
                  $set['chong'][$pan[$j]]=array_sum($sef[$pan[$j]]);
               }
            }
         }
      }
      //提现
      $where['endtime'] = array('gt',$day);
      $where['status'] = array('eq',3);
      $rest=M('carryapplywater')->field('
        money,
        endtime
        ')->where($where)->select();
      $tidata=array();
      foreach($rest as $n){
         $tidata['day'][]= date('Y-m-d ',$n['endtime']);
         $tidata['ti'][]=$n['money'];
      }
      $tipan= array_unique($tidata['day']);
      $tipan=array_values($tipan);
      for($i=0;$i<count($tidata['day']);$i++){
         for($j=0;$j<count($tipan);$j++){
            if($tidata['day'][$i]==$tipan[$j]){
               $sef[$tipan[$j]][]=$tidata['ti'][$i];
               for ($k=0;$k<count($sef[$tipan[$j]]);$k++){
                  $set['ti'][$tipan[$j]]=array_sum($sef[$tipan[$j]]);
               }
            }
         }
      }
      $this->ajaxReturn($set);
   }
    public function marketline(){
        $xnb_id=$this->strFilter(I('xnb'));
        $Date_2 = date('Y-m-d H:i:s', strtotime('-15 day'));
        $day = strtotime($Date_2);
        $entrust_m=M('transactionrecords');
        $tentrust=array();
        $data=array(); $grobe=array();
        //卖10，买10
        $tentrust=$entrust_m->where(array(
            'xnb'=>$xnb_id,
            'time'=>array('gt',$day)
        ))->field("sum(buypoundage) as buy,time,sum(sellpoundage) as sell")->group('time')->order('buypoundage desc')->select();
        for ($i=0;$i<count($tentrust);$i++){
            $tentrust[$i]['time']=date('Y-m-d', $tentrust[$i]['time']);
            $data[]= $tentrust[$i]['time'];
        }
        $pan=array_unique($data);
        $seft=array();
        for ($j=0;$j<count($tentrust);$j++){
            for ($k=0;$k<count($pan);$k++){
                if($tentrust[$j]['time']==$pan[$k]){
                    $grobe[$pan[$k]][]=$tentrust[$j]['buy']+$tentrust[$j]['sell'];
                }
                if(array_sum($grobe[$pan[$k]])==""){

                }else{
                    $seft[$pan[$k]]=array_sum($grobe[$pan[$k]]);
                }
            }
        }
        $save=array_merge($seft);
        $this->ajaxReturn($save);
    }
    public function markhouse(){
        $xnb_id=$this->strFilter(I('xnb'));
        $Date_2 = date('Y-m-d H:i:s', strtotime('-15 day'));
        $day = strtotime($Date_2);
        $entrust_m=M('transactionrecords');
        $tentrust=array();
        $grobe=array();
        //卖10，买10
        $tentrust['buy_data']=$entrust_m->where(array(
            'type'=>1,
            'market'=>$xnb_id,
            'time'=>array('gt',$day)
        ))->field("sum(buypoundage) as buy,time")->group('time')->order('buypoundage desc')->limit(50)->select();
        $tentrust['sell_data']=$entrust_m->where(array(
            'type'=>2,
            'market'=>$xnb_id,
            'time'=>array('gt',$day)
        ))->field("sum(sellpoundage) as sell,time")->group('time')->order('buypoundage desc')->limit(50)->select();
        $time=array();
        for($i=0;$i<count($tentrust['buy_data']);$i++){
            $tentrust['buy_data'][$i]['time']=date('Y-m-d', $tentrust['buy_data'][$i]['time']);
            $time['buy_time'][]=$tentrust['buy_data'][$i]['time'];
        }
        for($a=0;$a<count($tentrust['sell_data']);$a++){
            $tentrust['sell_data'][$a]['time']=date('Y-m-d', $tentrust['sell_data'][$a]['time']);
            $time['sell_time'][]=$tentrust['sell_data'][$a]['time'];
        }
        
        $data= array_merge(  $time['buy_time'],  $time['sell_time']);
        $pan=array_unique($data);
        $seft=array();
        for ($j=0;$j<count($tentrust['buy_data']);$j++){

            for ($k=0;$k<count($pan);$k++){
                if($tentrust['buy_data'][$j]['time']!=$pan[$k]){

                }else{
                    $grobe[$pan[$k]][]=$tentrust['buy_data'][$j]['buy'];
                }
                if(array_sum($grobe[$pan[$k]])==""){

                }else{
                    $seft[$pan[$k]]['buy']=array_sum($grobe[$pan[$k]]);
                }
            }
        }
        $grobes=array();
        for ($ja=0;$ja<count($tentrust['sell_data']);$ja++){
//            var_dump($tentrust['buy_data'][$ja]);
            for ($ka=0;$ka<count($pan);$ka++){
                if($tentrust['sell_data'][$ja]['time']!=$pan[$ka]){

                }else{
                    $grobes[$pan[$ka]][]=$tentrust['sell_data'][$ja]['sell'];
                }
                if(array_sum($grobes[$pan[$ka]])==""){

                }else{
                    $seft[$pan[$ka]]['sell']=array_sum($grobes[$pan[$ka]]);
                }
            }
        }
        $save=array_merge($seft);
        $this->ajaxReturn($save);
    }
}
