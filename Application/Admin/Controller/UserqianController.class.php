<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;
use Think\Page;
/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class UserqianController extends AdminController {
    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('users'); // 实例化Data数据对象  date 是你的表名
        $rest=$Data->field('id')->where("status=-1")->delete();
//        if (IS_POST){
//
//        }
        $name=$this-> strFilter(I('name'))?$this-> strFilter(I('name')):"";
        $where['currency_users.id']=array('like',"%".$name."%");;
        $where['currency_users.users'] =array('like',"%".$name."%");
        $where['_logic'] = "OR";
        $map['_complex'] = $where;
        $map['currency_users.status']=array("gt",-1);
        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name));// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)->field('currency_users.*,prent.users as prents')->join('left join currency_users as prent on currency_users.pid = prent.id')->order('currency_users.addtime DESC')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('_list', $list);
        $this->assign('_page',$show);
        $this->meta_title = '客户信息';
        $this->display();
    }
    /**
     * 前台用户注册
     *
     */
    function zhuce(){
        $data=array();
        $name=$this-> strFilter(I('username'),true);
        $data['users']=$name;
        $mima=$this-> strFilter(I('password'),true);
        $rmima=$this-> strFilter(I('repassword'),true);
        $data['password']=jiami($mima);
        $data['addtime']=time();
        $data['email']=$this-> strFilter(I('email'),true);
        $where['users']=I('username');
        $model=M('users');
        $Inquire=$model->where($where)->select();
        if ($Inquire){
            $this->error("账号已存在");
        }else{
            if($rmima==$mima){
                $result=$model->add($data);
                $id['id']=$result;
                $detail = $model->where($id)->select();
                $wallet=M('userproperty');
                $addone['userid']=$result;
                $addone['username']=$detail[0]['users'];
                $resultr=$wallet->add($addone);
                if ($resultr){
                    $user=$data['users'];
                    action_log('user_adduser', 'users', $resultr, UID,$user);
                    $this->success("注册成功");
                }
            }else{
                $this->error("两次密码不一致");
            }
        }
    }
    /**
     * 前台用户登录
     *
     */
    function login(){
        $data=array();
        $name=$this-> strFilter(I('fname'),true);
        $data['users']=$name;
        $mima=$this-> strFilter(I('lname'),true);
        $data['password']=jiami($mima);
        $where['users']=$name;
        $model=D('users');
        $Inquire=$model->where($where)->select();
        if($Inquire[0]['password']==$data['password']){
            $_SESSION['user']=$name;
            $redata=array();
            $redata['addip']=$Inquire[0]['addip'];
            $redata['id']=$Inquire[0]['id'];
            $redata['user']=$Inquire[0]['user'];
            $redata['addtime']=time();
            $model=M('loginsdaily');
            $add=$model->add($redata);
            $this->success("登录成功");
        }else{
            $this->error("密码错误请重输");
        }
    }
    /**
     * 修改用户状态
     *
     */
    function changeStatus(){
        $id=$_GET['id'];
        $method=$_GET['method'];
        $map['uid'] =   array('in',$id);
        $data['id']=$id;
        $model=M('users');
        $rest=$model->where($data)->select();
        $user=$rest[0]['users'];
        switch ( strtolower($method) ){
            case 'forbiduser':
                $caozuoqian="禁用";
                action_log('user_changeuser', 'users', $id, UID,$user,$caozuoqian);
                $this->forbid('users', $map );
                break;
            case 'resumeuser':
                $caozuoqian="启用";
                action_log('user_changeuser', 'users', $id, UID,$user,$caozuoqian);
                $this->resume('users', $map );
                break;
            case 'deleteuser':
               $this->delete('users', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    /**
     * 删除用户以及用户资产
     *
     */
//    public function deleteuser()
//    {
//        $ids=I('id');
//        $sid['id']=$ids;
//        $modeluser=M('users');
//        $restdata=$modeluser->where($sid)->select();
//        $user=$restdata[0]['users'];
//        $id=$restdata[0]['id'];
//        action_log('user_deleteuser', 'users', $id, UID,$user);
//        $data=$modeluser->where($sid)->delete();
//        if($data){
//            $model=M('userproperty');
//            $userid['userid']=$ids;
//            $result= $model->where($userid)->delete();
//            if($result){
//
//                $this->success("删除成功");
//            }
//        }
//    }


    /****登录日志*******/
    function logintext(){
        import('ORG.Util.Page');// 导入分页类
        $Data = M('loginsdaily'); // 实例化Data数据对象  date 是你的表名
        $map['status']=array("gt",-1);
        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15);// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select() ; // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('_list', $list);
        $this->assign('_page',$show);
        $this->meta_title = '用户登录日志';
        $this->display();
    }
    /****
     * 用户日志删除
     *****/
   public function deleteprice($ids = 0){
       empty($ids) && $this->error('参数错误！');
       $Data = M('loginsdaily');
       if(is_array($ids)){
           $map['id'] = array('in', $ids);
       }elseif (is_numeric($ids)){
           $map['id'] = $ids;
       }
       $data = $Data->where($map)->delete();
       if ($data != '') {
           $this->success("删除成功");
       } else {
           $this->error("删除失败");
       }
    }
    /**
     * 新增加用户
     *
     */
    public function add($username = '', $password = '', $repassword = '', $email = ''){
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }
            /* 调用注册接口注册用户 */
            $User   =   new UserApi;
            $uid    =   $User->register($username, $password, $email);
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
                if(!M('users')->add($user)){
                    $this->error('用户添加失败！');
                } else {
                    $this->success('用户添加成功！',U('index'));
                }
            } else {
                //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
           
            $this->meta_title = '新增用户';
            $this->display();
        }
    }
    /****
     *
     *用户充值
     *
     */

    function rechargedol(){
        $id['id']=$_GET['id'];
        if ($id['id']){
            $model=M('users');
            $redata=$model->field('users,id')->where($id)->find();
            $this->assign("data",$redata);
            $href="index.php/Admin/Userqian/index";
            session("href",$href);
        }
        $this->meta_title = '用户充值';
        $this->display();
    }
    function rechargemoney(){

        $username=I('username');
        $userid=I('userid');
        $ruidusername=I('ruidusername');
        $ruiduserid=I('ruiduserid');
        $href=I('href');
        if($ruidusername!="" && $ruiduserid!="" && $username=="" && $userid==""){
            $data['username']=  $this->stremail($ruidusername,true,"充值用户不可有非法字符");
            $user=$data['username'];
            $date['userid']= $this->strFilter($ruiduserid,true,"充值用户id不可有非法字符");
        }else if ($ruidusername=="" && $ruiduserid=="" && $username!="" && $userid!=""){
            $data['username']=  $this->stremail($username,true,"充值用户不可有非法字符");
            $user=$data['username'];
            $date['userid']= $this->strFilter($userid,true,"充值用户id不可有非法字符");
        }
        $id= $date['userid'];
        $cny=$this->strFilter(I('cny'),true);

        $model=M('userproperty');
        $model->startTrans();
        $old=$model->field('cny')->lock(true)->where($date)->select();
        $oldqian=$old[0]['cny'];
        if($cny<0){
            $model->rollback();
            $this->error("充值金额不可为负数");
        }
        if($old==null){
            $model->rollback();
        }
        $data['cny']=$cny+$old[0]['cny'];
        $newhou=$data['cny'];
        $redata=$model->where($date)->save($data);
        if(!$redata){
            $model->rollback();
            $this->error("充值失败");
        }
        $cnymodel=M('property');
        $chong['operaefront']=$oldqian;
        $chong['operatebehind']=$data['cny'];
        $chong['operatenumber']=$cny;
        $chong['operatetype']="充值人民币";
        $chong['userid']=$id;
        $chong['username']=$user;
        $chong['xnb']=1;
        $chong['explain']=session('user_auth.username');
        $chong['time']=time();
        $chongmodel=$cnymodel->add($chong);
        if(!$chongmodel){
            $model->rollback();
            $this->error("充值失败");
        }
        $model->commit();
        session("href",$href);
        action_log('user_recharge', 'userproperty', $id, UID,$user,$oldqian,$newhou,$cny,'cny');
        $this->success("充值成功");
    }
    function reuid(){
        $username=I('username');
        $userid=I('userid');
        if($username!=""){
            $data['users']=$this->stremail(I('username'),true,"充值用户不可有非法字符");
            $model=M('users');
            $redata=$model->where($data)->select();
            if ($redata){
                $this->ajaxReturn($redata);
            }else{
                $this->ajaxReturn(false);
            }
         }else if($userid!=""){
            $data['id']=$this->strFilter(I('userid'),true,"充值用户id不可有非法字符");
            $model=M('users');
            $redata=$model->where($data)->select();
            if ($redata){
                $this->ajaxReturn($redata);
            }else{
                $this->ajaxReturn(false);
            }
        }
    }
    function href(){
        $href=$this->returnback();
        session("href",null);
//        var_dump( session("href"));
        if($href){
            $data['href']=$href;
            $data['status']=1;
            $this->ajaxReturn($data);
        }else{
            $data['status']=0;
            $this->ajaxReturn($data);
        }
    }
    /*****
     * 
     * 用户虚拟币充值
     * 
     */
    function xnbrecharge(){
        $id['id']=$_GET['id'];
        if ($id['id']){
            $model=M('users');
            $redata=$model->field('users,id')->where($id)->find();
            $this->assign("data",$redata);
            $href="index.php/Admin/Userqian/index";
            session("href",$href);
        }
        $xnbmodel=M('xnb')->field('id,name,brief')->select();
        $this->assign("xnb",$xnbmodel);
        $this->meta_title = '用户虚拟币充值';
        $this->display();
    }
    function rechargexnb(){
        $username=I('username');
        $userid=I('userid');
        $ruidusername=I('ruidusername');
        $ruiduserid=I('ruiduserid');
        $href=I('href');
        if($ruidusername!="" && $ruiduserid!="" && $username=="" && $userid==""){
            $data['username']=  $this->stremail($ruidusername,true,"充值用户不可有非法字符");
            $user=$data['username'];
            $date['userid']= $this->strFilter($ruiduserid,true,"充值用户id不可有非法字符");
        }else if ($ruidusername=="" && $ruiduserid=="" && $username!="" && $userid!=""){
            $data['username']=  $this->stremail($username,true,"充值用户不可有非法字符");
            $user=$data['username'];
            $date['userid']= $this->strFilter($userid,true,"充值用户id不可有非法字符");
        }
        $id= $date['userid'];
        $xnb['id']=$this->strFilter(I('xnb'),true);
        $biref=M('xnb')->field('brief')->where($xnb)->find();
        $cny=$this->strFilter(I('cny'),true);
        $model=M('userproperty');
        $model->startTrans();
        $old=$model->field($biref['brief'])->lock(true)->where($date)->select();
        $oldqian=$old[0][$biref['brief']];
        if($cny<0){
            $model->rollback();
            $this->error("充值金额不可为负数");
        }
        if($old==null){
            $model->rollback();
        }
        $dati[$biref['brief']]=$cny+$old[0][$biref['brief']];
        $newhou=$dati[$biref['brief']];
        $redata=$model->where($date)->save($dati);
        if(!$redata){
            $model->rollback();
            $this->error("充值失败,1");
        }
        $cnymodel=M('property');
        $chong['operaefront']=$oldqian;
        $chong['operatebehind']=$dati[$biref['brief']];
        $chong['operatenumber']=$cny;
        $chong['operatetype']="充值虚拟币";
        $chong['userid']=$id;
        $chong['username']=$user;
        $chong['xnb']=$xnb['id'];
        $chong['explain']=session('user_auth.username');
        $chong['time']=time();
        $chongmodel=$cnymodel->add($chong);
        if(!$chongmodel){
            $model->rollback();
            $this->error("充值失败,2");
        }
        $model->commit();
        session("href",$href);
        action_log('user_recharge', 'userproperty', $id, UID,$user,$oldqian,$newhou,$cny,$biref['brief']);
        $this->success("充值成功");
    }
}
