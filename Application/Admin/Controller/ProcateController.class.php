<?php

namespace Admin\Controller;
use Think\Page;

/**
 * 产品分类
 * @author banana
 */
class ProcateController extends AdminController {

    /**
     * 分类列表
     */
    public function index($type=null,$key=null){
        $table=M('procate');
        $field = 'id,name,pid,status';
        $list = $table->field($field)->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_');

        $this->assign('tree', $list);
        C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取分类树模板

        $this->display();
    }

    /*文章类型*/
    public function gettree($id = 0, $field = true){
        /* 获取当前分类信息 */
        $table=M('texttype');
        if($id){
            $info = $table->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('gt', -1));
        $list = $table->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'toptype', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }

    /**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function tree($tree = null){
        C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }

    /**
     * 添加类别
     */
    public function add(){
        $pid=I('pid',0);
        $type=M('procate')->where('id='.$pid)->field('id,name')->find();
        $this->assign('type',$type);
        $this->display();
    }


    public function update(){
        $data['pid']=(int) I('pid',0);
        $data['name']=I('name');
        $data['status']= (int) I('status',1);

        $type=M('procate');
        if(!isset($_POST['id'])){
            $res=$type->add($data);
        }else{
            $id=I('id');
            $res=$type->where(' id='.$id)->save($data);
        }

        if(!$res){
            $this->error($type->getError());
        }else{
            $this->success($res>1?'新增成功':'更新成功', U('index'));
        }
    }
    public function edit(){
        $id=I('id',0);
        $type=M('procate')->where('id='.$id)->find();
        $toptype=M('procate')->where('id='.$type['pid'])->find();
        $menus = M('procate')->field(true)->select();
        $menus = D('Common/Tree')->toFormatTree($menus,$title = 'name',$pk='id',$pid = 'pid',$root = 0);
        $menus = array_merge(array(0=>array('id'=>0,'name'=>'顶级菜单')), $menus);
        // echo "<pre>";
        // print_r($toptype);
        $this->assign('Menus', $menus);
        $this->assign('toptype',$toptype);
        $this->assign('type',$type);
        $this->display();
    }
    public function edit1(){
        $id=I('id',0);
        $data['name'] = I('name');
        if($id){
            $res= M('procate')->where('id='.$id)->save($data);
            if($res){
                $this->success('修改成功!') ;
            }
        }
    }
    public function deltype(){
        $id = I('id',0);
        if(isset($id)){
            $type=M('procate')->where('id='.$id)->find();
            $info=M('procate')->where('pid='.$type['id'])->select();
            if($info){
                $this->error('请先删除子分类!');
            }else{
                $info=M('product')->where('(type='.$id. ') or (label = ",'. $id. '")')->select();
                if($info){
                    $this->error('该分类下有文章，请先该分类下的文章！');
                }else{

                    $res= M('procate')->delete($id);
                    if($res){
                        $this->success('删除成功！');
                    }else{
                        $this->error('删除失败！');
                    }
                }
            }
        }else{
            $this->error('请选择要操作的数据','',IS_AJAX);
        }

    }
    public function changestatus(){
        $status =   I('request.status');
        $id    =   I('request.id');
        if(empty($id)){
            $this->error('请选择要操作的数据');
        }
        $map['id'] = array('in',$id);
        $text=M('procate');
        $result=$text->where($map)->setField('status',$status);
        if($result!==false){
            $this->success('修改成功','',IS_AJAX);
        }
    }
}
