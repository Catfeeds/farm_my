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

class PropertyModel extends Model{
	public function PropertyAdd($data){
		$back=$this->add($data);
		if ($back==false){
			return false;
			exit();
		}
		return $back;
	}
}
