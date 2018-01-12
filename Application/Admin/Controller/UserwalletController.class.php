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
class UserwalletController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('wallet'); // 实例化Data数据对象  date 是你的表名
        $rest=$Data->field('id')->where("status=-1")->delete();
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
        $this->meta_title = '客户钱包';
        $this->display();
    }
    
    /**
     * 修改钱包状态
     *
     */
    function changeStatus(){
        $id=$_GET['id'];
        $method=$_GET['method'];
        $map['uid'] =   array('in',$id);
        $model=M('wallet');
        $data['userid']=$id;
        $resdata=$model->where($data)->select();
        $user=$resdata[0]['username'];
        $newhou=$resdata[0]['xnb'];
        switch ( strtolower($method) ){
            case 'forbiduser':
                $caozuoqian="禁用";
                action_log('user_changewallet', 'wallet', $id, UID,$user,$caozuoqian,$newhou);
                $this->forbid('wallet', $map );
                break;
            case 'resumeuser':
                $caozuoqian="启用";
                action_log('user_changewallet', 'wallet', $id, UID,$user,$caozuoqian,$newhou);
                $this->resume('wallet', $map );
                break;
            case 'deleteuser':
                $caozuoqian="删除";
                action_log('user_deletewallet', 'wallet', $id, UID,$user,$caozuoqian,$newhou);
                $this->delete('wallet', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    function deletebao(){
            $id=$_GET['id'];
            $model=M('wallet');
            $data['userid']=$id;
            $resdata=$model->where($data)->select();
            $user=$resdata[0]['username'];
            $newhou=$resdata[0]['xnb'];
            $baodelete=$model->where($data)->delete();
            if ($baodelete){
                $caozuoqian="删除";
                action_log('user_deletewallet', 'wallet', $id, UID,$user,$caozuoqian,$newhou);
                $this->success("删除成功");
            }else{
                $this->error("删除失败");
            }

        }
    
    /**
     *
     *读取修改钱包信息
    *
     */
    function modify(){
        $id['id']=$this-> strFilter(I('id'));
        $model=M('wallet');
        $data=$model->where($id)->select();
        $this->assign('data', $data);
        $this->display();
    }
    /**
     *
     *修改钱包信息
     *
     */
    function save(){
        $id['id']=$this-> strFilter(I('userid'),true);
        $data['userid']=$this-> strFilter(I('userid'),true);
        $data['username']=$this-> strFilter(I('username'),true);
        $data['xnb']=$this-> strFilter(I('xnb'),true);
        $data['label']=$this-> strFilter(I('label'),true);
        $data['addr']=$this-> strFilter(I('addr'),true);
        $model=M('wallet');
        $redata=$model->where($id)->select();
//        var_dump($id);
        if($redata){
            $request=$model->where($id)->save($data);
            if($request){
                action_log('user_modifywallet', 'wallet', $id['id'], UID,$data['username'],$data['xnb']);
                $this->success("修改成功");
            }else{
                $this->error("修改失败");
            }
        }else{
            $data['addtime']=time();
            $request=$model->add($data);
            if($request){
                action_log('user_addwallet', 'wallet', $request, UID,$data['username'],$data['xnb']);
                $this->success("新增成功");
            }else{
                $this->error("新增失败");
            }
        }
        
    }
}
