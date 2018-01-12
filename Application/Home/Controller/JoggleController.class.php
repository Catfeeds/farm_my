<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

//提供数据的控制器
class JoggleController extends HomeController{

	public function getTrdeWater(){
			$data = $this->getentrust_s(13);
			$data['TrdeWater'] = $this->getTransaction();
			$this->ajaxReturn($data);

	}

	//数据请求 卖13，买13数据请求
	public function getentrust_s($limit){
		$xnb_id=$this->strFilter(I('id'));
		$market=$this->strFilter(I('market'));
		$entrust_m=M('entrust');
		$tentrust=array();
		//卖10，买10
		$tentrust['buy_data']=$entrust_m->where(array(
			'type'=>1,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->cache('buy_data',3)->field('price,sum(number) as num')->group('price')->order('price desc')->limit($limit)->select();

		$tentrust['sell_data']=$entrust_m->where(array(
			'type'=>2,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->cache('sell_data',3)->field('price,sum(number) as num')->limit($limit)->group('price')->order('price desc,addtime asc')->select();
		return $tentrust;
	}

	//数据获取方法，前25条交易记录
	public function getTransaction($int){
		$transactionrecords_m=M('transactionrecords');
		$xnb_id=$this->strFilter(I('id'));
		$market=$this->strFilter(I('market'));
		$transaction_data=$transactionrecords_m->where(array(
			'xnb'=>$xnb_id,
			'market'=>$market
		))->cache('transaction_data',3)->field('time,type,price,number')->limit(25)->order('time desc')->select();
		return $transaction_data;
	}


}
