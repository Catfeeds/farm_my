<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use GatewayWorker\Register;
use OT\DataDictionary;
use Think\Cache\Driver\Redis;
use Home\Controller\ShopController;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {
    //登录日志


	//系统首页
    public function index(){

        //查询二维码
        $config = M("config") -> where("type = 5") -> field("value, name") -> select();
        $public_code = "";
        $android_code = "";
        $ios_code = "";
        foreach ($config as $key => $value) {
            switch ($value['name']) {
                case PUBLIC_CODE:$public_code = $value['value'];break;
                case APP_ANDROID_CODE:$android_code = $value['value'];break;
                case APP_IOS_CODE:$ios_code = $value['value'];break;
                default:break;
            }
        }
        //查询
        $information = M("texttype") -> where('toptype = 1 and status = 1') -> field('id, title') -> limit(3) ->  select();
        for ($i = 0; $i < count($information); $i ++) {
            $information[$i]['information'] = M("text") -> where('type = '. $information[$i]['id']. ' and status = 1 and header = 1 and footer = 1') -> field('id, title, endtime') -> order('id desc') -> limit(6) -> select();
        }
        //新手帮助
//        $help = M("text") -> where('type = 6 and status = 1 and header = 1 and footer = 1') -> field('id, title, endtime') -> limit(6) -> select();
        //最新公告
        $new_notice = M("text") -> where("type = 2 and status = 1 and header = 1 and footer = 1") -> field("id, title") -> order("id desc") -> limit(1) -> find();
        $xnb=M('xnb')->where(array('sort'=>1,'status'=>1))->order('id desc')->limit(6)->select();

        $this->assign("xnb",$xnb);
//        $this -> assign("help", $help);
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('mark');
        $market=  $market=="" ? $market_data[0]['id']:$market;

        $shop = new ShopController();
        $list = $shop -> index();
        // for ($i=0; $i < count($list); $i++) { 
        //     $list[$i] = $
        // }
        // dump($list);

        $this -> assign("list", $list);
        $this -> assign("market", $market);
        $this -> assign("new_notice", $new_notice);
        $this -> assign("information", $information);
        $this -> assign("public_code", $public_code);
        $this -> assign("android_code", $android_code);
        $this -> assign("ios_code", $ios_code);
        $this -> display();

    }
    function lift(){//注销
        $_SESSION = array();    //3、清楚客户端sessionid
         if(isset($_COOKIE['username'])) {   setcookie('username','',time()-3600,'/'); };
        //3、清楚客户端sessionid
         if(isset($_COOKIE['password'])) {   setcookie('password','',time()-3600,'/'); }
        $data=1;
        $this->ajaxReturn($data);
    }

    function record(){//记住密码后生成cookie；
        $user['users'] = $_POST['user'];
        $name=$_POST['user'];
        $rest=M('users')->where($user)->select();
        $wid['userid']=$rest[0]['id'];
        $pass = $_POST['pass'];
        if ($rest) {
            setcookie("username", $name, time() + 3600*24 );
            setcookie("password", $pass, time() + 3600 * 24 );
        }
    }

    function login()
    {
        $REG="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|^1(3|4|5|7|8)\d{9}$/";
        $username = I('name');
        $REGold=preg_match($REG,$username);
        $password = $this-> strFilter(I('pass'),true,"密码含有非法字符");
        if($REGold==1){
            $w['users'] = $username;
        }else{
            $this->error("账号错误");
            exit();
        }
        $rset = M("users")->where($w)->select();
        $wid['usreid'] = $rset[0]['id'];
        $id = $rset[0]['id'];
        $dealpwd = $rset[0]['dealpassword'];
        if (!$rset) {
            $this->error('账号密码错误！');
            return false;
        } else if ($rset[0]['password'] == jiami($password)) {
            if ($rset[0]['status'] == -1) {
                $this->error('用户已被删除');
                }else if($rset[0]['status'] == -2 && $rset[0]['loginci'] == 0){
                $this->error("您的账户密码错误次数太多已冻结，请去找回密码");
            } else {
                if ($rset[0]['status'] == 0) {
                    $this->error('用户已被禁用');
                } else {
                    session('user', array('user_name' => $username, 'password' => $password, 'dealpwd' => $dealpwd, 'id' => $id,'agent'=>$rset[0]['rset'], 'expire' => time() + 3600));
                    session('screennames',$rset[0]['screennames']);
                    session('truename',$rset[0]['username']);
                    $why = M('userproperty')->where($wid)->select();
                    $data['id']=$rset[0]['id'];
                    $deaft['loginci']=6;
                    $ssf= M('users')->where($data)->save($deaft);
                    $cny=$why[0]['cny'];//用户金额
                    $sid=$rset[0]['id'];//用户id
                    $ww=$rset[0]['users'];//用户名
                    $this->assign('wo',$ww);
                    $this->assign('vo',$sid);
                    $xnb_int=$this->prop();
                    $this->assign('cny',$xnb_int);
                    $this->resetnumber();
                    $this->success('登录成功');
                }
            }
        } else {
            $logins=$rset[0]['loginci'];
            $idss['id']=$rset[0]['id'];
            $jialogin=$logins-1;
            if($logins>0){
                $resdata['loginci']=$jialogin;
                $shenyu=M('users')->where($idss)->save($resdata);
                $this->error("密码错误！您还有 $jialogin 次机会");
            }else if($logins<=0){
                $resdata['status']=-2;
                $shenyus=M('users')->where($idss)->save($resdata);
                $this->error("您的账户密码错误次数太多已冻结，请去<a href='index.php/Home/Register/findTradePw'>找回密码</a>");
            }

        }
    }

    function kxiantu($value,$mark){
        $data['currency_xnb.brief']=$value;
        $data['currency_transactionrecords.market']=$mark;
        $rest=M('xnb')->field('
            currency_xnb.brief as brief,
            currency_transactionrecords.market as market,
           currency_transactionrecords.allmoney as allmoney,
           currency_transactionrecords.time as shoptime,
           currency_transactionrecords.price as price,
             currency_transactionrecords.number as number,
           currency_markethouse.opentime as opentime,
           currency_markethouse.closetime as closetime
        ')->join("LEFT JOIN currency_transactionrecords ON currency_xnb.id=currency_transactionrecords.xnb")
            ->join("LEFT JOIN currency_markethouse ON currency_transactionrecords.market=currency_markethouse.id")
            ->where($data)
            ->order('currency_transactionrecords.time asc')
            ->select();
        return $rest;
    }
    function echart(){
        $data=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"LKC";
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('mark');
        $market=  $market=="" ? $market_data[0]['id']:$market;
        $path = "./Public/XnbKline";
        $wenjianname = $data.$market;
        $filename = "$path/$wenjianname.text";
        $fps = fopen($filename, "r");
        $strf=fread($fps,filesize($filename));
        $str= json_decode(fread($fps,filesize($filename)),true);
        if($str['extime']>time()){
            $this->ajaxReturn($strf);
        }else{
            if($str['extime']>time()){
                $this->ajaxReturn($strf);
            }else {
                $fp = fopen($filename, "w");     //文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读
                if (flock($fp, LOCK_EX)) {
                    $rest = $this->kxiantu($data,$market);
                    $host = array();
                    $data = array();
                    $time = array();
                    $timea = array();
                    $timeHis = array();
                    foreach ($rest as $v) {
                        $host[] = $v;
                    }
                    for ($i = 0; $i < count($rest); $i++) {
                        $time[] = date("Y-m-d ", $host[$i]['shoptime']);
                        $timeHis[] = date("Y-m-d H:i:s", $host[$i]['shoptime']);
                        $pan = array_unique($time);
                        $pan = array_values($pan);
                        for ($j = 0; $j < count($pan); $j++) {
                            if ($time[$i] == $pan[$j]) {
                                $timea['day'][$j][] = $time[$i];
                                $timea['time'][$j][] = $timeHis[$i];
                                $timea['number'][$j][] = $host[$i]['number'];
                                $timea['price'][$j][] = $host[$i]['price'];
                            }
                        }
                    }

                    for ($tia = 0; $tia < count($timea['time']); $tia++) {
                        for ($a = 0; $a < count($timea['price']); $a++) {
                            $data['max'][] = max($timea['price'][$a]);
                            $data['min'][] = min($timea['price'][$a]);
                        }
                    }
                    $array = array();
                    $Date_1 = date("Y-m-d");
                    $Date_2 = date('Y-m-01', strtotime('-2 month'));
                    $d1 = strtotime($Date_1);
                    $d2 = strtotime($Date_2);
                    $Days = round(($d2 - $d1) / 3600 / 24);
                    if (-$Days < $Days) {
                        $days = $Days;
                    } else {
                        $days = -$Days;
                    }
                    for ($as = 0; $as < count($pan); $as++) {
                        $first = array_keys($timea['time'][$as]);
                        $idsa = array();
                        $idsa['min'][] = min($first);
                        $idsa['max'][] = max($first);
                        $data['open'][] = $timea['price'][$as][$idsa['min'][0]];
                        $data['close'][] = $timea['price'][$as][$idsa['max'][0]];
                        $data['number'][] = array_sum($timea['number'][$as]);
                        for ($qw = 0; $qw < $days; $qw++) {
                            if ($as == $qw) {
                                $array[$qw]['day'][] = $pan[$as];
                                $array[$qw]['open'][] = $data['open'][$qw];
                                $array[$qw]['close'][] = $data['close'][$qw];
                                $array[$qw]['max'][] = $data['max'][$qw];
                                $array[$qw]['min'][] = $data['min'][$qw];
                                $array[$qw]['number'][] = $data['number'][$qw];
                            }
                        }
                    }
                    $sef=array();
                    $zong=array();
                    $maxval=array();
                    $minval=array();
                    for($i=0;$i<count($array);$i++){
                        //循环赋值
                        $data0=$this->splitData([
                            [$array[$i]['day'],$array[$i]['open'],$array[$i]['close'],$array[$i]['min'],$array[$i]['max']]
                        ]);
                        $sef[]=$data0;
                        $zong[]=$array[$i]['number'];
//            var_dump(max($array[$i]['max']));
                        if( max($array[$i]['max']) && max($array[$i]['min'])){

                            $maxval[]=max($array[$i]['max']);
                            $minval[]=min($array[$i]['min']);
                        }
                    }
                    $maxnum=0;
                    for($ls=0;$ls<count($maxval);$ls++){
                        if($maxval[$ls]>$maxnum){
                            $maxnum=$maxval[$ls];
                        }
                    }
                    $maxmin=1;
                    for($lsa=0;$lsa<count($minval);$lsa++){

                        if($minval[$lsa+1]<$minval[$lsa] && $minval[$lsa+1]!=""){
                            $maxmin=$minval[$lsa+1];
                        }
                        if ($maxmin ==1) {
                            if ($minval[$lsa] < $minval[$lsa + 1] && $minval[$lsa + 1] != "") {
                                $maxmin = $minval[$lsa];
                            }
                        }
                        if($maxmin ==1 && count($minval)==1){
                            $maxmin=min($minval);
                        }
                    }
                    $day=array();
                    $value=array();
                    for($j=0;$j<count($sef);$j++){
                        //day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
                        $day[]=$sef[$j]['categoryData'];
                        $value[]=$sef[$j]['values'][0];
                    }
                    $mmax=array();
                    for($kk=0;$kk<count($value);$kk++){
                        $mmax[]=$value[$kk][3];
                    }
                    $sssa=max($mmax);
                    $saq=round(floatval($sssa)/5);
                    $jack=round(floatval($sssa)/5,3);
                    $sssmax=floatval($jack)*5+floatval($jack);
                    $write['day'] = $day;
                    $write['value'] = $value;
                    $write['sssmax'] = $sssmax;
                    $write['maxnum'] = $maxnum;
                    $write['minnum'] = $maxmin;
                    $write['jack'] = $jack;
                    $write['extime'] = time()+3600*6;
                    fwrite($fp, json_encode($write));
                    fclose($fp);
                    $this->ajaxReturn($write);
                }
            }
        }
    }
    function echartfmin(){
        $data=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"BTC";
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('mark');
        $market=  $market=="" ? $market_data[0]['id']:$market;
       
        $minter=$this->strFilter(I('xian'));
        $min=$minter/60;
        $redis=new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $name=$data.$min.$market."min";
        $str=$redis->get($name);
        if($str){
            $stfs=json_decode($str,true);
            $this->ajaxReturn($stfs);
        }else {
            $str = $redis->get($name);
            if ($str) {
                $stfs = json_decode($str, true);
                $this->ajaxReturn($stfs);
            } else {
                $rest = $this->kxiantu($data,$market);
                $host = array();
                $time = array();
                $timeHis = array();
                $timea = array();
                foreach ($rest as $v) {
                    $host[] = $v;
                }

                for ($i = 0; $i < count($rest); $i++) {
                    $time[] = date("Y-m-d ", $host[$i]['shoptime']);
                    $timeHis[] = $host[$i]['shoptime'];
                }

                $Date_2 = date('Y-m-d ', strtotime('-2 day'));
                $timestamp0 = strtotime($Date_2);
                $nowtime = time();
                $chatime = round(($nowtime - $timestamp0) / $minter);
                $suantime = $timestamp0;
                $timearray = array();
                for ($k = 0; $k < $chatime; $k++) {
                    $suantime = $suantime += $minter;
                    $timearray[$k][] = date('Y-m-d H:i', $suantime);
                }

                $timeqiu = array();
                for ($i = 0; $i < count($timearray); $i++) {
                    for ($qiutime = 0; $qiutime < count($timeHis); $qiutime++) {

                        if ($timeHis[$qiutime] > $timestamp0 && $timeHis[$qiutime] < $timearray[$i]) {
                            $timeqiu[] = $timeHis[$qiutime];
                            $now = date("Y-m-d H:i", $timeHis[$qiutime]);
                            if ($now >= $timearray[$i][0] && $now < $timearray[$i + 1][0]) {
                                $timearray[$i]['day'][] = $timeHis[$qiutime];
                                $timearray[$i]['price'][] = $host[$qiutime]['price'];
                                $timearray[$i]['num'][] = $host[$qiutime]['number'];
                                $timea[$i]['number'][] = $host[$qiutime]['number'];
                               
                            }
                        }
                    }
					 for ($money = 0; $money < count($timearray[$i]['price']); $money++) {
                                    $firset = array_keys($timearray[$i]['price']);
                                    $timearray[$i]['open'] = $timearray[$i]['price'][min($firset)];
                                    $timearray[$i]['close'] = $timearray[$i]['price'][max($firset)];
                                    $timearray[$i]['max'] = max($timearray[$i]['price']);
                                    $timearray[$i]['min'] = min($timearray[$i]['price']);
                                    $timearray[$i]['number'] = array_sum($timea[$i]['number']);
                                }
                }
                $sef = array();
                $zong = array();
                $maxval = array();
                $minval = array();
                for ($i = 0; $i < count($timearray); $i++) {
                    //循环赋值
                    $data0 = $this->splitData([
                        [$timearray[$i][0], $timearray[$i]['open'], $timearray[$i]['close'], $timearray[$i]['min'], $timearray[$i]['max']]
                    ]);
                    $sef[] = $data0;
                    $zong[] = $timearray[$i]['number'];
//            var_dump(max($array[$i]['max']));
                    if (max($timearray[$i]['max']) && max($timearray[$i]['min'])) {

                        $maxval[] = max($timearray[$i]['max']);
                        $minval[] = min($timearray[$i]['min']);
                    }
                }

                $maxnum = 0;
                for ($ls = 0; $ls < count($maxval); $ls++) {
                    if ($maxval[$ls] > $maxnum) {
                        $maxnum = $maxval[$ls];
                    }
                }
                $maxmin = 0;
                for ($lsa = 0; $lsa < count($minval); $lsa++) {
                    if ($minval[$lsa] < $minval[$lsa + 1] && $minval[$lsa + 1] != "") {
                        $maxmin = $minval[$lsa];
                    }
                }
                $day = array();
                $value = array();
                for ($j = 0; $j < count($sef); $j++) {
                    //day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
                    $day[] = $sef[$j]['categoryData'];
                    $value[] = $sef[$j]['values'][0];
                }
                $mmax = array();
                for ($kk = 0; $kk < count($value); $kk++) {
                    if ($value[$kk][3] == null) {
                        $mmax[] = 0;
                    } else {

                        $mmax[] = $value[$kk][3];
                    }

                }
                $sssa = max($mmax);
                $saq = floatval($sssa) / 5;
                $jack = floatval($saq) - floatval($saq) % 10;
               if($jack<1){
                    $sssmax=floatval($saq)*5+floatval($saq);
                }else{
                    $sssmax=floatval($jack)*5+floatval($jack);
                }
                $write['day'] = $day;
                $write['value'] = $value;
                $write['sssmax'] = $sssmax;
                $write['maxnum'] = $maxnum;
                $write['minnum'] = $maxmin;
                $write['jack'] = $jack;

                $write['extime'] = time() + 3600 * 24;
                $str=$redis->get($name);
                if($str){
                    $stfs=json_decode($str,true);
                    $this->ajaxReturn($stfs);
                }else{
                    $redis->SETEX($name, 1800, json_encode($write));
                    $this->ajaxReturn($write);
                }
            }
        }
    }
    function echarttmin(){
        $data=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"BTC";
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('mark');
        $market=  $market=="" ? $market_data[0]['id']:$market;
        $minter=$this->strFilter(I('xian'));
        $redis=new \Redis();
        $redis->connect('127.0.0.1', 6379);
//        $redis->set($data, "asfasf");
        $name=$data.$market."30min";
        $str=$redis->get($name);
        if($str){
            $stfs=json_decode($str,true);
            $this->ajaxReturn($stfs);
        }else {
            $str = $redis->get($name);
            if ($str) {
                $stfs = json_decode($str, true);
                $this->ajaxReturn($stfs);
            } else {
                $rest = $this->kxiantu($data,$market);
                $host = array();
                $time = array();
                $timeHis = array();
                $timea = array();
                foreach ($rest as $v) {
                    $host[] = $v;
                }

                for ($i = 0; $i < count($rest); $i++) {
                    $time[] = date("Y-m-d ", $host[$i]['shoptime']);
                    $timeHis[] = $host[$i]['shoptime'];
                }

                $Date_2 = date('Y-m-d ', strtotime('-2 day'));
                $timestamp0 = strtotime($Date_2);
                $nowtime = time();
                $chatime = round(($nowtime - $timestamp0) / $minter);
                $suantime = $timestamp0;
                $timearray = array();
                for ($k = 0; $k < $chatime; $k++) {
                    $suantime = $suantime += $minter;
                    $timearray[$k][] = date('Y-m-d H:i', $suantime);
                }

                $timeqiu = array();
                for ($i = 0; $i < count($timearray); $i++) {
                    for ($qiutime = 0; $qiutime < count($timeHis); $qiutime++) {

                        if ($timeHis[$qiutime] > $timestamp0 && $timeHis[$qiutime] < $timearray[$i]) {
                            $timeqiu[] = $timeHis[$qiutime];
                            $now = date("Y-m-d H:i", $timeHis[$qiutime]);
                            if ($now >= $timearray[$i][0] && $now < $timearray[$i + 1][0]) {
                                $timearray[$i]['day'][] = $timeHis[$qiutime];
                                $timearray[$i]['price'][] = $host[$qiutime]['price'];
                                $timearray[$i]['num'][] = $host[$qiutime]['number'];
                                $timea[$i]['number'][] = $host[$qiutime]['number'];
                               
                            }
                        }
                    }
					 for ($money = 0; $money < count($timearray[$i]['price']); $money++) {
                                    $firset = array_keys($timearray[$i]['price']);
                                    $timearray[$i]['open'] = $timearray[$i]['price'][min($firset)];
                                    $timearray[$i]['close'] = $timearray[$i]['price'][max($firset)];
                                    $timearray[$i]['max'] = max($timearray[$i]['price']);
                                    $timearray[$i]['min'] = min($timearray[$i]['price']);
                                    $timearray[$i]['number'] = array_sum($timea[$i]['number']);
                                }
                }
                $sef = array();
                $zong = array();
                $maxval = array();
                $minval = array();
                for ($i = 0; $i < count($timearray); $i++) {
                    //循环赋值
                    $data0 = $this->splitData([
                        [$timearray[$i][0], $timearray[$i]['open'], $timearray[$i]['close'], $timearray[$i]['min'], $timearray[$i]['max']]
                    ]);
                    $sef[] = $data0;
                    $zong[] = $timearray[$i]['number'];
//            var_dump(max($array[$i]['max']));
                    if (max($timearray[$i]['max']) && max($timearray[$i]['min'])) {

                        $maxval[] = max($timearray[$i]['max']);
                        $minval[] = min($timearray[$i]['min']);
                    }
                }

                $maxnum = 0;
                for ($ls = 0; $ls < count($maxval); $ls++) {
                    if ($maxval[$ls] > $maxnum) {
                        $maxnum = $maxval[$ls];
                    }
                }
                $maxmin = 0;
                for ($lsa = 0; $lsa < count($minval); $lsa++) {
                    if ($minval[$lsa] < $minval[$lsa + 1] && $minval[$lsa + 1] != "") {
                        $maxmin = $minval[$lsa];
                    }
                }
                $day = array();
                $value = array();
                for ($j = 0; $j < count($sef); $j++) {
                    //day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
                    $day[] = $sef[$j]['categoryData'];
                    $value[] = $sef[$j]['values'][0];
                }
                $mmax = array();
                for ($kk = 0; $kk < count($value); $kk++) {
                    if ($value[$kk][3] == null) {
                        $mmax[] = 0;
                    } else {

                        $mmax[] = $value[$kk][3];
                    }

                }
                $sssa = max($mmax);
                $saq = floatval($sssa) / 5;
                $jack = floatval($saq) - floatval($saq) % 10;
                if($jack<1){
                    $sssmax=floatval($saq)*5+floatval($saq);
                }else{
                    $sssmax=floatval($jack)*5+floatval($jack);
                }
                $write['day'] = $day;
                $write['value'] = $value;
                $write['sssmax'] = $sssmax;
                $write['maxnum'] = $maxnum;
                $write['minnum'] = $maxmin;
                $write['jack'] = $jack;
                $write['extime'] = time() + 3600 * 24;
                $str=$redis->get($name);
                if($str){
                    $stfs=json_decode($str,true);
                    $this->ajaxReturn($stfs);
                }else{
                    $redis->SETEX($name, 1800, json_encode($write));
                    $this->ajaxReturn($write);
                }
            }
        }
    }
    function onehero(){
        $data=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"BTC";
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('mark');
        $market=  $market=="" ? $market_data[0]['id']:$market;
        $minter=$this->strFilter(I('xian'));
        $path = "./Public/XnbOne";
        $wenjianname = $data.$market;
        $filename = "$path/$wenjianname.text";
        $fps = fopen($filename, "r");
        $strf=fread($fps,filesize($filename));
        $str= json_decode(fread($fps,filesize($filename)),true);
        if($str['extime']>time()){
            $this->ajaxReturn($strf);
        }else{
            if($str['extime']>time()){
                $this->ajaxReturn($strf);
            }else {
                $fp = fopen($filename, "w");     //文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读
                if (flock($fp, LOCK_EX)) {
                    $rest = $this->kxiantu($data,$market);
                    $host=array();
                    $time=array();
                    $timeHis=array();
                    $timea=array();
                    foreach($rest as $v){
                        $host[]=$v;
                    }

                    for($i=0;$i<count($rest);$i++){
                        $time[]=date("Y-m-d ",$host[$i]['shoptime']);
                        $timeHis[]=$host[$i]['shoptime'];
                    }

                    $Date_2 = date('Y-m-d ', strtotime('-5 day'));
                    $timestamp0 = strtotime($Date_2);
                    $nowtime=time();
                    $chatime=round(($nowtime-$timestamp0)/$minter);
                    $suantime=$timestamp0;
                    $timearray=array();
                    for ($k=0;$k<$chatime;$k++){
                        $suantime=$suantime+=$minter;
                        $timearray[$k][]=date('Y-m-d H:i', $suantime);
                    }
                    $timeqiu=array();
                    for ($i = 0; $i < count($timearray); $i++) {
                        for($qiutime=0;$qiutime<count($timeHis);$qiutime++) {

                            if ($timeHis[$qiutime]>$timestamp0 && $timeHis[$qiutime] <$timearray[$i]) {
                                $timeqiu[] = $timeHis[$qiutime];
                                $now=date("Y-m-d H:i", $timeHis[$qiutime]);
                                if( $now>=$timearray[$i][0] && $now< $timearray[$i+1][0]){
                                    $timearray[$i]['day'][]=$timeHis[$qiutime];
                                    $timearray[$i]['price'][]=$host[$qiutime]['price'];
                                    $timearray[$i]['num'][]=$host[$qiutime]['number'];
                                    $timea[$i]['number'][]=$host[$qiutime]['number'];
                                    for($money=0;$money<count($timearray[$i]['price']);$money++){
                                        $firset=array_keys($timearray[$i]['price']);
                                        $timearray[$i]['open']=$timearray[$i]['price'][min($firset)];
                                        $timearray[$i]['close']=$timearray[$i]['price'][max($firset)];
                                        $timearray[$i]['max']=max($timearray[$i]['price']);
                                        $timearray[$i]['min']=min($timearray[$i]['price']);
                                        $timearray[$i]['number']=array_sum( $timea[$i]['number']);
                                    }
                                }
                            }
                        }
                    }
                    $sef=array();
                    $zong=array();
                    $maxval=array();
                    $minval=array();
                    for($i=0;$i<count($timearray);$i++){
                        //循环赋值
                        $data0=$this->splitData([
                            [$timearray[$i][0],$timearray[$i]['open'],$timearray[$i]['close'],$timearray[$i]['min'],$timearray[$i]['max']]
                        ]);
                        $sef[]=$data0;
                        $zong[]=$timearray[$i]['number'];
//            var_dump(max($array[$i]['max']));
                        if( max($timearray[$i]['max']) && max($timearray[$i]['min'])){

                            $maxval[]=max($timearray[$i]['max']);
                            $minval[]=min($timearray[$i]['min']);
                        }
                    }

                    $maxnum=0;
                    for($ls=0;$ls<count($maxval);$ls++){
                        if($maxval[$ls]>$maxnum){
                            $maxnum=$maxval[$ls];
                        }
                    }
                    $maxmin=0;
                    for($lsa=0;$lsa<count($minval);$lsa++){
                        if($minval[$lsa]<$minval[$lsa+1] && $minval[$lsa+1]!=""){
                            $maxmin=$minval[$lsa];
                        }
                    }
                    $day=array();
                    $value=array();
                    for($j=0;$j<count($sef);$j++){
                        //day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
                        $day[]=$sef[$j]['categoryData'];
                        $value[]=$sef[$j]['values'][0];
                    }
                    $mmax=array();
                    for($kk=0;$kk<count($value);$kk++){
                        if($value[$kk][3]==null){
                            $mmax[]=0;
                        }else{

                            $mmax[]=$value[$kk][3];
                        }

                    }
                    $sssa=max($mmax);
                    $saq=round(floatval($sssa)/5);
                    $jack=round(floatval($sssa)/5,3);
                    $sssmax=floatval($jack)*5+floatval($jack);
                    $write['day'] = $day;
                    $write['value'] = $value;
                    $write['sssmax'] = $sssmax;
                    $write['maxnum'] = $maxnum;
                    $write['minnum'] = $maxmin;
                    $write['jack'] = $jack;
                    $write['extime'] = time()+3600*3;
                    fwrite($fp, json_encode($write));
                    fclose($fp);
                    $this->ajaxReturn($write);
                }
            }
        }
    }
    function eighero(){
        $data=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"BTC";
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('mark');
        $market=  $market=="" ? $market_data[0]['id']:$market;
        $minter=$this->strFilter(I('xian'));
        $path = "./Public/XnbEight";
        $wenjianname = $data.$market;
        $filename = "$path/$wenjianname.text";
        $fps = fopen($filename, "r");
        $strf=fread($fps,filesize($filename));
        $str= json_decode(fread($fps,filesize($filename)),true);

        if($str['extime']>time()){
            $this->ajaxReturn($strf);
        }else{

            if($str['extime']>time()){
                $this->ajaxReturn($strf);
            }else {

                $fp = fopen($filename, "w");     //文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读

                if (flock($fp, LOCK_EX)) {
                    $rest = $this->kxiantu($data,$market);
                    $host=array();
                    $time=array();
                    $timeHis=array();
                    $timea=array();
                    foreach($rest as $v){
                        $host[]=$v;
                    }

                    for($i=0;$i<count($rest);$i++){
                        $time[]=date("Y-m-d ",$host[$i]['shoptime']);
                        $timeHis[]=$host[$i]['shoptime'];
                    }

                    $Date_2 = date('Y-m-d ', strtotime('-1 month'));
                    $timestamp0 = strtotime($Date_2);
                    $nowtime=time();
                    $chatime=round(($nowtime-$timestamp0)/$minter);
                    $suantime=$timestamp0;
                    $timearray=array();
                    for ($k=0;$k<$chatime;$k++){
                        $suantime=$suantime+=$minter;
                        $timearray[$k][]=date('Y-m-d H:i', $suantime);
                    }

                    $timeqiu=array();
                    for ($i = 0; $i < count($timearray); $i++) {
                        for($qiutime=0;$qiutime<count($timeHis);$qiutime++) {

                            if ($timeHis[$qiutime]>$timestamp0 && $timeHis[$qiutime] <$timearray[$i]) {
                                $timeqiu[] = $timeHis[$qiutime];
                                $now=date("Y-m-d H:i", $timeHis[$qiutime]);
                                if( $now>=$timearray[$i][0] && $now< $timearray[$i+1][0]){
                                    $timearray[$i]['day'][]=$timeHis[$qiutime];
                                    $timearray[$i]['price'][]=$host[$qiutime]['price'];
                                    $timearray[$i]['num'][]=$host[$qiutime]['number'];
                                    $timea[$i]['number'][]=$host[$qiutime]['number'];
                                  
                                }
                            }
                        }
                        for($money=0;$money<count($timearray[$i]['price']);$money++){
                            $firset=array_keys($timearray[$i]['price']);
                            $timearray[$i]['open']=$timearray[$i]['price'][min($firset)];
                            $timearray[$i]['close']=$timearray[$i]['price'][max($firset)];
                            $timearray[$i]['max']=max($timearray[$i]['price']);
                            $timearray[$i]['min']=min($timearray[$i]['price']);
                            $timearray[$i]['number']=array_sum( $timea[$i]['number']);
                        }
                    }
                   
                    $sef=array();
                    $zong=array();
                    $maxval=array();
                    $minval=array();
                    for($i=0;$i<count($timearray);$i++){
                        //循环赋值
                        $data0=$this->splitData([
                            [$timearray[$i][0],$timearray[$i]['open'],$timearray[$i]['close'],$timearray[$i]['min'],$timearray[$i]['max']]
                        ]);
                        $sef[]=$data0;
                        $zong[]=$timearray[$i]['number'];
                        if( max($timearray[$i]['max']) && max($timearray[$i]['min'])){

                            $maxval[]=max($timearray[$i]['max']);
                            $minval[]=min($timearray[$i]['min']);
                        }
                    }

                    $maxnum=0;
                    for($ls=0;$ls<count($maxval);$ls++){
                        if($maxval[$ls]>$maxnum){
                            $maxnum=$maxval[$ls];
                        }
                    }
                    $maxmin=0;
                    for($lsa=0;$lsa<count($minval);$lsa++){
                        if($minval[$lsa]<$minval[$lsa+1] && $minval[$lsa+1]!=""){
                            $maxmin=$minval[$lsa];
                        }
                    }
                    $day=array();
                    $value=array();
                    for($j=0;$j<count($sef);$j++){
                        //day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
                        $day[]=$sef[$j]['categoryData'];
                       
                        $value[]=$sef[$j]['values'][0];
                    }
                    $mmax=array();
                    for($kk=0;$kk<count($value);$kk++){
                        if($value[$kk][3]==null){
                            $mmax[]=0;
                        }else{

                            $mmax[]=$value[$kk][3];
                        }

                    }

                    $sssa=max($mmax);
                    $saq=round(floatval($sssa)/5);
                    $jack=round(floatval($sssa)/5,3);
                    $sssmax=floatval($jack)*5+floatval($jack);
                    $write['day'] = $day;
                    $write['value'] = $value;
                    $write['sssmax'] = $sssmax;
                    $write['maxnum'] = $maxnum;
                    $write['minnum'] = $maxmin;
                    $write['jack'] = $jack;
                    $write['extime'] = time()+3600*4;

                    fwrite($fp, json_encode($write));
                    fclose($fp);
                    $this->ajaxReturn($write);
                }
            }
        }
    }
    function newdol(){
        $data['brief']=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"BTC";
        $market_m=M('markethouse');
        $market_data=$market_m->select();
        $market=I('mark');
        $where['market']=  $market=="" ? $market_data[0]['id']:$market;
        $rests=M('xnb')->field('
            brief,
            id
        ')
            ->where($data)->find();
        $where['xnb']=$rests['id'];
        $rest=M('transactionrecords')->field('
           market,
           allmoney,
           time as shoptime,
           price
        ')->where($where)->order('time desc')->limit(1)->find();
        if($rest['price']==""){
            $rest['price']=0.0000;
        }
//        var_dump($rest);
//        var_dump(55);
//        exit();
        $this->ajaxReturn($rest);
    }
    function increase(){
        $data['currency_xnb.brief']=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"BTC";
        $data['currency_transactionrecords.time']=array('lt',strtotime(date('Y-m-d',time())));
        $rest=M('xnb')->field('
            currency_xnb.brief as brief,
            currency_transactionrecords.market as market,
           currency_transactionrecords.allmoney as allmoney,
           currency_transactionrecords.time as shoptime,
           currency_transactionrecords.price as price,
         max(currency_transactionrecords.time) as maxtime
        ')->join("LEFT JOIN currency_transactionrecords ON currency_xnb.id=currency_transactionrecords.xnb")
            ->where($data)->select();
        $date['currency_xnb.brief']=$this->strFilter(I('brief'))?$this->strFilter(I('brief')):"BTB";
        $date['currency_transactionrecords.time']=array('egt',strtotime(date('Y-m-d',time())));
        $rests=M('xnb')->field('
            currency_xnb.brief as brief,
            currency_transactionrecords.market as market,
           currency_transactionrecords.allmoney as allmoney,
           currency_transactionrecords.time as shoptime,
           currency_transactionrecords.price as price,
         max(currency_transactionrecords.time) as maxtime
        ')->join("LEFT JOIN currency_transactionrecords ON currency_xnb.id=currency_transactionrecords.xnb")
            ->where($date)->select();
        $zhang=($rests[0]['allmoney']-$rest[0]['allmoney'])/$rest[0]['allmoney'];
        $this->ajaxReturn($zhang);
    }
    function splitData($rawData) {
        $categoryData = array();
        $values =array();
        $retuun=array();
        for ($i = 0; $i < count($rawData); $i++) {
            $categoryData[]=array_splice($rawData[$i],0,1)[0];
            for($j=0;$j<count($rawData[$i]);$j++){
                if($rawData[$i][$j]==null){
                    $rawData[$i][$j]= "-";
                }else if($rawData[$i][$j]!=null && !is_array($rawData[$i][$j])){
                    $rawData[$i][$j]=round($rawData[$i][$j],3);
                } else if($rawData[$i][$j][0]!=null && is_array($rawData[$i][$j])){
                    $rawData[$i][$j]=round($rawData[$i][$j][0],3);
                }
            }
            $values[]=($rawData[$i]);
           
        }

        $retuun['categoryData']= $categoryData;
       
        $retuun['values']= $values;
        return $retuun;
    }
    //保存聊天名字
    public function screennames(){
        if (session('user')['id']!=""){
            $screennames=$this->strFilter(I('name'),true,'非法字符！聊天室名字由字母和汉字组成！');
            $back=M('users')->where(['id'=>session('user')['id']])->save(['screennames'=>$screennames]);
            if ($back==false){
                $this->error('保存失败！请联系我们');
                exit();
            }
            session('screennames',$screennames);
            $this->success('保存成功！');
            exit();
        }
    }
}