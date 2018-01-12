<?php
namespace Wap\Controller;

use Think\Controller;
use Wap\Controller\TradeBuyController;
use Wap\Controller\TradeSellController;

class TradeController extends WapController {

    //交易大厅：交易中心
    public function buy() {
       
        $market_m=M('markethouse');

        //市场列表
        $market_data=$market_m->select();
        $market=I('Market');

        //虚拟币列表
        $market=  $market=="" ? $market_data[0]['id']:$market;  //如果为空就返回第一个交易市场
        $IndexController= new IndexController();
        $xnb_data=$IndexController->getXnbTede($market); //获取虚拟币交易信息
        $this -> assign('market_data',$market_data);
        $this -> assign('marketlength',count($market_data));
        $this -> assign('xnb_data',$xnb_data);
        $this -> assign("Market", $market);
        $where['id']=$market;
        $pd=$market_m->field('standardmoney')->where($where)->find();
        $this -> assign("pd", $pd['standardmoney']);
        $this -> display();
    }

    //交易虚拟币
    public function trade() {
        //交易功能方法的调用！
        if(IS_POST){
            $business_tupe=I('business_tupe');
           
            if ($business_tupe==1 && session('user_wap')['id']!=""){
                $TradeBuyController=  new TradeBuyController();
                $TradeBuyController->trade_buy();
            }elseif ($business_tupe==2  && session('user_wap')['id']!=""){
                $TradeSell=new TradeSellController();
                $TradeSell->trade_buy();
            }else{
                $this->error('请登录!');
            }
            exit();
        }
        //获取市场和虚拟币
        $xnb_id=$this->strFilter(I('xnb'));
        $marke_id=$this->strFilter(I('marke'));
        $uid=session('user_wap.id');

        //判断虚拟币和市场的关系是否合法，如果不合法就跳转到首页
        $markethouse_d= D('markethouse');
        $pd=$markethouse_d->criterionid($marke_id,$xnb_id); //如果合法会返回市场的本位币id

        if ($pd === false){
            $this->redirect('Index/index');
            exit();
        }
        $this->assign("pd",$pd);
        //获取买一卖一价格
        $TradeDataController= new TradeDataController();
        $Trade_data=$TradeDataController->getentrust_s(1, $xnb_id,$marke_id);
        $this->assign('Trade_data',$Trade_data);

        //最新价格
        $transactionrecords=M('transactionrecords');
        $transac_new=$transactionrecords->where(['xnb'=>$xnb_id,'market'=>$marke_id])->field('price')->order('time desc,id desc')->find();
        $this->assign('transac_new',$transac_new['price']);

        //获取虚拟币信息
        $xnb_d= D('xnb');
        $user_xnb=$xnb_d->getstandar($xnb_id);
        $this->assign('xnb_data',$user_xnb);


        //用户的该虚拟币信息
        $userproperty_d=D('userproperty');
            //获取本位币简称
        $xnb_standardmoney=$xnb_d->getstandar($pd);

            //用户可用虚拟币,市场本位币金额
        $user_xnb_us= $userproperty_d->where(['userid'=>session('user_wap.id')])->field($user_xnb['brief'].','.$xnb_standardmoney['brief'])->find();

        $this->assign('standardmoney',['name'=>$xnb_standardmoney['name'],'money'=>$user_xnb_us[$xnb_standardmoney['brief']]]);//本位币简称，和可用的本位币
        $this->assign('property_us',$user_xnb_us[$user_xnb['brief']]);//用户可用的虚拟币金额

            //用户冻结虚拟币
        $entrust_m=M('entrust');
        $property_d=$entrust_m->where(['userid'=>$uid,'xnb'=>$xnb_id])->field('sum(number)')->find();
        $this->assign('property_d',$property_d['sum(number)']);
            //用户可用本位币

        //买卖挂单前5的信息
        $Trade_water=$TradeDataController->getentrust_s(5, $xnb_id,$marke_id);
        $this->assign('Trade_water',$Trade_water);

        //用户挂单信息,用户扯单$uid,$market,$xnb='*'
        $entrust_water=$TradeDataController->getUserEntrust($uid,$marke_id,$xnb_id);
        $this->assign('entrust_water',$entrust_water);
        
        $this -> display();
    }

    //用户撤消的订单的方法
    public function delete_orders (){
        $orderfor=$this->strFilter(I('orderfor'));
        if (check_number($orderfor)!=$orderfor){
            $this->error('非法操作！');
            exit();
        }
        $TradeDataController=  new TradeDataController();
        $TradeDataController->delete_order($orderfor);
        exit();
    }

    //虚拟币行情
    public function market() {
        $xnb=$this->strFilter(I('xnb'));
        $marke=$this->strFilter(I('marke'));
        $TradeDataController=  new TradeDataController();
        //虚拟币详细信息
        $xnb_data=$TradeDataController->getXnbData($xnb,$marke);
        $this->assign('xnb_data',$xnb_data);

        //虚拟币挂单列表$limit,$xnb_id,$marke
        $entrust=$TradeDataController->getXnbEntrust(25,$xnb,$marke);
        $this->assign('entrust',$entrust);

        //成交记录
        $Trade_water=$TradeDataController->getTransaction($xnb, $marke, 25);
        $this->assign('trade_water',$Trade_water);
        $this -> display();
    }

    //交易大厅：编辑要展示的交易币种
    public function editDisplayCurrency() {
        $this -> display();
    }
    
    public function market_Kline() {
        $id['id']=$this->strFilter(I('xnb'))?$this->strFilter(I('xnb')):37;
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('Market');
        $market=  $market=="" ? $market_data[0]['id']:$market;
        $str=M('xnb')->where($id)->select();
        $this->assign("xnb",$str[0]);
        $this->assign("mark",$market);
        $this->assign("id",$id['id']);
    	$this -> display();
    }

    //行情图
    public function echarts(){
        $xnb_id=37;
        $entrust_m=M('entrust');
        $tentrust=array();
        //卖10，买10
        $tentrust['buy_data']=$entrust_m->where(array(
            'type'=>1,
            'xnb'=>$xnb_id
        ))->field('price,sum(number) as num')->group('price')->order('price desc')->limit(50)->select();
        $tentrust['sell_data']=$entrust_m->where(array(
            'type'=>2,
            'xnb'=>$xnb_id
        ))->field('price,sum(number) as num')->limit(50)->group('price')->order('price asc,addtime ')->select();
        $this->ajaxReturn($tentrust);
    }
}