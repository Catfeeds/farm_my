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
//市场MarkethouseModel.class.php
class MarkethouseModel extends Model{
	//获取市场信息
	public function getMarkethouse($id){
		$back= $this->where(array(
			'id'=>$id
		))->field('
		id,
		name,
		standardmoney,
		openingquotation,
		closetime,
		buymaxprice,
		buyminprice,
		maxallmoney,
		minallmoney
		')->find();
		if ($back['id']!=""){
			$xnb_m=D('xnb');
			$back['standardmoney_brief']=$xnb_m->getstandar($back['standardmoney'])['brief'];
		}

		return $back;
	}

	//判断市场和虚拟币的关系是否合法
	public function criterionid($Market,$xnb_id){
		$Market=$this->field('xnb')->where(['id'=>$Market])->find();
		$Market['xnb']=json_decode($Market['xnb']);
		$Xnb=M('xnb')->field('status')->where(['id'=>$xnb_id])->find();
		if($Xnb['status']!=1){
			return false;
			exit();
		}
		if (in_array($xnb_id,$Market['xnb'])){
			return true;
			exit();
		};
		return false;
	}
}
