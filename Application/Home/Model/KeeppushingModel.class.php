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
//手续费的分红！
class KeeppushingModel extends Model{
	
	public function addKeeppushing($uid,$poundage,$type,$type_id,$time,$keepm_type,$oderfor){
		$user_m = M('users'); //用户表
		$userproperty_d= D('userproperty');
		$property_d  = D('property');  //记录表
		$keepmoney_m=M('keepmoney');
		$back=$this->find();
		$back['storey']=json_decode($back['storey'],true);
		$poundage=$poundage*($back['storey']['allnumber']/100);
		$int=1;  //递归的次数也是，分红多少层
		$pid=$user_m->where(['id'=>$uid])->field('id,pid,users')->find();   //初始化，查出自己的父级id
		return $backthis=$this->getpid($user_m,$userproperty_d,$pid,$type,$int,$poundage,$back['storey'],$type_id,$property_d,$keepmoney_m,$time,$keepm_type,$oderfor);
	}
	
	public function getpid($user_m,$userproperty_d,$pid,$type,$int,$poundage,$rule,$type_id,$property_d,$keepmoney_m,$time,$keepm_type,$oderfor){

		$poundage_b=($rule[$int]/100)*$poundage;  //本次的手续费
		$int++;
		$keepmoney['childid']=$pid['id'];
		$keepmoney['childname']=$pid['users'];

		$pid=$user_m->where(['id'=>$pid['pid']])->field('id,pid,users')->find();   //查出自己的父级id

		if ($pid['id']==""){
			return true;
			exit();
		}

		$back=$userproperty_d->setmoney($pid['id'],$type,$poundage_b,$type_id,$property_d);  //返回pid金额

		$keepmoney['userid']=$pid['id'];
		$keepmoney['username']=$pid['users'];
		$keepmoney['number']=$poundage_b;
		$keepmoney['type']=$keepm_type;
		$keepmoney['xnb']=$type_id;
		$keepmoney['oderfor']=$oderfor;
		$keepmoney['time']=$time;
		$keepmoney_back=$keepmoney_m->add($keepmoney); //分红手续费记录！

		if ($back==false || $keepmoney_back==false){
			return false;
			exit();
		}

		if ($int<count($rule)){
			$this->getpid($user_m,$userproperty_d,$pid,$type,$int,$poundage,$rule,$type_id,$property_d,$keepmoney_m,$time,$keepm_type,$oderfor);
		}
		return true;
	}
	

}










