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

class UserpropertyModel extends Model{
	//判断用户虚拟币信息是足够
	public function checkmoney($type,$money,$types=false,$scale){

		$xnb_d=D('xnb');
		$field_name=$xnb_d->getstandar($type);//通过xnb的id返回相关信息,资产字段

		if ($field_name['id']==""){   //若果这个id没有说明本位币不存在
			return 1;
		}

		 $back=$this->where(array(
			 'userid'=>session('user_wap')['id'],
		 ))->field($field_name['brief'])->find();

		if ($types){  //判断 挂单比例是否大于规定的百分比，只有卖家才会判断
			if (($money/$back[$field_name['brief']])>($scale/100)){
				return 3;
			}
		}
		if ($money>$back[$field_name['brief']]+$back['repeats']){
			return 2;
			exit();
		}
		return $field_name;
	}

	//根据用户id返回用户某个货币的资产信息
	public function getUserMoney($uid,$type){
		$back=$this->where(array(
			'userid'=>$uid,
		))->field($type)->find();
		return $back;
	}

	//分红手续费使用的方法
	public function setmoney($id,$type,$int,$type_id,$property_d){


		$lock_back=$this->lock(true)->where(['userid'=>$id])->field('id,userid,username')->find();//将该条记录锁定

		if ($lock_back==false){
			return false;
			exit();
		}
		//用户资金；流水
		$property_buyset_back=$lock_back; //获取用户的财产信息
		$poundage['userid']=$lock_back['userid'];
		$poundage['username']=$lock_back['username'];
		$poundage['xnb']=$type_id;
		$poundage['operatenumber']=$int; //操作数量（金额）
		$poundage['operatetype']='手续费分红';
		$poundage['operaefront']=$property_buyset_back[$type];  //操作之前
		$poundage['operatebehind']=round($poundage['operaefront']+$poundage['operatenumber'],6); //操作之后
		$poundage['time']=time();
		$back_p=$property_d->add($poundage); //添加流水

		$back=$this->where(['userid'=>$id])->setInc($type,$int);

		if ($back==false || $back_p==false){
			return false;
			exit();
		}
		return true;

	}
	

}
