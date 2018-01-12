<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Wap\Model;
use Think\Model;
//委托管理
class EntrustModel extends Model{
	
	//买家数据匹配
	public function buy_data($entrust_where){
		  $buydata= $this
		    ->field('id,userid,username,price,addtime,number,type,poundage,oderfor,allmoney,xnb')
		    ->where($entrust_where)
			->order('price,addtime')
			->limit(1)
			->find();
		return $buydata;
	}
	//卖单记录生成
	
	
	
	
}
