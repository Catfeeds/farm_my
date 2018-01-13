<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Page;

/**
 * 后台内容控制器
 * @author huajie <banhuajie@163.com>
 */
class ContentController extends AdminController {

    /* 文章列表 */
    public function arlist($type=null,$key=null){
        $map = array();
        if(!empty($type)){
            $map['type'] = $type;
        }
        if ($type == 16) {
            $map_1['toptype'] = $type;
            $typeid = M("texttype") -> where($map_1) -> field('id') -> select();
            $typeidstr = "";
            foreach ($typeid as $key => $value) {
                $typeidstr .= $value['id'].",";
            }
            $map['type'] = array('in', $typeidstr);
        }

        $count = M("text") -> where($map) -> count();
        $Page = new Page($count, 15);
        $show = $Page -> show();

        $arlist = M()
            -> table("currency_text as t")
            -> join("left join currency_xnb as x on t.xnbid = x.id")
            -> join("left join currency_texttype as tt on t.type = tt.id")
            -> join("left join currency_texttype as ttl on t.label = ttl.id")
            -> where($map)
            -> order("id desc")
            -> limit($Page -> firstRow, $Page -> listRows)
            -> field("t.*, x.name as xnbname, tt.title as typename, ttl.title as labelname")
            -> select();

        $count_ar = count($arlist);
        for ($i = 0; $i < $count_ar; $i ++) {
            if ($arlist[$i]['xnbname'] == NULL) {
                $arlist[$i]['xnbname'] = "公告";
            }
            if ($arlist[$i]['labelname'] == NULL) {
                $arlist[$i]['labelname'] = "系统";
            }
        }
        $this -> assign("type", $type);
        $this -> assign("page", $show);
        $this -> assign('list',  $arlist);
        $this -> display() ;
    }

    //更新文章
    public function arupdate(){
        $id=I('id');
        $data['label']   = I('label');
        $data['xnbid']   = I('xnb');
        $data['title']   = I('title');
        $data['admin']   = I('admin');
        $data['type']    = I('type');
        $data['brief']   = I('brief');
        $data['text']    = I('text');
        $data['header']  = I('header');
        $data['footer']  = I('footer');
        $data['status']  = I('status');
        $data['sort']    = I('sort');
        $data['addtime'] = I('addtime') != "" ? strtotime(I('addtime')) : time();
        $data['endtime'] = time();

        if(empty($id)){
            $res=M('text')->add($data);
        }else{
            $res=M('text')->where('id='.$id)->save($data);
        }
        if(!$res){
            $this->error(M('text')->getError());
        }else{
            $this->success($res>1?'新增成功':'更新成功', U('arlist',array('type'=>$data['type'])));
        }
    }

    public function arlabel() {
        $html = "<option value=''>--请选择--</option>";
        $toptype = $_POST['toptype'];
        $labellist = M("texttype") -> where('toptype = '. $toptype) -> select();

        foreach ($labellist as $key => $value) {
            $html .= '<option value="'. $value['id'] .'">'. $value['title'] .'</option>';
        }
        echo $html;
    }

    public function labelname() {
        $id = $_POST['id'];
        $labelname = M('texttype') -> field('id, title') -> where('id = '. $id) ->  find();
        echo $labelname['title'];
    }

    public function aredit(){
        $id   = I('id');
        $type = I('type');
        $fatherid      = M('texttype')->field('id')->where('toptype = 0')->select();
        $fatheridstr   = "0,";
        foreach ($fatherid as $item => $value) {
            $fatheridstr .= $value['id'].",";
        }
        $fatheridstr = substr($fatheridstr,0,strlen($fatheridstr)-1);

        $menus = M('texttype')    -> field(true) -> where('toptype in('. $fatheridstr. ')') -> select();
        $menus = D('Common/Tree') -> toFormatTree($menus,$title = 'title',$pk='id',$pid = 'toptype',$root = 0);

        $xnb = M("xnb") -> field('id, name') -> where("id <> 1") -> select();

        $this -> assign('type',  $type);
        $this -> assign('xnb',   $xnb);
        $this -> assign('Menus', $menus);

        if(!empty($id)){
            $article = M('text') -> find($id);
            $labelid = $article['label'];
            $type    = $article['type'];

            $alllabel = M('texttype') -> field('id, title') -> where('toptype = '. $article['type']) -> select();

            $this -> assign('type',    $type);
            $this -> assign('labelid', $labelid);
            $this -> assign('alllabel', $alllabel);
            $this -> assign('text',      $article);
        }
        $this -> display() ;

    }
    public function ardelete(){
        $ids = array_unique((array)I('ids',0));

        if(empty($ids[0])){
            $this->error('请选择要操作的数据!');
        }
        $map = array('id' => array('in', $ids) );
        $msg   = array_merge( array( 'success'=>'删除成功！', 'error'=>'删除失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        $res=M('text')->where($map)->delete();
        if($res!==false){
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
    }
    public function setstatus(){
        $ids    =   I('request.ids');
        $status =   I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }
        $map['id'] = array('in',$ids);
        $text=M('text');
        $result=$text->where($map)->setField('status',$status);
        if($result!==false){
            $this->success('修改成功');
        }
    }
    /*
   * 首页显示
   */
    public function headershow(){
        $status =   I('request.status');
        $ids    =   I('request.ids');
        if(empty($ids)){
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$ids);
        $text=M('text');
        $result=$text->where($map)->setField('header',$status);
        if($result!==false){
            $this->success('修改成功!');
        }
    }

    /*
   * 底部显示显示
   */
    public function footershow(){
        $ids    =   I('request.ids');
        $status =   I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$ids);
        $text=M('text');
        $result=$text->where($map)->setField('footer',$status);
        if($result!==false){
            $this->success('修改成功!');
        }

    }
    public function arsort(){
        $data['id']=I('id');
        $data['sort']=I('sort');
        if(empty($data['id'])){
            $this->error('参数错误!');
        }
        $res=M('text')->save($data);
        if($res!==false){
            $this->success('修改成功！');
        }else{
            $this->error('修改失败！');
        }

    }

    /*广告*/
    public function adlist($cate_id = null){
        $adlist=$this->lists('advertisement',null,'sort desc');
        int_to_string($adlist);
        $this->assign('adlist', $adlist);
        $this->meta_title = '广告管理';
        $this->display();
    }
    public function addad(){

        $this->meta_title = '广告管理';
        $this->display();
    }
    public function updatead(){

        $data['id'] =I('id');
        $data['name']=I('name');
        $data['url'] =I('url');
        $data['sort'] = I('sort');
        //$data['img'] = I('img');
        $data['addtime']=I('addtime',time());
        $data['endtime']=time();


        //处理图片
        if(!empty($_FILES['imgurl'])){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
            $upload->savePath  =      'Picture/';
            // 上传单个文件 
            $info   =   $upload->upload(I('imgurl'));
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            }else{// 上传成功 获取上传文件信息
                $data['img']="Uploads/".$info['imgurl']['savepath'].$info['imgurl']['savename'];
            }
        }

        $ad=M('advertisement');
        if(empty($data['id'])){
            $res= $ad->add($data);
        }else{
            $info = M('advertisement')->where('id = '.$data['id'])->find();
            $res= $ad->save($data);
            unlink($info['img']);
        }
        if(!$res){
            $this->error(D('advertisement')->getError());
        }else{
            $this->success($res>1?'新增成功':'更新成功', U('adlist'));
        }

    }
    public function editad(){
        $id=I('id');
        if($id<1){
            $this->error('参数错误');
        }
        $ad=M('advertisement')->where('id='.$id)->find();
        $this->assign('ad',$ad);
        $this->meta_title = '广告管理';
        $this->display();
    }
    public function deletead(){
        $ids = array_unique((array)I('ids',0));
        if(empty($ids[0])){
            $this->error('请选择要操作的数据!');
        }
        $map = array('id' => array('in', $ids) );
        $imgs=M('advertisement')->where($map)->field('img')->select();

        $msg   = array_merge( array( 'success'=>'删除成功！', 'error'=>'删除失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        if( M('advertisement')->where($map)->delete()!==false ) {
            foreach ($imgs as $value) {
                unlink($value['img']);
            }
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
        $this->display('adlist');
    }
    public function adsort(){
        $data['id']=I('id');
        $data['sort']=I('sort');
        if(empty($data['id'])){
            $this->error('参数错误!');
        }
        $res=M('advertisement')->save($data);
        if($res!==false){
            $this->success('修改成功!');
        }

    }

    /*
     * APP页面图片
     */
    public function appphotolist() {
        $adlist=$this->lists('appphoto',null,'sort desc');
        int_to_string($adlist);
        $this->assign('list', $adlist);
        $this->meta_title = 'APP图片展示列表';

        $this -> display();
    }
    public function addappphoto() {
        $id=I('id');
        if ($id) {
            $ad=M('appphoto')->where('id='.$id)->find();
            $this->assign('app',$ad);
            $this->meta_title = 'APP图片展示列表';
        }

        $this -> display();
    }
    public function updateappphoto() {
        $data['id'] =I('id');
        $data['name']=I('name');
        $data['sort'] = I('sort');
        //$data['img'] = I('img');
        $data['addtime']=I('addtime',time());
        $data['endtime']=time();


        //处理图片
        if(!empty($_FILES['imgurl'])){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
            $upload->savePath  =      'Picture/';
            // 上传单个文件
            $info   =   $upload->upload(I('imgurl'));
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            }else{// 上传成功 获取上传文件信息
                $data['imgurl']="Uploads/".$info['imgurl']['savepath'].$info['imgurl']['savename'];
            }
        }

        $ad=M('appphoto');
        if(empty($data['id'])){
            $res= $ad->add($data);
        }else{
            $info = M('advertisement')->where('id = '.$data['id'])->find();
            $res= $ad->save($data);
            unlink($info['img']);
        }
        if(!$res){
            $this->error(D('appphoto')->getError());
        }else{
            $this->success($res>1?'新增成功':'更新成功', U('adlist'));
        }

    }
    public function deleteappphoto() {
        $ids = array_unique((array)I('ids',0));
        if(empty($ids[0])){
            $this->error('请选择要操作的数据!');
        }
        $map = array('id' => array('in', $ids) );
        $imgs=M('appphoto')->where($map)->field('img')->select();

        $msg   = array_merge( array( 'success'=>'删除成功！', 'error'=>'删除失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        if( M('appphoto')->where($map)->delete()!==false ) {
            foreach ($imgs as $value) {
                unlink($value['img']);
            }
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
        $this->display('adlist');
    }
    public function appsort() {
        $data['id']=I('id');
        $data['sort']=I('sort');
        if(empty($data['id'])){
            $this->error('参数错误!');
        }
        $res=M('appphoto')->save($data);
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
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function tree($tree = null){
        C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }
    public function typelist(){
        $table=M('texttype');
        $map  = array('status' => array('gt', -1));
        $field = 'id,title,sort,toptype,header,footer,status';
        $list = $table->field($field)->where($map)->order('sort desc')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'toptype', $child = '_');

        $this->assign('tree', $list);
        C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取分类树模板

        $this->display();
    }

    public function addtype(){
        $pid=I('pid',0);
        $type=M('texttype')->where('id='.$pid)->field('id,title')->find();
        $this->assign('type',$type);
        $this->display();
    }
    public function updatetype(){
        $data['toptype']=I('toptype',0);
        $data['title']=I('title');
        $data['header']=I('header',1);
        $data['footer']=I('footer',1);
        $data['status']=I('status',1);
        $data['sort']=I('sort',0);
        $data['addtime']=I('addtime',time());
        $data['endtime']=time();
        $type=M('texttype');
        if(!isset($_POST['id'])){
            $res=$type->add($data);
        }else{
            $id=I('id');
            $res=$type->where(' id='.$id)->save($data);
        }
        if(!$res){
            $this->error($type->getError());
        }else{
            $this->success($res>1?'新增成功':'更新成功', U('typelist'));
        }
    }
    public function edittype(){
        $id=I('id',0);
        $type=M('texttype')->where('id='.$id)->find();
        $toptype=M('texttype')->where('id='.$type['toptype'])->find();
        $menus = M('texttype')->field(true)->select();
        $menus = D('Common/Tree')->toFormatTree($menus,$title = 'title',$pk='id',$pid = 'toptype',$root = 0);
        $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
        $this->assign('Menus', $menus);
        $this->assign('toptype',$toptype);
        $this->assign('type',$type);
        $this->display();
    }
    public function edit1(){
        $id=I('id',0);
        $data['title'] = I('title');
        $data['sort']=I('sort');
        if($id){
            $res= M('texttype')->where('id='.$id)->save($data);
            if($res){
                $this->success('修改成功!') ;

            }
        }
    }
    public function deltype(){
        $id = I('id',0);
        if(isset($id)){
            $type=M('texttype')->where('id='.$id)->find();
            $info=M('texttype')->where('toptype='.$type['id'])->select();
            if($info){
                $this->error('请先删除子分类!');
            }else{
                $info=M('text')->where('(type='.$id. ') or (label = ",'. $id. '")')->select();
                if($info){
                    $this->error('该分类下有文章，请先该分类下的文章！');
                }else{

                    $res= M('texttype')->delete($id);
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
        $text=M('texttype');
        $result=$text->where($map)->setField('status',$status);
        if($result!==false){
            $this->success('修改成功','',IS_AJAX);
        }
    }
    //友情链接
    public function links(){
        $list=$this->lists('interlinkage',null,'sort desc');
        int_to_string($list);
        $this->assign('list', $list);
        $this->meta_title = '友情链接';
        $this->display();

    }
    public function addlink(){
        $this->display();
    }
    public function updatelink(){
        $data['title']=I('title');
        $data['name']=I('name');
        $data['url']=I('url');
        $data['status']=I('status',1);
        $data['sort']=I('sort',0);
        $data['addtime']=I('addtime',time());
        $data['endtime']=time();
        $link=M('interlinkage');
        if(!isset($_POST['id'])){
            $res=$link->add($data);
        }else{
            $id=I('id');
            $res=$link->where(' id='.$id)->save($data);

        }
        if(!$res){
            $this->error($link->getError());
        }else{
            $this->success($res>1?'新增成功':'更新成功', U('links'));
        }
    }
    public function deletelink(){
        $ids = array_unique((array)I('ids',0));
        if(isset($ids)){
            $map = array('id' => array('in', $ids) );
            $res=M('interlinkage')->where($map)->delete();
            if($res){
                $this->success('删除成功！');
            }else{
                $this->error('删除失败！');
            }
        }else{
            $this->error('请选择要操作的数据');
        }
    }
    public function editlink(){
        $id = I('request.id');
        if(isset($id)){
            $link=M('interlinkage')->where('id='.$id)->find();
            $this->assign('link',$link);
            $this->display();
        }else{
            $this->error('未知错误!');
        }
    }
    public function sortlink(){
        $data['id']=I('id');
        $data['sort']=I('sort');
        if(empty($data['id'])){
            $this->error('参数错误!');
        }
        $res=M('interlinkage')->save($data);
        if($res!==false){
            $this->success('修改成功！');
        }

    }
    public function linkstatus(){
        $ids = array_unique((array)I('ids',0));
        $status =   I('request.status');
        if(empty($ids[0])){
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$ids);
        $link=M('interlinkage');
        $result=$link->where($map)->setField('status',$status);
        if($result!==false){
            $this->success('修改成功!');
        }
    }
    //用户反馈
    public function opinion() {
        $status = I("status") ? I("status") : "";

        if ($status !== "") {
            $map['o.status'] = $status;
        }
        $count = M("opinion as o") -> where($map) -> count();
        $Page = new Page($count, 15, array("status" => $status));
        $show = $Page -> show();

        $list = M()
            -> table("currency_opinion as o")
            -> join("left join currency_users as u on o.uid = u.id")
            -> field("o.*, u.users")
            -> where($map)
            -> order("o.time desc")
            -> limit($Page -> firstRow, $Page -> listRows)
            -> select();
        $this -> assign("_page", $show);
        $this -> assign("_list", $list);
        $this -> display();
    }
    //回复反馈
    public function opinionReply() {
        $id = I("id");
        $text = M("opinion") -> where("id = $id") -> field("uid, text") -> find();

        $this -> assign("text", $text);
        $this -> assign("id", $id);
        $this -> display();
    }
    //回复反馈提交
    public function addReply() {
        $id = I("id");
        $data['text'] = I("text");
        $data['uid'] = I("uid");
        $data['reply'] = $this -> strFilter(I("reply")) ? $this -> strFilter(I("reply")) : "";
        $data['status'] = 1;

        $res = M("opinion") -> where("id = $id") -> save($data);
        if ($res) {
            $this -> success("回复成功");
        } else {
            $this -> error("回复失败");
        }
    }
}
