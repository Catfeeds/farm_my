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
class UsercertificationController extends AdminController {
    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('realname'); // 实例化Data数据对象  date 是你的表名

        if (IS_POST){

        }
        $name=$this->  strFilter(I('name'))?$this-> strFilter(I('name')):"";
        $where['id']=array('like',"%".$name."%");;
        $where['users'] =array('like',"%".$name."%");
        $where['username'] =array('like',"%".$name."%");
        $where['_logic'] = "OR";
        $map['_complex'] = $where;
        $map['status']=array("gt",-1);
        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,10,array('name'=>$name));// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('_list', $list);
        $this->assign('_page',$show);
        $this->meta_title = '客户提现地址';

        $this->display();
    }
    function lookreal(){
        $id=$this->strFilter(I('id'));
        $where['currency_realname.id']=$id;
        $rest=M('realname')->field('
        currency_realname.id as id,
            currency_realname.userid as userid,
            currency_realname.users as users,
            currency_realname.username as username,
             currency_realname.topurl as topurl,
              currency_realname.bankurl as bankurl,
               currency_realname.takeurl as takeurl,
            currency_users.phone as phone,
            currency_users.idcard as idcard
        ')->join("LEFT JOIN  currency_users ON  currency_realname.userid= currency_users.id")->where($where)->select();
        $this->assign("data",$rest);
        if($rest){
            $this->display();
        }else{
            redirect('index.php/Admin/Userrecord/index/');
        }

    }
    /**
     * 确认审核
     *
     */
    function real(){
        $id['id']=$this-> strFilter(I('id'));
        $status=$this-> strFilter(I('status'));
        $model=M('realname');
        $model->startTrans();
        $redata=$model->where($id)->select();
        $data['userid']=$redata[0]['userid'];
        $data['username']=$redata[0]['username'];
        $data['topurl']=$redata[0]['topurl'];
        $data['bankurl']=$redata[0]['bankurl'];
        $data['takeurl']=$redata[0]['takeurl'];
//        $data['cardurl']=$redata[0]['cardurl'];
        $data['addtime']=$redata[0]['addtime'];
        $data['users']=$redata[0]['users'];
        $data['status']=$status;
        $data['endtime']=time();
        $data['admin']=session('user_auth.username');

        $modeldel=$model->where($id)->delete();

        if ($modeldel==false){
            $model->rollback();
            $this->error('审核失败！');
            exit();
        }
        $namemodel=M('realnamewater');
        $namewater=$namemodel->add($data);

        if($namewater==false){
            $model->rollback();
            $this->error('审核失败！');
            exit();
        }
        $user=M('users');
        $users['id']=$redata[0]['userid'];
        if($data['status']==1){
            $userdata['yanze']=1;
            $caozuoqian="审核通过";
        }else if($data['status']==0){
            $userdata['yanze']=-2;
            $caozuoqian="拒绝通过";
        }
        $usersave=$user->where($users)->save($userdata);
        if ($usersave==false){
            $model->rollback();
            $this->error('用户已进行过审核');
            exit();
        }
        action_log('user_realnamewater','realnamewater', $namewater, UID,$data['users'],$caozuoqian);
        $model->commit();
        $this->success('审核完成！');
//        redirect('index.php/Admin/Userrecord');
    }
   
}
