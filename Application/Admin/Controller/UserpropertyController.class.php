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
class UserpropertyController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('userproperty'); // 实例化Data数据对象  date 是你的表名
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
        $list = $Data->where($map)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('_list', $list);
        $this->assign('_page',$show);
        $this->meta_title = '客户资产';
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
        switch ( strtolower($method) ){
            case 'forbiduser':
                $this->forbid('wallet', $map );
                break;
            case 'resumeuser':
                $this->resume('wallet', $map );
                break;
            case 'deleteuser':
                $this->delete('wallet', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }
    function property(){
        $id=I('id');
        $xnb=I('xnb')?I('xnb'):"cny";
        $data=array();
        $model=M('userproperty')->where("currency_userproperty.id=$id")->select();
        $ss=array();
        $dd=array();
        foreach($model as &$v){
            $keys=array_keys($v);
            $data[]=$keys;
            for($i=0;$i<count($v);$i++){
                if($v[$data[0][$i]]==0){

                }else{
                    $ss[]=$data[0][$i];
                    if($data[0][$i]=$xnb){
                        $dd[]=$v[$data[0][$i]];
                    }
                }
            }
        }
        if($xnb=="cny"){
            $Data =   M('userproperty');
            $where['currency_userproperty.id']=$id;
            $minmodel = $Data
                ->field('
        currency_userproperty.id as currency_userproperty_id,
        currency_userproperty.userid as currency_userproperty_userid,
        currency_userproperty.cny as currency_userproperty_cny,
        currency_userproperty.username as currency_userproperty_username,
        currency_carryapply.poundage,
        currency_carryapply.allmoney
        ')->join("LEFT JOIN currency_carryapply ON currency_userproperty.userid =currency_carryapply.userid")->where($where)->select();
            $isd=array();
            $qiu=0;
            $sucssss=array();
            foreach($minmodel as $v){
                $sucss=0;
                    $isd[]=$v;
                    $shouxu=$v['poundage']/100;
                    $sucsss=($v['allmoney']*$shouxu)+$v['allmoney'];
                    for($i=0;$i<count($isd);$i++){
                        $sucssss[]=$sucsss;
                        $ssa=$qiu+$sucssss[$i];
                        $qiu=$ssa;
                    }
            }
            $minmodel['dongjie']=$qiu;
            $minmodel['keyong']=$dd[0];
            $minmodel['zong']=$qiu+$dd[0];

            $this->assign("yoona",$minmodel);
        } else{
            $Data =   M('userproperty');
            $where['currency_userproperty.id']=$id;
            $minmodel = $Data
                ->field('
        currency_userproperty.id as currency_userproperty_id,
        currency_userproperty.userid as currency_userproperty_userid,
        currency_userproperty.cny as currency_userproperty_cny,
        currency_userproperty.username as currency_userproperty_username,
        currency_entrust.poundage,
        currency_entrust.allmoney,
        currency_entrust.id as currency_entrust_id,
        currency_xnb.id as currency_xnb_id,
        currency_xnb.name as currency_xnb_name,
        currency_xnb.brief
        ')->join("LEFT JOIN currency_entrust on currency_userproperty.userid =currency_entrust.userid")->join("left join currency_xnb on currency_entrust.xnb=currency_xnb.id")->where($where)->order('currency_xnb.brief')->select();
            $isd=array();
            $qiu=0;
            $sucssss=array();
            foreach($minmodel as $v){
                $sucss=0;
                if($v['brief']==$xnb){
                    $isd[]=$v;
                    $shouxu=$v['poundage']/100;
                    $sucsss=($v['allmoney']*$shouxu)+$v['allmoney'];
                    for($i=0;$i<count($isd);$i++){
                        $sucssss[]=$sucsss;
                        $ssa=$qiu+$sucssss[$i];
                        $qiu=$ssa;
                    }
                }
            }
            $minmodel['dongjie']=$qiu;
            $minmodel['keyong']=$dd[0];
            $minmodel['zong']=$qiu+$dd[0];
            $this->assign("yoona",$minmodel);
        }
        $this->assign("xiang",$ss);
        $this->assign("xnb",$xnb);
        $this->assign("id",$id);
        $this->assign("yue",$dd[0]);
        $this->display();
    }
    /****
     * 
     * 用户资产流水
     * 
     */
    function details(){
        import('ORG.Util.Page');// 导入分页类
        $Data = M('property'); // 实例化Data数据对象  date 是你的表名
        if (IS_POST){

        }
        $wheres['id'] = I('id');     //  $id = I('id'); 用户id
        $userproperty = M('userproperty')->where($wheres)->select();
        $map['userid'] = array('eq',$userproperty[0]['userid']);
        $map['status']=array("gt",-1);
        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15);// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->field('
        currency_property.id,
        currency_property.username,
        currency_property.xnb,
        currency_property.operatenumber,
        currency_property.operatetype,
        currency_property.operaefront,
        currency_property.operatebehind,
        currency_property.explain,
        currency_property.time,
        currency_xnb.name     
        ')->join('currency_xnb on currency_xnb.id = currency_property.xnb' )->where($map)->order('currency_property.id')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('_list', $list);
        $this->assign('_page',$show);
        $this->display();
    }
    /****
     *
     * 用户资产流水删除
     *
     */
//    function dproperty(){
//        $where['id'] = I('id');
//        $data = M('property')->where($where)->delete();
//        if ($data){
//            $this->success("删除成功");
//        }else{
//            $this->error("删除失败");
//        }
//    }

}
