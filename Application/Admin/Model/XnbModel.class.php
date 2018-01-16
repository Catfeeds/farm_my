<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;
//委托管理
class XnbModel extends Model{
	//获取虚拟币信息
	public function trade_xnb($add_data){
		  $xnb_data= $this
					  ->field('
					  id,
					  name,
					  buytop,
					  selltop,
					  riserange,
					  riserange,
					  fallrange,
					  brief,
					  buycomplicated,
					  sellcomplicated,
					  sellpoundage,
					  buypoundage,
					  scale
					  ')
					 ->where(['id'=>$add_data])->find();
		  return $xnb_data;
	}

	//通过xnb的id返回相关信息
	public function getstandar($id){
		$back=$this->field('brief,name,id')->where(array(
			'id'=>$id
		))->find();
		return $back;
	}

	public function getXnb_info($id,$info){
	    $back = $this->where(['id'=>$id])->field('id,'.$info)->find();
	    return $back[$info];
    }


}
