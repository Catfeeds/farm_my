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

class TradingController extends AdminController {

    public function marking(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('markethouse as m'); // 实例化Data数据对象  date 是你的表名
        $name=$this->strFilter(I('name'))?$this->strFilter(I('name')):"";

        $where['m.id']=array('like',"%".$name."%");;
        $where['m.name'] =array('like',"%".$name."%");
        $where['_logic'] = "OR";
        $map['_complex'] = $where;
        $map['m.status']=array("gt",-1);

        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,4,array('name'=>$name));// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出

        // 进行分页数据查询 连表查询币种名称
        $list = M()
            -> table("currency_markethouse as m")
            ->join("left join currency_xnb as x on m.standardmoney = x.id")
            ->field("m.*, x.name as standardname")
            ->where($map)
            ->order('id')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('_list', $list);
        $this->assign('_page',$show);
        $this->meta_title = '交易市场';
        $this->display();
    }

    public function add(){
        $xnb_m= M('xnb');
        $markethouse_m=M('markethouse');
        if (IS_POST){
            $id=I('id');
            $name=$this->strFilter(I('name'));
            if (check_number($id)!=""){
                $name_back=$markethouse_m->field('id')->where(array('name'=>$name))->find();
                if ($name_back['id']!=$id && $name_back['id']!=""){
                    $this->error('市场名称已存在！1');
                    exit();
                }
            }else{
                $name_back=$markethouse_m->field('id')->where(array('name'=>$name))->find();
                if ($id!=""){
                    $this->error('市场名称已存在！2');
                    exit();
                }
            }
            $add_data['name']=$this->strFilter(I('name'));
            $add_data['sellminprice']=check_number(I('sellminprice'));
            $add_data['buyminprice']=check_number(I('buyminprice'));
            $add_data['sellmaxprice']=check_number(I('sellmaxprice'));
            $add_data['maxallmoney']=check_number(I('maxallmoney'));
            $add_data['minallmoney']=check_number(I('minallmoney'));
            $add_data['buymaxprice']=check_number(I('buymaxprice'));
            $add_data['standardmoney']=check_number(I('standardmoney'));  //本位币
            $add_data['openingquotation']=check_number(I('openingquotation'));  //是否开发
            $key=0;
            foreach ($add_data as $k=>$v){
                if ($v==""){
                    $this->error($this->getError($key));
                    exit();
                }
                $key++;
            }
            $add_data['xnb']=json_encode(I('xnb'));  //所属虚拟币
            $add_data['status']=I('status');
            if ($id!=""){
                $add_data['id']=$id;
                $save_back=$markethouse_m->save($add_data);
                if ($save_back===false){
                    $this->error('修改失败！');
                    exit();
                }
                $this->success('修改成功！');
            }else{
                $add_back=$markethouse_m->add($add_data);
                if ($add_back==false){
                    $this->error('添加失败！');
                    exit();
                }
                $this->success('添加成功！');
            }
            exit();
        }

        $id=I('id');
        if (check_number($id)!=""){
            $data=$markethouse_m->where(array('id'=>$id))->find();
            $data['xnb']=json_decode( $data['xnb']);
            $data['xnb']=$xnb_m->where(['id'=>['in',$data['xnb']]])->field('id,name')->select();
            $this->assign('data',$data);
        }
        $xnb_all=$xnb_m->field('id,name')->select();
        $this->assign('xnb_all',$xnb_all);
        $this->display();
    }

    private function getError($int){
        $back=['币种名称','卖家最小交易价','买家最小交易价','卖家最大交易价','买家大交易价','非法参数','非法参数'];
        return $back[$int];
    }

    public function changestatus(){
        if (IS_POST){
            $markethouse_m=M('markethouse');
            $id=I('id');
            $type=I('type');

            foreach ($id as $v){
                if (!is_numeric($v)){
                    $this->error('非法参数！');
                    exit();
                }
            }
            if ($type!=1 && $type!=2){
                $this->error('非法参数！');
                exit();
            }
           $back= $markethouse_m->where(['id'=>['in',$id]])->save(array('status'=>$type));
            if ($back===false){
                $this->error('修改失败！');
                exit();
            }
            $this->success('修改成功！');
            exit();
        }
    }

    public function changOpeningquotation(){
        if (IS_POST){
            $markethouse_m=M('markethouse');
            $id=I('id');
            $openingquotation=I('type');

            if(preg_match("/^[1-9][0-9]*$/",$id)!=1){
                $this->error('非法参数！');
                exit();
            };
            if ($openingquotation!=1 && $openingquotation!=2){
                $this->error('非法参数！');
                exit();
            }
            $back= $markethouse_m->where(['id'=>['in',$id]])->save(array('openingquotation'=>$openingquotation));
            if ($back===false){
                $this->error('修改失败！');
                exit();
            }
            $this->success('修改成功！');
            exit();
        }
    }

    public function delete(){
        if (IS_POST) {
            $id = I('id');
            $markethouse_m = M('markethouse');
            $delete_back = $markethouse_m->where(['id' =>['in',$id]])->delete();
            if ($delete_back === false) {
                $this->error('删除失败！');
                exit();
            }
            $this->success('删除成功！');
            exit();
        }
    }




}
