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
//交易模块 购买
class TradeDataController extends HomeController{

	//ajax请求数据，虚拟币的成交价，最高最低，24小时成交量
	public function getBuyData(){
		$xnb_m=M('xnb');
		$markethouse_m=M('markethouse');
		$transactionrecords=M('transactionrecords');

		$marke_data=$markethouse_m->field('name,id')->order('id')->select();   //市场的展示
		$marke_id=preg_match("/^[1-9][0-9]*$/",I('markethouse'))>0 ? I('markethouse') : $marke_data[0]['id'];

		$xnb_all=$markethouse_m->where(['id'=>$marke_id])->field('xnb')->find();
		$xnb_all['xnb']=json_decode($xnb_all['xnb']);
		$map['currency_xnb.id']=['in',$xnb_all['xnb']];


		//得到虚拟币列表，最新价
		$table=$xnb_m
				->field('
				currency_xnb.name as currency_xnb_name,
				currency_xnb.id as currency_xnb_id,
				currency_xnb.brief,
				currency_xnb.imgurl,
				currency_transactionrecords.id as currency_transactionrecords_id,
				currency_transactionrecords.price as currency_transactionrecords_price
				')
				->where($map)
				->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb')
				->order('currency_transactionrecords.time desc,currency_transactionrecords.id desc')
				->select(false);  //虚拟币信息的展示

		$xnb_data=$xnb_m
			->cache('xnb_li',5)
			->table($table.'a')
			->where(array(
				'currency_xnb_id'=>array('neq',1),
			))
			->group('currency_xnb_id')
			->select();  //虚拟币信息的展示


		$date=time()-86400;//24以前的时间
		$xnb_where['time']=array('egt',$date);

		$t_date = strtotime(date('Y-m-d',time()));    //今日0点时间

		foreach ($xnb_data as $k=>$v){
			//24的成交量和成交额
			$xnb_where['xnb']=$v['currency_xnb_id'];
			$all_data=$transactionrecords
				->where($xnb_where)
				->cache($v['currency_xnb_id'].'xnb_data_number',5)
				->field('sum(number) as numbers,avg(price) as avg_price')
				->find();
			$xnb_data[$k]=array_merge($xnb_data[$k],$all_data);
			//闭盘价
			$close_data=$transactionrecords                 //昨天的闭盘价格
				->cache($v['currency_xnb_id'].'xnb_data_price',5)
				->where(['xnb'=>$v['currency_xnb_id'],'time'=>[['egt',$t_date-86400],['lt',$t_date]]])
				->field('price as close_price')
				->order('time desc')
				->limit(1)
				->select()[0];
			$close_data=$close_data?$close_data:array('close_price'=>0); //如果为空则返回最新价，确保涨跌为0
			$xnb_data[$k]=array_merge($xnb_data[$k],$close_data);

			//最新
			$cwhere['xnb']=$v['currency_xnb_id'];
			$cwhere['market']=$marke_id;
			$now_data=$transactionrecords                 //今天的最新价
			->where($cwhere)
				->field('price as now_price,time as now_price_time')
				->order('now_price_time desc,id desc')
				->find();
			if($now_data!=""){
				$xnb_data[$k]['currency_transactionrecords_price']=$now_data['now_price'];
			}else{
				$xnb_data[$k]['currency_transactionrecords_price']='0.00';
				$now_data=array('now_price'=>0);
			}
			$xnb_data[$k]=array_merge($xnb_data[$k],$now_data);
		}
		//日涨跌=（最新价-昨日收盘价格）/昨日收盘价格

		$this->ajaxReturn($xnb_data);
	}
	function getXnb(){
		$xnb_m=M('xnb');
		$markethouse_m=M('markethouse');
		$transactionrecords=M('transactionrecords');

		$marke_data=$markethouse_m->field('name,id')->order('id')->select();   //市场的展示
		$marke_id=preg_match("/^[1-9][0-9]*$/",I('markethouse'))>0 ? I('markethouse') : $marke_data[0]['id'];

		$xnb_all=$markethouse_m->where(['id'=>$marke_id])->field('xnb')->find();
		$xnb_all['xnb']=json_decode($xnb_all['xnb']);
		$map['currency_xnb.id']=['in',$xnb_all['xnb']];
		$map['currency_xnb.status']=array('eq',1);
		//得到虚拟币列表，最新价
		$table=$xnb_m
			->field('
				currency_xnb.name as currency_xnb_name,
				currency_xnb.id as currency_xnb_id,
				currency_xnb.brief,
				currency_xnb.imgurl,
				currency_transactionrecords.id as currency_transactionrecords_id,
				currency_transactionrecords.price as currency_transactionrecords_price
				')
			->where($map)
			->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb')
			->order('currency_transactionrecords.time desc,currency_transactionrecords.id desc')
			->select(false);  //虚拟币信息的展示

		$xnb_data=$xnb_m
			->table($table.'a')
			->where(array(
				'currency_xnb_id'=>array('neq',1),
			))
			->group('currency_xnb_id')
			->select();  //虚拟币信息的展示


		$date=time()-86400;//24以前的时间
		$xnb_where['time']=array('egt',$date);
		$xnb_where['market']=$marke_id;

		$t_date = strtotime(date('Y-m-d',time()));    //今日0点时间

		foreach ($xnb_data as $k=>$v){
			//24的成交量和成交额
			$xnb_where['xnb']=$v['currency_xnb_id'];
			
			$all_data=$transactionrecords
				->where([
					'xnb'=>$v['currency_xnb_id']
				])
				->field('xnb,number,price')
				->limit(100)
				->order('id desc')
				->select(false);

			$all_data= $transactionrecords->table($all_data.' a')->field('sum(number) as numbers,avg(price) as avg_price')->order()->find();

			$xnb_data[$k]=array_merge($xnb_data[$k],$all_data);

			//闭盘价
			$close_data=$transactionrecords                 //昨天的闭盘价格
			->where(['xnb'=>$v['currency_xnb_id'],'market'=>$marke_id,'time'=>[['egt',$t_date-86400],['lt',$t_date]]])
				->field('price as close_price')
				->order('time desc')
				->limit(1)
				->select()[0];
			$close_data=$close_data?$close_data:array('close_price'=>0); //如果为空则返回最新价，确保涨跌为0
			$xnb_data[$k]=array_merge($xnb_data[$k],$close_data);

			//最新
			$cwhere['xnb']=$v['currency_xnb_id'];
			$cwhere['market']=$marke_id;
			$now_data=$transactionrecords                 //今天的最新价
			->where($cwhere)
				->field('price as now_price,time as now_price_time')
				->order('time desc,id desc')
				->find();
			if($now_data!=""){
				$xnb_data[$k]['currency_transactionrecords_price']=$now_data['now_price'];
			}else{
				$xnb_data[$k]['currency_transactionrecords_price']='0.00';
				$now_data=array('now_price'=>0);
			}
			$xnb_data[$k]=array_merge($xnb_data[$k],$now_data);

		}

		$this->ajaxReturn($xnb_data);
	}
	
	
	
	
}
