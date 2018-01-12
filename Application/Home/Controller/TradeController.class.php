<?php 
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Home\Model\PropertyModel;
use Think\Controller;
//交易模块 购买
class TradeController extends HomeController{

	public function __construct(){
		parent::__construct();

		$this->redata();
		$xnb_id=$this->strFilter(I('id'));

		if ($xnb_id!="") {
			$xnb_d = D('xnb');
			$transactionrecords_m = M('transactionrecords'); //交易记录
			$markethouse_d=	D('markethouse');
			$entrustwater_m=M('entrustwater');

			$xnb_data = $xnb_d->trade_xnb($xnb_id);  //得到虚拟币信息以及他的市场信息
			$markethouse_id=I('markethouse');

			if (positive($markethouse_id)!=1){
				$this->redirect('Trade/buy');
				exit();
			}
			//虚拟币列表，本位币简称
			$xnb_allid=$markethouse_d->where(['id'=>$markethouse_id])->field('xnb,opentime')->find();//虚拟币列表
			$xnb_allid['xnb']=json_decode($xnb_allid['xnb']);
			$xnb_all=$xnb_d->where(['id'=>['in',$xnb_allid['xnb']]])->select();

			$where['xnb'] = $xnb_id;
			$where['time'] = array('egt',strtotime(date('Y-m-d',time())));    //今日0点时间
			$where['market']=$markethouse_id;
			$transaction_data = $transactionrecords_m->where($where)->field('max(price),min(price)')->find();  //查询最高最低价


			$transaction_allnumber= $transactionrecords_m->where(array(   //获取成交量
				'xnb'=>$xnb_id
			))->sum('number');
			$length=strlen(round($transaction_allnumber,0));            //获取交易量的长度！
			if ($length>=5 && $length<=8){
				$transaction_allnumber=round($transaction_allnumber/10000,2)."万";
			}
			if ($length>=9){
				$transaction_allnumber=round($transaction_allnumber/100000000,2).'亿';
			}

			$recently=$transactionrecords_m->where(['xnb'=>$xnb_id,'market'=>$markethouse_id])->field('price')->order('time desc,id desc')->find();

			$tran_all=$this->getentrust(1);			//买一卖一价

			$this->assign('sum_number',$transaction_allnumber); //成交量
			$this->assign('tran_all',$tran_all);
			$this->recently=$recently;
			$this->assign('recently',$recently);//最近价格
			$this->assign('hl',$transaction_data);//最高最低价
			$this->xnb_data=$xnb_data;
			$this->assign('xnb_data', $xnb_data);  //直接展示的虚拟币
			$this->assign('xnb_all', $xnb_all);  //虚拟币列表
		}
	}

	//交易中心的虚拟币列表
	public function buy(){
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
//				'currency_xnb_id'=>array('neq',68)
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

			$all_data= $transactionrecords->table($all_data.' a')->field('sum(number),avg(price)')->order()->find();
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
		$where['id']=$marke_id;
		$pd=M('markethouse')->field('standardmoney')->where($where)->find();
//		$xnb_data['pd']=$pd['standardmoney'];
		//日涨跌=（最新价-昨日收盘价格）/昨日收盘价格
		$this->assign('pd',$pd['standardmoney']);
		$this->assign('markethouse',$marke_id);
		$this->assign('xnb_data',$xnb_data);
		$this->assign('marke_data',$marke_data);
		$this->display();
	}


	//交易中心
	public function trade(){
		if(IS_POST){    //交易功能方法的调用！
			$business_tupe=I('business_tupe');
			if ($business_tupe==1 && session('user')['id']!=""){
				$this->trade_buy();
			}elseif ($business_tupe==2  && session('user')['id']!=""){
				$TradeSell=new TradeSellController();
				$TradeSell->trade_buy();
			}else{
				$this->error('请登录!');
			}
			exit();
		}
		$entrust_m=M('entrust');           //委托管理
		$entrustwater_m=M('entrustwater');           //委托管理
		$transactionrecords_m=M('transactionrecords'); //交易记录
		$xnb_m=M('xnb');
		$Userproperty_d=D('Userproperty');
		$markethouse_d=D('markethouse');

		$xnb_id=$this->strFilter(I('id'))? $this->strFilter(I('id')) :$this->strFilter(I('xnb'));
		$marke_id=$this->strFilter(I('markethouse'));   //市场id

		//币种信息

		if (!$markethouse_d->criterionid($marke_id,$xnb_id)){   //判断虚拟币和市场信息是否合法，不合法就重定向
			$this->redirect('buy');
			exit();
		}



		$brief=$markethouse_d    //本位币的简称
			->where(['currency_markethouse.id'=>$marke_id])
			->field('currency_xnb.brief,currency_xnb.id,currency_xnb.name')
			->join('left join currency_xnb on currency_markethouse.standardmoney=currency_xnb.id')
			->find();

		$user_money=$Userproperty_d->where(['userid'=>session('user')['id']])->field($this->xnb_data['brief'].",".$brief['brief'].',repeats')->find();             //用户拥有的资产

		$user_money_d_sell=$entrust_m->field('sum(number)')->where(['userid'=>session('user')['id'],'xnb'=>$this->xnb_data['id'],'type'=>2])->find();  //卖出冻结的资产

		$user_money_xb=$entrust_m->field('sum(allmoney)')
			->where(['standardmoney'=>$this->xnb_data['id'],'userid'=>session('user')['id'],'type'=>1])->find();  //虚拟币作为本位币交易，买单冻结

		$user_money_d['sum(number)']=$user_money_d_sell['sum(number)']+$user_money_xb['sum(allmoney)'];  //冻结的虚拟币=卖单+买单

		if ($brief['id']==1){  //如果本位币是人民币，就将虚拟币折合成本位币
			$user_money_all=($user_money_d['sum(number)']+$user_money[$this->xnb_data['brief']])*$this->recently['price'];  //折合人民币
			$this->assign('allxnb',$user_money_all);  //折合人民币
		}

		$user_money_bbuy=$entrust_m->field('sum(allmoney)')
			->where(['standardmoney'=>$brief['id'],'userid'=>session('user')['id'],'type'=>1])->find();  //本位币作为虚拟币进行购买交易，买单冻结的本位币
		$user_money_dsell=0;
		if ($brief['id']!=1){     //本位币不是人民币，本位币作为虚拟币交易卖出，卖单冻结的人民币
			$user_money_dsell=$entrust_m->field('sum(number)')
				->where(['xnb'=>$brief['id'],'userid'=>session('user')['id']])->find();  //冻结本位币
		}
		$user_money_dball=$user_money_bbuy['sum(allmoney)']+$user_money_dsell['sum(number)'];  // 冻结的本位币

		// 卖10，买10
		$buy_sell=$this->getentrust_s(10);
		//成交记录25条
		$transaction_data=$this->getTransaction(25);

		//该用户在该市场的该虚拟币的订单

		$list = $entrustwater_m
			->field('
				currency_entrustwater.type,
				currency_entrustwater.cancel,
				currency_entrustwater.id as currency_entrustwater_id,
				currency_entrustwater.oderfor,
				currency_entrustwater.username as currency_entrustwater_username,
				currency_entrustwater.price,
				currency_entrustwater.number,
				currency_entrustwater.allmoney,
				currency_entrustwater.addtime as currency_entrustwater_time,
				currency_entrust.id as currency_entrust_id
			')
			->join('left join currency_entrust on currency_entrustwater.id=currency_entrust.entrustwater_id')
			->where(array(
				'currency_entrustwater.userid'=>session('user')['id'],
				'currency_entrustwater.cancel'=>0,
				'currency_entrustwater.xnb'=>$xnb_id,
				'currency_entrustwater.market'=>$marke_id

			))->order('currency_entrustwater_time desc')->limit(5)->select();

			foreach ($list as $k=>$v){
				$where=array();
				switch ($v['type']){
					case 1:
						$where['buyoderfor']=$v['oderfor'];
						break;
					case 2:
						$where['selloderfor']=$v['oderfor'];
						break;
				}
				$where['market']=$marke_id;
				$deal=$transactionrecords_m->where($where)->sum('number');
				$deal=$deal?$deal:0;
				$list[$k]['deal']=$deal;
			}
		$where['id']=$marke_id;
		$pd=M('markethouse')->field('standardmoney')->where($where)->find();



		//本位币
		$this->assign('pd',$pd['standardmoney']);
		$this->assign('order_data',$list);//

		$this->assign('dbtr',$user_money_dball);  //冻结的本位币
		$this->assign('kytr',$user_money[$brief['brief']]);  //可用的本位币

        $this->assign('repeats',$user_money['repeats']);

		$this->assign('user_money_d',$user_money_d['sum(number)']);  //冻结的虚拟币
		$this->assign('usermoney',round($user_money[$this->xnb_data['brief']],6)); //虚拟币资产
		$this->markid();
		$this->assign('brief',$brief);
		$this->assign('transaction_data',$transaction_data);
		$this->assign('buy_data',$buy_sell['buy_data']);
		$this->assign('sell_data',$buy_sell['sell_data']);
		$this->display();
	}

	protected function trade_buy(){

		$transactionrecords_d=D('transactionrecords'); //（成交记录）
		$poundage_m=M('poundage');
		$entrust_d=D('entrust');           //委托管理
		$xnb_d=D('xnb');                   //虚拟币
		$userproperty_d=D('userproperty');//用户财产
		$markethouse_d=D('markethouse');  //市场
		$keeppushing_d=D('keeppushing');

		$price=I('price');    //单价
		$number=I('number');   //购买数量
		$dealpassword=I('dealpassword');   //交易密码
		$xnb_id=$this->strFilter(I('xnb'),true,'非法数据！');       //虚拟币id
		$markethouse=$this->strFilter(I('markethouse'),true,'非法数据！');   //交易市场

		if ($xnb_id==1){
			$this->error('非法数据');
		}
		


		if (session('user')['dealpwd']!=jiami($dealpassword)){   //交易密码验证
			$this->error('交易密码不正确！');
			exit();
		}

		if (check_number($price)!=$price || check_number($number)!=$number || $price=="" || $number==""){    //单价，校验
			$this->error('非法参数！1');
			exit();
		}

		if (!$markethouse_d->criterionid($markethouse,$xnb_id)){   //判断虚拟币与市场的关系是否合法！S
			$this->error('非法参数！2');
			exit();
		};

		$xnb_back=$xnb_d->trade_xnb($xnb_id);   //本次交易货币
		
		if ($xnb_back['id']==""){
			$this->error('非法参数！x');
			exit();
		}

		$markethouse_back=$markethouse_d->getMarkethouse($markethouse);  //本次市场信息,
		if ($markethouse_back['id']==""){
			$this->error('非法参数！M');
			exit();
		}



//		$poundage=$price*$number*$xnb_back['buypoundage']/100;  //本次手续费，扣购买的币
		$Transacallmoney=$price*$number;               //本次交易总额
		$number = round($number,6);     //购买数量,保留6位小数
		$price  =  round($price,6);		//购买单价,保留6位小数

		if($xnb_back['id']==""){
			$this->error('非法参数！2');
			exit();
		}

		if($markethouse_back['openingquotation']==2){
			$this->error('该市场未开盘，无法交易！');
			exit();
		}

		//判断判断本位币是否足够
		$checkmoney_back=$userproperty_d->checkmoney($markethouse_back['standardmoney'],$Transacallmoney);

		switch ($checkmoney_back){
			case 1:
				$this->error("系统错误！请联系我们！1");
				exit();
				break;
			case 2:
				$this->error("余额不足！请充值或分批购买");
				exit();
				break;
		}
		
		//交易买入上限
//		if($number>$xnb_back['buytop']){
//			$this->error("挂单数超过上限".$xnb_back['buytop']."！");
//			exit();
//		}


		$transac['xnb']=$xnb_id;
		$transac['market']=$markethouse_back['id'];
		$transac['time']=array('gt',$xnb_back['closetime']);

		$transac_back=$transactionrecords_d->field('id,price')->where($transac)->order('time desc')->limit(1)->select();

		if($price/$transac_back['price']>$xnb_back['riserange']/100){
			$this->error("你的单价超过涨停幅度！".$xnb_back['riserange']."%");
			exit();
		}

		if($price/$transac_back['price']>$xnb_back['fallrange']/100){
			$this->error("你的单价超过跌停幅度！".$xnb_back['fallrange']."%");
			exit();
		}
		if ($Transacallmoney>$markethouse_back['maxallmoney'] && $markethouse_back['maxallmoney']!=0){                          //买家最大交易价
			$this->error('单笔交易总额大于最大交易总额'.'￥'.$markethouse_back['maxallmoney'].' !');
			exit();
		}
		if ($Transacallmoney<$markethouse_back['minallmoney'] && $markethouse_back['minallmoney']!=0){                          //买家最大交易价
			$this->error('单笔交易总额小于最小交易总额'.'￥'.$markethouse_back['minallmoney'].' !');
			exit();
		}
		//最大最小交易额暂定挂单时限制
		if ($price>$markethouse_back['buymaxprice'] && $markethouse_back['buymaxprice']!=0){                          //买家最大交易价
			$this->error('单价大于最大交易价'.'￥'.$markethouse_back['buymaxprice'].' !');
			exit();
		}

		if ($price<$markethouse_back['buyminprice'] && $markethouse_back['buyminprice']!=0){                           //买家最小交易价
			$this->error('单价小于最小交易价'.'￥'.$markethouse_back['buyminprice'].' !');
			exit();
		}

		$standardmoney_id   = $markethouse_back['standardmoney'];  //该市场本位币id
		$standardmoney_name = $markethouse_back['standardmoney_brief'];   //该市场本位币简称
		$xnb_name=$xnb_back['brief'];   //此次交易虚拟币简称

		$fp = fopen($xnb_back['buycomplicated'],'r+');     //文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读
		if(flock($fp,LOCK_EX)){         //单线业务逻辑处理
			$property_d = D('property');          //用户资产流水
			

			$entrustwater_m =M('entrustwater');   //挂单记录
			$entrust_m = M('entrust');

			$entrustwater_m->startTrans();   //开启事务

			$buy_trde=array(
				'userid'=>session('user')['id'],
				'username'=>session('user')['user_name'],
				'market'=>$markethouse_back['id'],  //市场
				'price'=>$price,
				'number'=>round($number,6),
				'allmoney'=>$Transacallmoney,   //交易总额
				'type'=>1,                      //1买2卖
				'poundage'=>$xnb_back['buypoundage'],  //记录手续费，手续费为百分比，按购买到的币百分比收币
				'xnb'=>$xnb_id,                 // 购买的虚拟币类型
				'oderfor'=>session('user')['id'].time().rand(1000000,2000000),
				'addtime'=>time(),
				'standardmoney'=>$standardmoney_id  //本位币
			);

			//交易开始将用户资产锁死，获取本用户的资产
			$lock_back=$userproperty_d->lock(true)->where(array(
				'userid'=>session('user')['id']
			))->find();

			if ($lock_back!=true){            //判断是否开启读写锁死！
				$entrustwater_m->rollback();
				$this->error('挂单失败！9');
				exit();
			}

            #重消费

            if ($lock_back['repeats']>0){
                $pay_money = $price*$number;
                $pay_repeats = 0;
                    if ($lock_back['repeats']>=$pay_money){ #如果重销足够,全部扣重销

                        $pay_repeats = $pay_money;

                    }else{

                        $pay_repeats = $lock_back['repeats'];  #如果不够 ，讲重销扣完 ，并且扣现金

                        //买家购买金额的流水
                        $property_buyset_back=$lock_back; //获取用户的财产信息
                        $property_buyset['userid']=session('user')['id'];
                        $property_buyset['username']=session('user')['user_name'];
                        $property_buyset['xnb']=$markethouse_back['standardmoney'];  //买家扣除的是市场本位币
                        $property_buyset['operatenumber']=$price*$number-$pay_repeats; //操作数量（金额）
                        $property_buyset['operatetype']='挂单扣除';
                        $property_buyset['operaefront']=$property_buyset_back[$standardmoney_name];  //操作之前
                        $property_buyset['operatebehind']=round($property_buyset['operaefront']-$property_buyset['operatenumber'],6); //操作之后
                        $property_buyset['time']=$buy_trde['addtime'];
                        $back=$property_d->PropertyAdd($property_buyset); //添加流水
                        if ($back==false){
                            $entrustwater_m->rollback();
                            $this->error('挂单失败！L1');
                            exit();
                        }

                        //买家扣除购买金额，市场本位币
                        $user_setDec_back=$userproperty_d->where(array(
                            'userid'=>session('user')['id']
                        ))->setDec($standardmoney_name,$price*$number-$pay_repeats);

                        if($user_setDec_back==false){
                            $entrustwater_m->rollback();
                            $this->error('挂单失败！1');
                            exit();
                        }

                    }


                    #优先扣除重销币   因为有重销，所以不论哪种情况都要扣重销账户
                    //重销币的流水
                    $property_buyset_back=$lock_back; //获取用户的财产信息
                    $property_buyset['userid']=session('user')['id'];
                    $property_buyset['username']=session('user')['user_name'];
                    $property_buyset['xnb']=2;  //买家扣除的是市场本位币
                    $property_buyset['operatenumber']=$pay_repeats; //操作数量（金额）
                    $property_buyset['operatetype']='挂单扣除';
                    $property_buyset['operaefront']=$property_buyset_back[$standardmoney_name];  //操作之前
                    $property_buyset['operatebehind']=round($property_buyset['operaefront']-$property_buyset['operatenumber'],6); //操作之后
                    $property_buyset['time']=$buy_trde['addtime'];
                    $back=$property_d->PropertyAdd($property_buyset); //添加流水
                    if ($back==false){
                        $entrustwater_m->rollback();
                        $this->error('挂单失败！L1');
                        exit();
                    }

                    //买家扣除重销币
                    $user_setDec_back=$userproperty_d->where(array(
                        'userid'=>session('user')['id']
                    ))->setDec('repeats',$pay_repeats);

                    if($user_setDec_back==false){
                        $entrustwater_m->rollback();
                        $this->error('挂单失败！1');
                        exit();
                    }
                $buy_trde['repeats'] = $pay_repeats;

            }else{
                //买家购买金额的流水
                $property_buyset_back=$lock_back; //获取用户的财产信息
                $property_buyset['userid']=session('user')['id'];
                $property_buyset['username']=session('user')['user_name'];
                $property_buyset['xnb']=$markethouse_back['standardmoney'];  //买家扣除的是市场本位币
                $property_buyset['operatenumber']=$price*$number; //操作数量（金额）
                $property_buyset['operatetype']='挂单扣除';
                $property_buyset['operaefront']=$property_buyset_back[$standardmoney_name];  //操作之前
                $property_buyset['operatebehind']=round($property_buyset['operaefront']-$property_buyset['operatenumber'],6); //操作之后
                $property_buyset['time']=$buy_trde['addtime'];
                $back=$property_d->PropertyAdd($property_buyset); //添加流水
                if ($back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！L1');
                    exit();
                }

                //买家扣除购买金额，市场本位币
                $user_setDec_back=$userproperty_d->where(array(
                    'userid'=>session('user')['id']
                ))->setDec($standardmoney_name,$price*$number);

                if($user_setDec_back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！1');
                    exit();
                }
            }


			//挂单记录的处理
			$water_back=$entrustwater_m->add($buy_trde);
			if ($water_back===false){
				$entrustwater_m->rollback();
				$this->error('挂单失败！2');
				exit();
			}
			$buy_trde['entrustwater_id']=$water_back; //挂单id
			
			//交易处理
			$entrust_where['price']=array('elt',$price);    //匹配条件 小于等于 买家
			$entrust_where['type']=2;                       //卖出类型
			$entrust_where['xnb']=$xnb_id;                   //购买的虚拟币
			$entrust_where['market']=$markethouse_back['id'];  //所在的市场

			$this->Transactionrecords(
				$entrust_d,
				$entrust_where,
				$entrust_m,
				$buy_trde,
				$entrustwater_m,
//				$number,
				$transactionrecords_d,
//				$markethouse_back['id'],        //市场
//				$xnb_back['buypoundage'],      //买家手续费
//				$xnb_back['sellpoundage'],    //卖家手续费
				$poundage_m,
				$standardmoney_id,
				$standardmoney_name,
				$userproperty_d,
				$xnb_name,
				$property_d,
				$keeppushing_d,
				$xnb_back['memory_day']
				);

			$entrustwater_m->commit();
			$this->success("挂单成功！");
		}else{
			$this->error('系统错误！请联系我们！wj');
		}
		fclose($fp);
		exit();
	}
		//购买交易方法
	protected function Transactionrecords(                            //购买交易方法
		$entrust_d,
		$entrust_where,
		$entrust_m,
		$buy_trde,
		$entrustwater_m,
//		$number,
		$transactionrecords_d,
//		$market,
//		$buypoundage,
//		$sellpoundage,
		$poundage_m,
		$standardmoney_id,
		$standardmoney_name,
		$userproperty_d,
		$xnb_name,
		$property_d,
		$keeppushing_d,
		$memory_day		//储存天数

		){

		$entrust_back=$entrust_d->buy_data($entrust_where);     //匹配交易数量

		if ($entrust_back['id']==""){                     //如果卖家单id为空，说明价格过低匹配失败，交易单直接挂起！

			$entrust_addback=$entrust_m->add($buy_trde);
			if ($entrust_addback===false){
				$entrustwater_m->rollback();
				$this->error('挂单失败！3');
				exit();
			}
			$entrustwater_m->commit();
			$this->success('挂单成功！');
			exit();
		}
		//数据库读写锁死，该订单和发布订单的资产！
		$entrust_lock      =  $entrust_d->lock(true)->where(array('id'=>$entrust_back['id']))->find(); //将查找出来的订单锁死，防止数据脏读
		$userproperty_lock =  $userproperty_d->lock(true)->where(array('userid'=>$entrust_back['userid']))->find();       //将该订单发布者的资产锁住，防止脏读数据
		$entrustwater_lock =  $entrustwater_m->lock(true)->where(array('oderfor'=>$entrust_back['oderfor']))->find();    //将该委托记录锁死

		if ($entrust_lock!=true || $userproperty_lock!=true || $entrustwater_lock!=true){
			$entrustwater_m->rollback();
			$this->error('挂单失败！3');
			exit();
		}


		$trde          =0; //交易数
		$status        =0; //状态变量，用于判断是否递归
		$poundage_buy  =0; //本次买家手续费
		$poundage_sell =0; //本次卖家手续费
		$number_top    =$buy_trde['number'];  //交易前的数量
//		$poundage_top  =$buy_trde['poundage'];               //交易前的手续费
		$sell_back=0;   //判断卖家是否卖完


		if ($number_top>$entrust_back['number']) {       //买单大于卖单
			$sell_back=1;
			$status=1;
			$trde                = $entrust_back['number'];         //本次交易数
			$buy_trde['number'] = $buy_trde['number']-$entrust_back['number'];  //交易后的数量，递归变量

			$entrust_deleteback=$entrust_d->where(array(              //如果买单大于卖单，说明卖单已经卖完，则删除该条记录.
				'id'=>$entrust_back['id']
			))->delete();

			if ($entrust_deleteback==false){
				$entrustwater_m->rollback();
				$this->error('挂单失败！4');
				exit();
			}
		}

		if ($number_top<$entrust_back['number']){         //买单小于卖单
			$trde                = $buy_trde['number'];                                      //本次交易数
			$buy_trde['number'] = 0;  //交易后的数量，递归变量

			$entrust_saveback=$entrust_d->where(array(    //扣除本次交易数量
				'id'=>$entrust_back['id']
			))->setDec('number',$trde);

			if ($entrust_saveback==false){
				$entrustwater_m->rollback();
				$this->error('挂单失败！5');
				exit();
			}
			
		}

		if ($number_top==$entrust_back['number']){
			$sell_back=1;
			$trde                = $buy_trde['number'];         //本次交易数
			$buy_trde['number'] = 0;     //交易后的数量，递归变量

			$entrust_deleteback=$entrust_d->where(array(         //如果买单等于卖单，说明卖单已经卖完，则删除该条记录
				'id'=>$entrust_back['id']
			))->delete();
			if ($entrust_deleteback==false){
				$entrustwater_m->rollback();
				$this->error('挂单失败！6');
				exit();
			}
		}


		if ($sell_back===1){
			$sell_backs=$entrustwater_m->where(['id'=>$entrustwater_lock['id']])->save(['cancel'=>2]);
			if ($sell_backs==false){
				$entrustwater_m->rollback();
				$this->error('挂单失败！11');
				exit();
			}

		}

		//因为手续费有可能因为小数点的原因，变为负数，所以当小于0的时候，将手续费变为0
		$poundage_buy           	= getEffective($trde*($buy_trde['poundage']/100));          //本次买家手续费=交易数量*买家手续费百分比

		$poundage_sell         		= getEffective($trde*$entrust_back['price']*($entrust_back['poundage']/100));      //卖家手续费=（交易数量*单价）* 卖家手续费
		
		$buy_trde['allmoney']     	= getEffective($buy_trde['allmoney']-($trde*$entrust_back['price']));   //总金额=总金额-交易额
		
		//交易记录的生成！
		$transactionrecords_addback=$transactionrecords_d->adds(
			session('user')['id'],
			$entrust_back['userid'],
			$buy_trde['market'],
			$entrust_back['price'],
			$trde,
			$poundage_buy,
			$poundage_sell,
			$buy_trde['xnb'],
			1,
			$buy_trde['oderfor'],
			$entrust_back['oderfor'],/*$buy_trde['oderfor']*/
			$standardmoney_id
			);

		//手续费记录的生成！
		$oderfor=array(
			'买家订单'=>$buy_trde['oderfor'],
			'卖家订单'=>$entrust_back['oderfor']
		);
		$oderfor=json_encode($oderfor);


		//分红手续费功能的实现！
		$keep = $keeppushing_d->addKeeppushing(session('user')['id'],$poundage_buy,$xnb_name,$entrust_back['xnb'],$buy_trde['addtime'],'买入虚拟币',$oderfor);

		if ($keep==false){
			$entrustwater_m->rollback();
			$this->error('挂单失败！L6');
			exit();
		}

		$poundage_addback=$poundage_m->add(array(   //买家手续费
			'market'=>$buy_trde['market'],
			'type'=>1,
			'money'=>$poundage_buy,
			'time'=>$buy_trde['addtime'],
			'xnb'=>$entrust_back['xnb'],  //币种类型,为得到的虚拟币
			'oderfor'=>$buy_trde['oderfor'],
			'userid'=>session('user')['id'],
			'username'=>session('user')['user_name']
		));

		//分红手续费功能的实现！
		$keep=$keeppushing_d->addKeeppushing($entrust_back['userid'],$poundage_sell,$standardmoney_name,$standardmoney_id,$buy_trde['addtime'],'卖出虚拟币',$oderfor);

		if ($keep==false){
			$entrustwater_m->rollback();
			$this->error('挂单失败！L9');
			exit();
		}

		$poundage_addbacks=$poundage_m->add(array(   //卖家手续费
			'market'=>$buy_trde['market'],
			'type'=>2,
			'money'=>$poundage_sell,
			'time'=>$buy_trde['addtime'],
			'xnb'=>$standardmoney_id,
			'oderfor'=>$entrust_back['oderfor'],
			'userid'=>$entrust_back['userid'],
			'username'=>$entrust_back['username'],
		));

		//卖家资产流水账的生成！
		$property_sell_back=$userproperty_d->getUserMoney($entrust_back['userid'],$standardmoney_name.',repeat'); //获取用户的财产信息
		

		//卖家收入本金币  ....  卖家收入的本金币的一部分进入重消账户

		#进入重复消费
		$tradeSellController = new TradeSellController();
		$repeat_back = $tradeSellController->repeat([  //返回本次增加的重消币
			'userid'=>$entrust_back['userid'],  //用户id
			'money'=>getEffective($trde*$entrust_back['price']-$poundage_sell) //本次交易的金额
		]);

		if ($repeat_back){
			#重复消费资金明细
			$property_repeat['userid']=$entrust_back['userid'];
			$property_repeat['username']=$entrust_back['username'];
			$property_repeat['xnb']=2;  //卖家得到的是本位币
			$property_repeat['operatenumber']=$repeat_back; //操作数量
			$property_repeat['operatetype']='卖出收入';
			$property_repeat['operaefront']=$property_sell_back['repeat'];  //操作之前
			$property_repeat['operatebehind']=getEffective($property_repeat['operaefront']+$property_repeat['operatenumber']); //操作之后
			$property_repeat['time']=$buy_trde['addtime'];
			$back=$property_d->PropertyAdd($property_repeat); //添加流水
			if ($back==false){
				$this->error('挂单失败！B');
			}
		}

		$property_sell['userid']=$entrust_back['userid'];
		$property_sell['username']=$entrust_back['username'];
		$property_sell['xnb']=$standardmoney_id;  //卖家得到的是本位币
		$property_sell['operatenumber']=getEffective($trde*$entrust_back['price']-$poundage_sell-$repeat_back); //操作数量
		$property_sell['operatetype']='卖出收入';
		$property_sell['operaefront']=$property_sell_back[$standardmoney_name];  //操作之前
		$property_sell['operatebehind']=getEffective($property_sell['operaefront']+$property_sell['operatenumber']); //操作之后
		$property_sell['time']=$buy_trde['addtime'];
		$back=$property_d->PropertyAdd($property_sell); //添加流水
		if ($back==false){
			$entrustwater_m->rollback();
			$this->error('挂单失败！L');
			exit();
		}
		
		

		
		#进入本位币
		$userproperty_savaback=$userproperty_d->where(array(
			'userid'=>$entrust_back['userid']
		))->setInc($standardmoney_name,getEffective($trde*$entrust_back['price']-$poundage_sell-$repeat_back));


		//买家资产流水账的生成！

       $property_buy_back=$userproperty_d->getUserMoney(session('user')['id'],$xnb_name); //获取用户的财产信息
       $property_buy['userid']=session('user')['id'];
       $property_buy['username']=session('user')['user_name'];
       $property_buy['xnb']=$buy_trde['xnb'];     //买家收入的是认购币
       $property_buy['operatenumber']=$trde-$poundage_buy; //操作数量
       $property_buy['operatetype']='买入收入';
       $property_buy['operaefront']=$property_buy_back[$xnb_name];  //操作之前
       $property_buy['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
       $property_buy['time']=$buy_trde['addtime'];

		/*  废弃该方法 ，得到的认购币自动锁死
     $back=$property_d->PropertyAdd($property_buy); //添加流水
     if ($back==false){
         $entrustwater_m->rollback();
         $this->error('挂单失败！L');
         exit();
     }

     //买家收入认购币 交易数量-手续费

     $userproperty_savabacks=$userproperty_d->where(array(
         'userid'=>session('user')['id']
     ))->setInc($xnb_name,$trde-$poundage_buy);
     */

        //资产转存
        $memory_m = M('memory');
        $back = $memory_m->add([
            'user_id'=>session('user')['id'],
            'xnb_id'=>$entrust_back['xnb'],
            'number_all'=>$trde,
            'time_start'=>time(),
            'time_end'=>time()+($memory_day*86400),
            'balance'=>$trde
        ]);

        if ($back==false){
            $entrustwater_m->rollback();
            $this->error('挂单失败！z');
            exit();
        }


		if ($poundage_addback==false||$poundage_addbacks==false || $transactionrecords_addback===false || $userproperty_savaback===false ){    //手续费类型添加，交易记录添加统一回滚
			$entrustwater_m->rollback();
			$this->error('挂单失败！7');
			exit();
		}

		if ($status==1){
			$this->Transactionrecords($entrust_d,
				$entrust_where,
				$entrust_m,
				$buy_trde,
				$entrustwater_m,
//				$number,
				$transactionrecords_d,
//				$market,
//				$buypoundage,
//				$sellpoundage,
				$poundage_m,
				$standardmoney_id,
				$standardmoney_name,
				$userproperty_d,
				$xnb_name,
				$property_d,
				$keeppushing_d,
				$memory_day
				);   //递归
		}
		//如果$status!=1说明该流程将买家的购买数量完成了，那么将返回用户未消耗完的用于购买的金额
		//修改订单状态为已完成！
		$bakc_stutus=$entrustwater_m->where(array('id'=>$buy_trde['entrustwater_id']))->save(['cancel'=>2]);
		if ($bakc_stutus===false){
			$entrustwater_m->rollback();
			$this->error('挂单失败！p');
		}

		if ($buy_trde['allmoney']>0){
            $property_uback_back=$userproperty_d->getUserMoney(session('user')['id'],$standardmoney_name); //获取用户的财产信息

		    $back_money = $buy_trde['allmoney'];
            $entrustwater_lock = $entrustwater_m->where(['id'=>$buy_trde['entrustwater_id']])->find();

            if ($entrustwater_lock['repeats']>0){  //如果用户使用重消账户支付

                #扣除的金额
                $out_money = $entrustwater_lock['allmoney']-$buy_trde['allmoney'];

                if ($out_money<=$entrustwater_lock['repeats']){  //消费的金额<重消金额
                    #应返的重消金额
                    $pay_repeats = $entrustwater_lock['repeats']- $out_money;
                    #应返的本金
                    $pay_money = $entrustwater_lock['allmoney']-$entrustwater_lock['repeats'];

                    #返回用户重消金额
                    $user_setinc=$userproperty_d->where(array('userid'=>$entrust_lock['userid']))->setInc('repeats',$pay_repeats);
                    if ($user_setinc==false){
                        $entrust_m->rollback();
                        $this->error('撤销失败！2');
                    }


                    #返回用户重消金额的流水
                    $property_buyset_repeats = $property_uback_back; //获取用户的财产信息
                    $property_repeats['userid'] = session('user')['id'];
                    $property_repeats['username'] = session('user')['user_name'];
                    $property_repeats['xnb']=2;  //买家扣除的是市场本位币
                    $property_repeats['operatenumber'] = $pay_repeats; //操作数量（金额）
                    $property_repeats['operatetype']='买单余额返回';
                    $property_repeats['operaefront']=$property_buyset_repeats['repeats'];  //操作之前
                    $property_repeats['operatebehind'] = round($property_repeats['operaefront']+$property_repeats['operatenumber'],6); //操作之后
                    $property_repeats['time'] = time();
                    $back=$property_d->PropertyAdd($property_repeats); //添加流水
                    if ($back==false){
                        $entrust_m->rollback();
                        $this->error('挂单失败！L1');
                        exit();
                    }



                }

            }else{
                $property_uback=$userproperty_d->where(array(   //返回金额
                    'uerid'=>session('user')['id']
                ))->setInc($standardmoney_name,$buy_trde['allmoney']);


                if ($property_uback==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！s');
                }

                //返回买家金额的流水

                $property_uback['userid']=session('user')['id'];
                $property_uback['username']=session('user')['user_name'];
                $property_uback['xnb']=$standardmoney_id;  //买家收入的是认购币
                $property_uback['operatenumber']=$buy_trde['allmoney']; //操作数量（金额）
                $property_uback['operatetype']='买单余额返回';
                $property_uback['operaefront']=$property_uback_back[$standardmoney_name];  //操作之前
                $property_uback['operatebehind']=$property_buy['operaefront']+$property_buy['operatenumber']; //操作之后
                $property_uback['time']=$buy_trde['addtime'];
                $back=$property_d->PropertyAdd($property_uback); //添加流水
                if ($back==false){
                    $entrustwater_m->rollback();
                    $this->error('挂单失败！L3');
                    exit();
                }
            }



		}

	}

	//数据请求 卖1，买1数据请求
	public function getentrust($limit){
		$xnb_id=$this->strFilter(I('id'));
		$market=$this->strFilter(I('markethouse'));
		$entrust_m=M('entrust');
		$tentrust=array();
		//卖10，买10
		$tentrust['buy_data']=$entrust_m->where(array(
			'type'=>1,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->field('price,number')->limit($limit)->order('price desc,addtime asc')->select();

		$tentrust['sell_data']=$entrust_m->where(array(
			'type'=>2,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->field('price,number')->limit($limit)->order('price ,addtime asc')->select();
		return $tentrust;
	}

	//数据请求 卖13，买13数据请求
	public function getentrust_s($limit){
		$xnb_id=$this->strFilter(I('id'));
		$market=$this->strFilter(I('markethouse'));

		$entrust_m=M('entrust');
		$tentrust=array();
		//卖10，买10
		$tentrust['buy_data']=$entrust_m->where(array(
			'type'=>1,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->field('price,sum(number) as num')->group('price')->order('price desc')->limit($limit)->select();

		$tentrust['sell_data']=$entrust_m->where(array(
			'type'=>2,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->field('price,sum(number) as num')->limit($limit)->group('price')->order('price desc,addtime asc')->select();
		return $tentrust;
	}

	//数据获取方法，前25条交易记录
	public function getTransaction($int){
		$transactionrecords_m=M('transactionrecords');
		$market=$this->strFilter(I('markethouse'));
		$xnb_id=$this->strFilter(I('id'));
		$transaction_data=$transactionrecords_m->where(array(
			'xnb'=>$xnb_id,
			'market'=>$market
		))->field('time,type,price,number')->limit(25)->order('time desc')->select();
		return $transaction_data;
	}

	//交易行情
	public function market(){
		//前50条交易记录
		$water_data=$this->getTransaction(50);
		//前50挂单
		$entrust_data=$this->getentrust_s(50);
		$this->markid();
		$this->assign('water_data',$water_data);
		$this->assign('entrust_data',$entrust_data);
		$this->display();
	}

	//了解虚拟币
	public function introduce(){
		$id = $this -> strFilter(I('id'));
		$xnbdetail = M("xnb") -> where('id = '. $id) -> find();
		$this -> assign("xnbdetail", $xnbdetail);
		$this -> display();
	}

	//评价列表
	public function evaluate(){
		$xnbid  = $this -> strFilter(I('id'));

		//分页显示评价
		$rows = 10;
		$map = array("xnb_id" => $xnbid);
		$count = M("comment") -> where($map) -> count();
		$page = new \Think\Page($count, $rows);
		$page -> setConfig('prev', "上一页");
		$page -> setConfig('next', '下一页');
		$page -> parameter['id'] = $xnbid;
		$show = $page -> show();
		$ex_show = explode("<a class", $show);
		for ($i = 0; $i < count($ex_show); $i ++) {
			$ex_show[$i] = "<li><a class". $ex_show[$i] ."</a></li>";
		}
		unset($ex_show[0]);
		$show = implode("", $ex_show);
		$list = M("comment") -> where($map) -> limit($page -> firstRow, $page -> listRows) -> select();
		//每个评论的被赞和被嘲数
		for ($i = 0; $i < count($list); $i ++) {
			$list[$i]['great'] = M("commentlike") -> where("commentid = ". $list[$i]['id']) -> count();
		}

		//显示评分 求平均值
		$score = M("commentscore") -> where($map) -> field('tecscore, appscore, proscore') -> select();
		$count = count($score);
		$tecscoretotal = 0;
		$appscoretotal = 0;
		$proscoretotal = 0;
		foreach ($score as $key => $value) {
			$tecscoretotal += $value['tecscore'];
			$appscoretotal += $value['appscore'];
			$proscoretotal += $value['proscore'];
		}
		$tecscore = (float) number_format($tecscoretotal / $count, 1);
		$appscore = (float) number_format($appscoretotal / $count, 1);
		$proscore = (float) number_format($proscoretotal / $count, 1);
		$score    = array("tecscore" => $tecscore, "appscore" => $appscore, "proscore" => $proscore);

		//progressbar
		$tecprogress = round($tecscore / 5 * 100, 2). "%";
		$appprogress = round($appscore / 5 * 100, 2). "%";
		$proprogress = round($proscore / 5 * 100, 2). "%";
		$progress    = array("tecprogress" => $tecprogress, "appprogress" => $appprogress, "proprogress" => $proprogress);

		//stars
		$star = round(($tecscore + $appscore + $proscore) / 3);
		$starcount = array();
		for($i = 0; $i < $star; $i ++) {
			array_push($starcount, "");
		}
		$starempty = array();
		for($j = 0; $j < (5 - $star); $j ++) {
			array_push($starempty, "");
		}

		$this -> assign("starcount", $starcount);
		$this -> assign("starempty", $starempty);
		$this -> assign("progress", $progress);
		$this -> assign("score", $score);
		$this -> assign("page", $show);
		$this -> assign("xnbid", $xnbid);
		$this -> assign("list", $list);

		$this -> display();
	}

	//提交评价
	public function evaluatePost() {
		$data = array();

		$userid = session('user.id');
		$username = M("users") -> where('id = '. $userid) -> field('users') -> find();

		//计算相关评分
		$xnbid    = $this -> strFilter(I('xnbid'));
		$tecscore = $this -> strFilter(I('tecscore'));
		$appscore = $this -> strFilter(I('appscore'));
		$proscore = $this -> strFilter(I('proscore'));

		//评论部分
		$data['userid']   = $userid;
		$data['username'] = $username['users'];
		$data['xnb_id']   = $xnbid;
		$data['text']     = $this -> strFilter(I('text'));
		$data['time']     = time();

		//评分部分
		$score['userid'] = $userid;
		$score['xnb_id'] = $xnbid;
		$score['tecscore'] = $tecscore;
		$score['appscore'] = $appscore;
		$score['proscore'] = $proscore;

		$res_evaluate = "";
		$res_score    = "";
		if ($data['text'] != ""){
			$res_evaluate = M("comment") -> add($data);
		}
		if ($score['tecscore'] != "undefined" && $score['appscore'] != "undefined" && $score['proscore'] != "undefined") {
			$res_score = M("commentscore") -> add($score);
		}

		if ($res_evaluate || $res_score) {
			$this -> success("评价成功");
		} else {
			$this -> error("评价失败");
		}
	}

	//评分只能评一次
	public function havscore() {
		$xnb_id = $this -> strFilter(I('xnbid'));
		$userid = session('user.id');

		$map = array("xnb_id" => $xnb_id, "userid" => $userid);
		$res = M("commentscore") -> where($map) -> find();
		if ($res) {
			$this -> error("已经评分过");
		} else {
			$this -> success();
		}
	}

	//判断是否点赞过
	public function like() {
		$commentid = $this -> strFilter(I('commentid'));
		$userid = session("user.id");
		if ($userid == "") {
			$this -> error(2);//未登录
		} else {
			//判断是否点赞或嘲笑过
			$map = array("commentid" => $commentid, "userid" => $userid);
			$like = M("commentlike") -> where($map) -> find();
			if ($like) {
				$this -> error("已经点过赞了");//已经赞过
			} else {
				$data['userid'] = $userid;
				$data['commentid'] = $commentid;

				$res = M("commentlike") -> where($map) -> add($data);
				if ($res) {
					$count = M("commentlike") -> where("commentid = ". $commentid) -> count();
					$this -> success($count);
				} else {
					$this  -> error("点赞失败");//错了
				}
			}
		}
	}

	//一分钟之内只能评论3次
	public function frequency() {
		$xnbid = $this -> strFilter(I('xnbid'));
		$userid = session('user.id');
		$map = array("userid" => $userid, "xnbid" => $xnbid);
		$res = M("comment") -> where($map) -> field("time") -> order("id desc") -> limit(3) -> select();
		$lastime = $res[2]['time'];
		$now = time();
		$diff = $now - $lastime;
		$remain = $diff % 86400 % 3600;
		$mins = intval($remain/60);
		if ($mins < 1) {
			$this -> error("一分钟内只能评价三次");
		} else {
			$this -> success();
		}
	}
	//行情图
	public function echarts(){
		$xnb_id=$this->strFilter(I('xnb'));
		$market_m=M('markethouse');
		$market_data=$market_m->select();
		$market=I('mark');
		$market=  $market=="" ? $market_data[0]['id']:$market;
		$entrust_m=M('entrust');
		$tentrust=array();
		//卖10，买10
		$tentrust['buy_data']=$entrust_m->where(array(
			'type'=>1,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->field('price,sum(number) as num')->group('price')->order('price asc')->limit(50)->select();
		$tentrust['sell_data']=$entrust_m->where(array(
			'type'=>2,
			'xnb'=>$xnb_id,
			'market'=>$market
		))->field('price,sum(number) as num')->limit(50)->group('price')->order('price asc,addtime ')->select();
		$this->ajaxReturn($tentrust);
	}
	//市场走势图
	public function echart()
	{
		$data['currency_xnb.brief'] = $this->strFilter(I('brief')) ? $this->strFilter(I('brief')) : "BTC";
		$marke_data=M('markethouse')->field('name,id')->order('id')->select();   //市场的展示
		$data['currency_transactionrecords.market']=preg_match("/^[1-9][0-9]*$/",I('mark'))>0 ? I('mark') : $marke_data[0]['id'];
		$mark=$data['currency_transactionrecords.market'];
		$path = "./Public/Tradeline";
		$wenjianname = $data['currency_xnb.brief'].$data['currency_transactionrecords.market'];
		$filename = "$path/$wenjianname.text";
		$fps = fopen($filename, "r");
		$strf=fread($fps,filesize($filename));
		$str= json_decode(fread($fps,filesize($filename)),true);
		if($str['extime']>time()){
			$this->ajaxReturn($strf);
		}else{
			if($str['extime']>time()){
				$this->ajaxReturn($strf);
			}else{
				$fp = fopen($filename, "w");
				if (flock($fp, LOCK_EX)) {
					//文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读
					$rest = M('xnb')->field(' 
            currency_xnb.brief as brief,
           currency_transactionrecords.allmoney as allmoney,
           currency_transactionrecords.time as shoptime,
            currency_transactionrecords.number as number,
            currency_transactionrecords.type as type,
           currency_transactionrecords.price as price
        ')->join("LEFT JOIN currency_transactionrecords ON currency_xnb.id=currency_transactionrecords.xnb ")
						->where($data)
						->order('currency_transactionrecords.time desc')
						->limit(20)
						->select();

					$data = array();
					foreach ($rest as $v) {
						$data['day'][] = $v['shoptime'];
						$data['price'][] = $v['price'];
					}
					$sef2 = array();
					$value = array();
					for ($z = 0; $z < count($data['price']); $z++) {
						$sef2[] = date("Y-m-d H:i:s",$data['day'][$z]);
						if ($data['price'][$z] == null) {
							if ($data['price'][$z-1] == null) {
								$data['price'][$z]=0;
							} else {
								$data['price'][$z] = $data['price'][$z-1];
							}
						}
						if($data['price'][$z]<1){
							$data['price'][$z]=floatval($data['price'][$z])*10000;
						}
						if($data['price'][$z]<3){
							$data['price'][$z]=floatval($data['price'][$z])*100;
						}
						if($data['price'][$z]<100){
							$data['price'][$z]=floatval($data['price'][$z])*1000;
						}
						$value[] = $data['price'][$z];
					}

					if ($str['extime'] > time()) {
						$this->ajaxReturn($strf);
					} else {
						$write['day'] = $sef2;
						$write['value'] = $value;
						$write['extime'] = time() + 3600 *2;
						fwrite($fp, json_encode($write));
						fclose($fp);
						$this->ajaxReturn($write);
					}
				}
			}
		}

	}
	
	//传参市场id
	public function markid(){
		$markethouse_m=M('markethouse');
		$marke_data=$markethouse_m->field('name,id')->order('id')->select();   //市场的展示
		$marke_id=preg_match("/^[1-9][0-9]*$/",I('markethouse'))>0 ? I('markethouse') : $marke_data[0]['id'];
		$this->assign('market',$marke_id);
	}
}
