<?php
namespace Wap\Controller;

use Think\Controller;

class PublicController extends WapController {
    
    //登录
    public function login() {
        $this -> display();
    }
    //找回密码
    public function retrievePassword() {
        $this -> display();
    }
    function logins()
    {

        $REG="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|^1(3|4|5|7|8)\d{9}$/";
        $username = I('name');
        $REGold=preg_match($REG,$username);
        if($REGold==1){
            $w['users'] = $username;
        }else{
            $this->error("账号错误");
            exit();
        }
        $password = $this-> strFilter(I('pass'),true,"账号或密码错误");
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
                    //seeion
                    session('user_wap', array('user_name' => $username, 'password' => $password, 'dealpwd' => $dealpwd, 'id' => $id,'agent'=>$rset[0]['rset'], 'expire' => time() + 3600));
                    session('screennames',$rset[0]['screennames']);
                    session('truename_wap',$rset[0]['username']);
                    $why = M('userproperty')->where($wid)->select();
                    $cny=$why[0]['cny'];//用户金额
                    $sid=$rset[0]['id'];//用户id
                    $ww=$rset[0]['users'];//用户名
                    $this->assign('wo',$ww);
                    $this->assign('vo',$sid);
                    $this->assign('cny',$cny);
                    
                    $this->resetnumber();
                    $ere['loginci']=6;
                    if(!$rset[0]['wxuser']){
                        $ere['wxuser']=session("openid");
                    }
                    $model=M('users')->where("id=$id")->save($ere);
                    $this->success('登录成功');
//                    redirect('index.php/Wap/Profile/profile/');
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
                $this->error("您的账户密码错误次数太多已冻结，请去找回密码");
            }

        }
    }
    function lift(){//注销
        $_SESSION = array();    //3、清楚客户端sessionid
        if(isset($_COOKIE['username'])) {   setcookie('username','',time()-3600,'/'); };
        //3、清楚客户端sessionid
        if(isset($_COOKIE['password'])) {   setcookie('password','',time()-3600,'/'); }
        $data=1;
        $this->ajaxReturn($data);
    }
}