<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ContentController extends HomeController {

	//系统首页
    public function index(){

    }
    public function typelist(){
        $map['status']=1;
        $map['toptype']=1;
        $typelist=M('texttype')->where($map)->select();
        $this->assign('typelist',$typelist);
        $type=I('type',2);
        $current_type=M('texttype')->where('id='.$type)->find();
        $this->assign('current_type',$current_type);
        $this->assign('type',$type);
        $map1['type']=$type;
        $map1['status']=1;
        $text=M('text');
        $list=$text->where($map1)->field('id,type,title,brief,endtime')->order('endtime desc')->page(I('p',1).',8')->select();
        $this->assign('list',$list);
        $count  = $text->where($map1)->count();// 查询满足要求的总记录数
        $Page   = new \Think\Page($count,8);// 实例化分页类
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->redata();
        $this->display(); // 输出模板
    }

    public function detail($id){
        $id=I('id');
        if(!empty($id)){
           $text=M('text')->where('id='.$id)->find();
           $this->assign('text',$text);

        }
        $map['status']=1;
        $map['toptype']=1;
        $typelist=M('texttype')->where($map)->select();
        $this->assign('typelist',$typelist);
        $type=$text['type'];
        $current_type=M('texttype')->where('id='.$type)->find();
        $this->assign('current_type',$current_type);
        $this->assign('type',$type);
        $this->redata();
        $this->display();
    }
    public function about(){
        $id=I('id',12);
        $map['type']=5;
        $map['status'] = 1;
        $list=M('text')->where($map)->field('id,title')->order('sort desc')->select();
        $text=M('text')->where('id='.$id)->find();
        
        $this->assign('cid',  $id);
        $this->assign('list',$list);
        $this->assign('text',$text);

        $this->display();

    }
    public function help(){
        $id=I('id',17);
        $map['type']=4;
        $map['status'] = 1;
        $list=M('text')->where($map)->field('id,title')->order('sort desc')->select();
        $text=M('text')->where('id='.$id)->find();

        $this->assign('id',$id);
        $this->assign('list',$list);
        $this->assign('text',$text);
        $this->redata();
        $this->display();
    }
}