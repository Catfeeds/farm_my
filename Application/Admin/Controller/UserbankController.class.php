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
class UserbankController extends AdminController {
    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
//        //$data = M('bank')->join('LEFT JOIN currency_users ON currency_bank.userid = currency_users.id')->where('currency_bank.id != 0')->select();
//        $Action =   M('bank')->where(array('status'=>array('gt',1)));
//        $list   =   $this->lists( $Action);
//        int_to_string($list);
//        // 记录当前列表页的cookie
//        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('bank'); // 实例化Data数据对象  date 是你的表名
        $rest=$Data->field('id')->where("status=-1")->delete();
        if (IS_POST){

        }
        $name=$this-> strFilter(I('name'))?$this->  strFilter(I('name')):"";
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
     * 修改卡号地址状态
     *
     */
    function changeStatus(){
        $id=$_GET['id'];
        $method=$_GET['method'];
        $map['uid'] =   array('in',$id);
        $data['id']=$id;
        $model=M('bank');
        $resdata=$model->where($data)->select();
        $user=$resdata[0]['username'];
        $newhou=$resdata[0]['bank'];
        switch ( strtolower($method) ){
            case 'forbiduser':
                $caozuoqian="禁用";
                action_log('user_changebank', 'bank', $id, UID,$user,$caozuoqian,$newhou);
                $this->forbid('bank', $map );
                break;
            case 'resumeuser':
                $caozuoqian="启用";
                action_log('user_changebank', 'bank', $id, UID,$user,$caozuoqian,$newhou);
                $this->resume('bank', $map );
                break;
            case 'deleteuser':
                $caozuoqian="删除";
                action_log('user_deletebank', 'bank', $id, UID,$user,$caozuoqian,$newhou);
                $this->delete('bank', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    function deletebao(){
        $id=$_GET['id'];
        $model=M('bank');
        $data['id']=$id;
        $resdata=$model->where($data)->select();
        $user=$resdata[0]['username'];
        $newhou=$resdata[0]['bank'];
        $baodelete=$model->where($data)->delete();
        if ($baodelete){
            $caozuoqian="删除";
            action_log('user_deletebank', 'bank', $id, UID,$user,$caozuoqian,$newhou);
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }

    }
    function add(){
        $model=M('banktype');
        $redata=$model->field('bankname')->select();
        $this->assign("data",$redata);
        $this->display();
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
        $bank['bankname']=$this->strFilter(I('bank'),true,"开户银行不能为空");
        $data['bankprov']=$this-> strFilter(I('bankprov'));
        $data['bancity']=$this-> strFilter(I('bancity'));
        $data['bankcard']=$this-> strFilter(I('bankcard'));
        $data['type']=$this-> strFilter(I('type'));
        $data['addtime']=time();
        $data['bankaddr']=$bank['bankname'];
        $bankmodel=M('banktype')->field('id')->where($bank)->find();
        $data['bank']=$bankmodel['id'];
        $bankmodel=M('bank');
        $where['userid']= $id['id'];
        $restbank=$bankmodel->where($where)->select();
        if($restbank){
            $this->error("该用户已绑定过银行卡");
            exit();
        }

        if($redata){
            $bank=M('bank');
            $add=$bank->add($data);
            if($add){
                if($data['type']==1){
                    $caozuoqian="银行";
                }else if($data['type']==2){
                    $caozuoqian="支付宝";
                }else if($data['type']==3){
                    $caozuoqian="微信";
                }

                action_log('user_addbank', 'bank', $add, UID,$data['username'],$caozuoqian, $data['bankcard']);
                $this->success("添加成功");
            }
        }else{
            $this->error("用户不存在");
        }
//        var_dump($redata);
    }
}
