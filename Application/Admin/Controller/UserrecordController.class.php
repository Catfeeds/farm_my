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
class UserrecordController extends AdminController {
    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('realnamewater'); // 实例化Data数据对象  date 是你的表名
        if (IS_POST){

        }
        $name=$this-> strFilter(I('name'))?$this-> strFilter(I('name')):"";
        $where['id']=array('like',"%".$name."%");;
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
    /**
     * 确认审核
     *
     */
    function real(){
        $id['id']=$this-> strFilter(I('id'));
        $model=M('realname');
        $model->startTrans();
//        $model=M('realname');
        $redata=$model->where($id)->select();
        $data['userid']=$redata[0]['userid'];
        $data['username']=$redata[0]['username'];
        $data['topurl']=$redata[0]['topurl'];
        $data['bankurl']=$redata[0]['bankurl'];
        $data['takeurl']=$redata[0]['takeurl'];
        $data['cardurl']=$redata[0]['cardurl'];
        $data['addtime']=$redata[0]['addtime'];
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
        $userdata['yanze']=1;
        $usersave=$user->where($users)->save($userdata);
        if ($usersave==false){
            $model->rollback();
            $this->error('审核失败！');
            exit();
        }
        $model->commit();
        $this->success('审核成功！');
        
    }
    /**
     * 增加提现卡号地址
     *
     */
    function join(){
        $id['id']=$this-> strFilter(I('userid'));
        $model=M('users');
        $redata=$model->where($id)->select();
        $data['username']=$redata[0]['users'];
        $data['userid']=$id['id'];
        $data['name']=$this-> strFilter(I('name'));
        $data['bank']=$this-> strFilter(I('bank'));
        $data['bankprov']=$this-> strFilter(I('bankprov'));
        $data['bancity']=$this-> strFilter(I('bancity'));
        $data['bankcard']=$this-> strFilter(I('bankcard'));
        $data['type']=$this-> strFilter(I('type'));
        $data['addtime']=time();
        if($redata){
            $bank=M('bank');
            $add=$bank->add($data);
            if($add){
                $this->success("添加成功");
            }
        }else{
            $this->error("用户不存在");
        }
//        var_dump($redata);
    }
    function changeStatus(){
        $id=$_GET['id'];
        $method=$_GET['method'];
        $map['uid'] =   array('in',$id);
        $data['id']=$id;
        $model=M('realnamewater');
        $resdata=$model->where($data)->select();
        $user=$resdata[0]['users'];
        switch ( strtolower($method) ){
            case 'forbiduser':
                $this->forbid('realnamewater', $map );
                break;
            case 'resumeuser':
                $this->resume('realnamewater', $map );
                break;
            case 'deleteuser':
                $caozuoqian="删除";
                action_log('user_delrealnamewater','realnamewater', $id, UID,$user,$caozuoqian);
                $date['id']=$resdata[0]['userid'];
                $yan['yanze']=-1;
                M('users')->where($date)->save($yan);
                $this->delete('realnamewater', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
}
