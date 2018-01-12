<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;
//委托管理
class TransactionrecordsModel extends Model{
	//买家数据匹配+交易记录
	public function adds($buy,$sell,$market,$price,$number,$poundage_buy,$poundage_sell,$xnb,$type,$buyoderfor,$selloderfor,$standardmoney){
		
		$back=$this->add(array(   //卖家交易记录的生成！
			'buy'=>$buy,
			'sell'=>$sell,                  //$entrust_back['userid'] 卖家id
			'market'=>$market,                 //$xnb_back['currency_markethouse_id']  市场
			'price'=>$price,                         //$entrust_back['price']   单价
			'number'=>$number,
			'allmoney'=>$number*$price,                   //交易额
			'buypoundage'=>$poundage_buy,            //$xnb_back['buypoundage']   买家手续费
			'sellpoundage'=>$poundage_sell,            //$xnb_back['sellpoundage']   卖家手续费！
			'xnb'=>$xnb,
			'type'=>$type,
			'buyoderfor'=>$buyoderfor,              //买家订单号
			'selloderfor'=>$selloderfor,			//卖家订单号
			'standardmoney'=>$standardmoney,
			'time'=>time()
		));
		return $back;
	}
	//卖单记录生成

}
