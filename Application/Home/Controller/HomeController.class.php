<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

    public function _invit(){  //  获取邀请码
        $data = I("invit");
//        var_dump($data);
        if($data != ''){
            session('invit',$data);
        }
    }

    public function __construct(){
        parent::__construct();
        $this->_invit();
        $this->cookie();
        $this->redata();

    }
    
	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}


    protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
    }

	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}
//防止非法字符输入
    protected function strFilter($str,$type=false,$error="含有非法字符请重输"){
        if($type){
            if($str==""){
                $this->error($error);
            }
        }
        $reg=" /\ |\￥|\……|\、|\‘|\’|\；|\：|\【|\】|\（|\）|\！|\·|\-|\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        //允许通过的特殊字符   。，《 》 “ ”
        $REGold=preg_match($reg,$str);
        if($REGold==1){
            $this->error($error);
        }else{
            return $str;
        }
    }
    protected function strusername($str,$type=false,$error="含有非法字符请重输"){
        if($type){
            if($str==""){
                $this->error($error);
            }
        }
        $reg=" /\ |\￥|\……|\、|\‘|\’|\；|\：|\【|\】|\（|\）|\！|\·|\-|\/|\~|\!|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\/|\;|\'|\`|\-|\=|\\\|\|/";
        //允许通过的特殊字符   。，《 》 “ ”
        $REGold=preg_match($reg,$str);
        if($REGold==1){
            $this->error($error);
        }else{
            return $str;
        }
    }
    //
    protected function redata(){
        $this->assign("session",count(session('user')));
        $data=session('user.user_name');
        $this->assign('id',$data);
    }
    //
    function resetnumber(){
        $REG="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|^1(3|4|5|7|8)\d{9}$/";

        $loginusername = I('name');
        $REGold=preg_match($REG,$loginusername);
        if($REGold==1){
            $w['users'] = $loginusername;
        }else{
            $this->error("账号错误");
        }

        $rsetlogin = M("users")->where($w)->select();
        $loginci['loginci']=3;
        $loginciid['id']=$rsetlogin[0]['id'];
        $reslogin=M('users')->where($loginciid)->save($loginci);
    }
    function Verified(){
        $model=M('users');
        $id['id']=session('user.id');
        $verified=$model->field('yanze,phone')->where($id)->select();
        if($verified[0]['yanze']==0 && $verified[0]['phone']=="" ){
            $verirest['wenzi']="手机号未绑定";
            return $verirest;
        }else if($verified[0]['yanze']==1 && $verified[0]['phone']!=""){
            $verirest['wenzi']="双重验证有效";
            return $verirest;
        }else if($verified[0]['yanze']==-1 && $verified[0]['phone']!=""){
            $verirest['wenzi']="请实名验证";
            return $verirest;
        }
    }
    //首页登陆过期
    function cookie(){
        if(session("user")==null){
            return false;
        }
        if(session('user.expire')>time()) { //检查一下session生存时间
            $id['userid']=session('user.id');
            $userid['id']=session('user.id');
            $why=M('userproperty')->where($id)->select();
            $bank=M('users')->where($userid)->select();
            $cny=$why[0]['cny'];
            if($cny==0){
                $money='0.00';
            }else{
                $money=$why[0]['cny'];
            }
            $sid=$id['userid'];
            $ww=$bank[0]['users'];
            $this->assign('wo',$ww);
            $this->assign('vo',$sid);
            $xnb_int=$this->prop();
            $this->assign('cny',$xnb_int);
            $this->text();
        }else{
            if (!empty($_COOKIE['username']) && !empty($_COOKIE['password'])) {
                $username=$_COOKIE['username'];
                $password=$_COOKIE['password'];
                session("user",null);
                $this->assign('username',$username);
                $this->assign('password',$password);
                $this->assign('session','');
                $this->assign('login',"您的账号登陆过期，请重新登陆");
                $this->text();
            }else{
                session("user",null);
                $this->assign('session','');
                $this->assign('login',"您的账号登陆过期，请重新登陆");
                $this->text();
            }
        }
    }
    public  function text(){
        //用户登录IP；
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        $ww['addip']=$onlineip;
        //用户信息
        $id=session('user.id');
        $userid['id']=session('user.id');
        $bank=M('users')->where($userid)->select();
        $ww['username']=$bank[0]['users'];
        $ww['userid']=$id['userid'];
        $ww['addtime']=time();
        M('loginsdaily')->add($ww);
    }
    public function prop(){
        $markethouse_m=M('markethouse');//市场
        $userproperty_m=M('userproperty');
        $xnb_m=M('xnb');
        $entrust_m=M('entrust');

        $xnb_data_sql=$xnb_m       //获取所有虚拟币
        ->field('currency_xnb.id as xnb_id,currency_xnb.brief as xnb_brief,currency_xnb.name as xnb_name,currency_markethouse.name as marke,currency_xnb.imgurl,currency_transactionrecords.price')
            ->join('left join currency_markethouse on  currency_xnb.id=currency_markethouse.standardmoney')
            ->join('left join currency_transactionrecords on currency_xnb.id=currency_transactionrecords.xnb and currency_transactionrecords.standardmoney=1')
            ->order('currency_transactionrecords.time desc')
            ->select(false);
        $xnb_data=$xnb_m->table($xnb_data_sql.'a')->group('xnb_id')->select();

        $property=$userproperty_m->where(['userid'=>session('user')['id']])->field()->find();   //获取用户的所有资产！
        $xnb_data['allpropertys']=0;


        //匹配该本位币的用户可用资产
        $xnb_data['allpropertys']=0;
        foreach ( $xnb_data as $k=>&$v){
            $v['property']=$property[$v['xnb_brief']];   //用户可用的资产

            if ($v['xnb_id']!=1) {  //人民币不考虑卖单
                //卖单的冻结
                $entrust_buy_data = $entrust_m->where(['userid' => session('user')['id'], 'xnb' => $v['xnb_id'], 'type' => 2])->field('sum(number)')->group('type')->find();
                if ($entrust_buy_data != "") {
                    $v['property_clos'] += $entrust_buy_data['sum(number)'];
                }
            }
            //虚拟币作为本位币的交易冻结
            $entrust_sell_data=$entrust_m->where(['userid'=>session('user')['id'],'standardmoney'=>$v['xnb_id'],'type'=>1])->field('sum(allmoney)')->group('type')->find();

            if ($entrust_sell_data!=""){
                $v['property_clos']+=$entrust_sell_data['sum(allmoney)'];
            }
            $v['price']=$v['price']?$v['price']:0;

            if ($v['xnb_id']!=1){  //人人民币不考虑折合

                #锁定资产
                $v['memory'] = M('memory')->where([
                    'user_id'=>session('user')['id'],
                    'xnb_id'=>$v['xnb_id']
                ])->sum('balance');

                // 计算折合
                if ($v['price']!=""){
                    $v['allproperty']+=($v['property']+$v['property_clos']+$v['memory'])*$v['price'];
                }else{

                    //与虚拟币本位币转换
                    $transactionrecords_m = M('transactionrecords');
                    //与虚拟币本位币最新成交价
                    $b_price=$transactionrecords_m->where(['xnb'=>$v['xnb_id']])->field('price,standardmoney')->order('time desc')->find();
                    //虚拟币本位币与人民币最新成交价
                    $b_price_r=$transactionrecords_m->where(['xnb'=>$b_price['standardmoney'],'standardmoney'=>1])->field('price')->order('time desc')->find();
                    $v['allproperty']+= ($v['property']+$v['property_clos'])*$b_price['price']*$b_price_r['price'];
                }
            }else{
                $v['allproperty']=$v['property']+$v['property_clos'];
            }

            $xnb_data['allpropertys']+= $v['allproperty'];

        }
        return $xnb_data;
    }
}
