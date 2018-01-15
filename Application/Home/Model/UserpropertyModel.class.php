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

class UserpropertyModel extends Model{
	//判断用户虚拟币信息是足够
	public function checkmoney($type,$money,$types=false,$scale){

		$xnb_d=D('xnb');
		$field_name=$xnb_d->getstandar($type);//通过xnb的id返回相关信息,资产字段

		if ($field_name['id']==""){   //若果这个id没有说明本位币不存在
			return 1;
		}

		 $back=$this->where(array(
			 'userid'=>session('user')['id'],
		 ))->field($field_name['brief'].',repeats')->find();

		if ($types){  //判断 挂单比例是否大于规定的百分比，只有卖家才会判断
			if (($money/$back[$field_name['brief']])>($scale/100)){
				return 3;
			}
		}
		if ($money> ($back[$field_name['brief']]+$back['repeats'])){
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

    /**
     * 获取用户资产 ，并且锁定用户资产信息
     * @param $uid
     * @param $money_type
     * @return mixed
     */
	public function getUserMoneyAll($uid,$money_type)
	{
		return $this->lock(true)->field('userid,cny,'.$money_type)->where(['userid'=>$uid])->find();
	}

    /**
     * 扣除用户资产
     * @param $xnb_id 操作的xnb
     * @param $number 操作的数量
     * @param $userid 用户id
     * @param $operatetype   操作类型(资产流水的类型)
     * @param $status 1减少/2增加
     * @return bool
     */
	public function setChangeMoney($xnb_id,$number,$userid,$operatetype,$status = 1){
	    #获取xnb 简称
        $xnb_m   = new XnbModel();
	    $xnb_data = $xnb_m->getstandar($xnb_id);
	    if (empty($xnb_data['id'])){
	        $this->error = '消费类型不存在';
	        return false;

        }

		#锁死用户资产，并且返回用户资产信息
		$money_back = $this->getUserMoneyAll($userid,$xnb_data['brief']);
		if(!$money_back['userid']){
		    $this->error = '用户不存在！';
			return false;		
		}

		$back = true;

		if ($status==1){

            $back = $this->where(['userid'=>$userid])->setDec($xnb_data['brief'],$number);  // 减少用户资产

        }else{

            $back = $this->where(['userid'=>$userid])->setInc($xnb_data['brief'],$number);  // 减少用户资产

        }

		if (!$back){
            return false;
        }

        #用户资产变动明细
        $back = $this->poundage(
            ['id'=>$userid,'name'=>''],
            $xnb_id,
            $number,
            $type,
            $money_back[$xnb_data['brief']]
        );


        return $back;


	}

    /**
     * 用户资产记录的生成
     * @param array $user   用户信息
     *        ------------id 用户id
     *        ------------name 用户名(如果用户名为空，系统按id去查询name)
     * @param $xnb  操作的虚拟币
     * @param $number 操作的资产
     * @param $operatetype 操作的类型
     * @param $operaefront  操作之前的数量
     * @param $explain      备注信息
     */

	public function detail(array $user,$xnb,$number,$operatetype,$operaefront,$explain = null){

        $property_m = M('property');

        if (empty($user['name'])){
            $user_m = new UsersModel();
            $user_data = $user_m->getUserData($user['id']);
            $user['name'] = $user_data['users'];
        }

        $back = $property_m->add([
            'userid'=>$user['id'],
            'username'=>$user['name'],
            'xnb'=>$xnb,
            'operatenumber'=>$number, //操作的数量
            'operatetype'=>$operatetype, //操作的类型
            'operaefront'=>$operaefront,//操作之前
            'operatebehind'=>$operaefront+$number,
            'explain'=>$explain,
            'time'=>time(),
        ]);


        if (!$back) $this->error = '资产记录生成失败！';

        return $back ? $back : false;

    }



	

}
