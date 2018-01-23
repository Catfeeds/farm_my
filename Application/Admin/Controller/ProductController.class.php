<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\RepeatCfgModel;
use Think\Page;

/**
 * 后台产品控制器
 * @author huajie <banhuajie@163.com>
 */
class ProductController extends AdminController {
    public function index() {
        $adlist=$this->lists('product',null,'time desc');
        int_to_string($adlist);
        $this->assign('list', $adlist);
        $this->meta_title = '产品列表';

        $this -> display();
    }

    public function add() {
        $id=I('id');
        if ($id) {
            $ad=M('product')->where('id='.$id)->find();
            $this->assign('text',$ad);
            $this->meta_title = '产品详情';
        }

        $fatherid      = M('procate')->field('id')->where('pid = 0')->select();
        $fatheridstr   = "0,";
        foreach ($fatherid as $item => $value) {
            $fatheridstr .= $value['id'].",";
        }
        $fatheridstr = substr($fatheridstr,0,strlen($fatheridstr)-1);

        $menus = M('procate')    -> field("id, name, pid") -> where('pid in('. $fatheridstr. ')') -> select();
        $menus = D('Common/Tree') -> toFormatTree($menus,$title = 'name',$pk='id',$pid = 'pid',$root = 0);

        $this -> assign('Menus', $menus);
        $this -> display();
    }

    public function update() {

        $data['id']             = I('id');
        $data['name']           = I('name');
        $data['price']          = I('price');
        $data['number']         = I('number');
        $data['cat_id']         = I('cat_id');
        $data['out']            = I('out');
        $data['description']    = I('description');
        $data['status']         = I('status');
        $data['sort']           = I('sort', 0);
        $data['cny']            = I('cny', null);
        $data['cmc']            = I('cmc', null);
        $data['integral']       = I('integral', null);
        $data['time']           = time();

        if ($data['out'] == null || $data['out'] == "") {
            $type = M("procate") -> field("type") -> where("id = ". $data['cat_id']) -> find();
            if ($type['type'] == 1) {
                $this -> error("该商品分类为红包商城，请添加商品的出局金额");
                exit();
            }
        }
        
        if ($data['cny'] == null || $data['cmc'] == null || $data['integral'] == null) {
            $type = M("procate") -> field("type") -> where("id = ". $data['cat_id']) -> find();
            if ($type['type'] == 2) {
                $this -> error("该商品分类为报单商城，请添加报单商品的所需属性");
                exit();
            }
        }

        //处理图片
        if($_FILES['imgurl']['name'] != ""){
            $info = $this -> upload(I("imgurl"));
            if($info['status'] ==2) {// 上传错误提示错误信息
                $this->error($info['data']);
            }else{// 上传成功 获取上传文件信息
                $data['img']=$info['data'];
            }
        }

        $ad=M('product');
        if(empty($data['id'])){
            $res= $ad->add($data);
        }else{
            if (isset($data['img']) && $data['img'] != "") {
                $info = M('product')->field("img")->where('id = '.$data['id'])->find();
                if (is_file($info['img'])) {
                    unlink($info['img']);
                }
            }
            $res= $ad->save($data);
        }
        if(!$res){
            $this->error(D('product')->getError());
        }else{
            $this->success($res>1?'新增成功':'更新成功', U('index'));
        }

    }

    public function delete() {
        $ids = array_unique((array)I('ids',0));
        if(empty($ids[0])){
            $this->error('请选择要操作的数据!');
        }
        $map = array('id' => array('in', $ids) );
        $imgs=M('product')->where($map)->field('img')->select();

        $msg   = array_merge( array( 'success'=>'删除成功！', 'error'=>'删除失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        if( M('product')->where($map)->delete()!==false ) {
            foreach ($imgs as $value) {
                if (isset($value['img']) && is_file($value['img'])) {
                    unlink($value['img']);
                }
                
            }
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
        $this->display('index');
    }

    public function sort() {
        $data['id']=I('id');
        $data['sort']=I('sort');
        if(empty($data['id'])){
            $this->error('参数错误!');
        }
        $res=M('product')->save($data);
        if($res!==false){
            $this->success('修改成功!');
        }
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
     * 上传文件
     */
    private function upload($img) {
        $upload = new \Think\Upload();                                   // 实例化上传类
        $upload->maxSize   =     3145728 ;                               // 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');     // 设置附件上传类型
        $upload->rootPath  =     './Uploads/';                           // 设置附件上传根目录
        $upload->savePath  =     'Picture/';
        // 上传单个文件
        $info   =   $upload->upload($img);
        if ($info) {
            return array("status" => 1, "data" => "Uploads/".$info['imgurl']['savepath'].$info['imgurl']['savename']);
        } else {
            return array("status" => 2, "data" => $upload->getError());
        }
    }
}
