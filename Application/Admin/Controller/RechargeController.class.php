<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Think\Page;
/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class RechargeController extends AdminController
{
    public function index()
    {
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('rechargewater'); // 实例化Data数据对象  date 是你的表名

        $name=$this->strFilter(I('name'))?$this->strFilter(I('name')):"";
//        $method=$this->strFilter(I('method'))?$this->strFilter(I('method')):"";
//        if ($method != "") {
//            $where['rechargetype'] = $method;
//        }
        $map_1['username'] = array('like', '%'. $name .'%');
        $map_1['admin'] = array('like', '%'. $name .'%');
        $map_1['_logic'] = "OR";
        $where['_complex'] = $map_1;
//        $where['status']=array("gt",-1);
        $count = $Data->where($where)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,10,array('name'=>$name));// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($where)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条

        $this->assign('_list',$list);// 赋值数据集,委托的数据
        $this->assign('_page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }

    function apply()//人民币提现
    {
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('carryapplywater'); // 实例化Data数据对象  date 是你的表名
        $financeMethod=new RecController();
        $financeMethod->cnyout_page($Data);
        $this->display(); // 输出模板

    }

    function xnllinewater()//虚拟币转入记录
    {
        import('ORG.Util.Page');// 导入分页类
        $Data = M('xnbrollinwater'); // 实例化Data数据对象  date 是你的表名
        $financeMethod=new RecController();
        $financeMethod->paging_data($Data);
        $this->display(); // 输出模板
    }

    function xnlloutwater()//虚拟币转出记录
    {
        import('ORG.Util.Page');// 导入分页类
        $Data = M('xnbrolloutwater'); // 实例化Data数据对象  date 是你的表名
        $financeMethod=new RecController();
        $financeMethod->paging_data_out($Data);
        $this->display(); // 输出模板
    }

    function records()//成交记录
    {
        import('ORG.Util.Page');// 导入分页类
        $Data = M('transactionrecords as t'); // 实例化Data数据对象  date 是你的表名
        $financeMethod=new RecController();
        $financeMethod->records($Data);

        $this->display();
    }

    function poundage() //手续费记录
    {
        import('ORG.Util.Page');
        $poundage = new RecController();
        $poundage -> poundage();
        
        $this -> display();
    }
    
    function property() //用户资产明细
    {
        import('ORG.Util.Page');
        $property = new RecController();
        $property -> property();
        
        $this -> display();
    }

    function changeStatus()
    {
        $id     = $_GET['id'];
        $method = $_GET['method'];
        $model  = $_GET['model'];
//        var_dump($model);
        $map['uid'] = array('in', $id);
        switch (strtolower($method)) {
            case 'forbiduser':
                $this->forbid("$model", $map);
                break;
            case 'resumeuser':
                $this->resume("$model", $map);
                break;
            case 'deleteuser':
                $this->delete("$model", $map);
                break;
            default:
                $this->error('参数非法');
        }
    }

    function memory(){   //会员锁定资产

        $where = [];

        $search_type=  I('search_type'); //查询类型

        if ($search_type==0 && !empty(I('search'))){
            $where['currency_users.users'] = ['like','%'.I('search').'%'];
        }elseif ($search_type==1 && !empty(I('search'))){
            $where['currency_xnb.name'] = I('search');
        }elseif ($search_type==2){
            $where['currency_memory.time_start']=[['EGT',I('start_time')],['ELT',I('end_time')]];
        }elseif ($search_type==3){
            $where['currency_memory.time_end']=[['EGT',I('start_time')],['ELT',I('end_time')]];
        }
        $this->assign('search_type',$search_type ? $search_type :0);
        $count =  M('memory')
                ->join('left join currency_users on currency_memory.user_id=currency_users.id')
                ->join('left join currency_xnb on currency_memory.xnb_id=currency_xnb.id')
            ->field('currency_memory.id')
            ->where($where)
            ->count();

        import('ORG.Util.Page');
        $Page = new Page($count,15);// 实例化分页类 传入总记录数 传入状态；

        $data =  M('memory')
                ->join('left join currency_users on currency_memory.user_id=currency_users.id')
                ->join('left join currency_xnb on currency_memory.xnb_id=currency_xnb.id')
                ->field('
                    currency_memory.id,
                    currency_users.users,
                    currency_xnb.name,
                    currency_memory.number_all,
                    currency_memory.balance,
                    currency_memory.time_start,
                    currency_memory.time_end
                ')
                  ->where($where)
                  -> limit($Page -> firstRow, $Page -> listRows)
                  ->select();
        $show = $Page->show();// 分页显示输出
        $this->assign('data',$data);

        $this->assign('page',$show);
        $this->display();
    }


    /**
     * 用户锁定资产发放期数
     */
    function memorylist(){
        import('ORG.Util.Page');

        $memoryall_m = M('memory_all');

        $time_start = I('start_time');
        $time_end = I('end_time');

        $where = [];

        if ($time_end && $time_start){

            $where['time'] = [['EGT',strtotime($time_start)],['elt',strtotime($time_end)],'and'];

        }

        $count = $memoryall_m->where($where)->count();

        $Page = new Page($count,15,['start_time'=>$time_start,'end_time'=>$time_end]);// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();

        $data = $memoryall_m->where($where)->field('id,time')-> limit($Page -> firstRow, $Page -> listRows)->select();

        $this->assign('data',$data);

        $this->assign('page',$show);

        $this->display();
    }

    /**
     * 发放详情
     */
    function memorylist_all(){

        $xnb_m = M('xnb');

        $memoryall_m = M('memory_all');

        $id = I('id');

        $xnb_all = $xnb_m->field('id,brief,name')->select();

        $list = $memoryall_m->where(['id',$id])->find();

        $memory = json_decode($list['data'],true);


        foreach ($xnb_all as &$value){

            $value['memory'] = $memory[$value['id']];

            $value['time'] = $list['time'];

        }

        $this->assign('data',$xnb_all);

        $this->assign('id',$id);

        $this->display();

    }



    /**
     * 发放详情列表
     */

    function memorylist_list(){
        import('ORG.Util.Page');

        $memorylist_M = M('memory_list');

        $id = I('id');

        $where = ['currency_memory_list.memory_id'=>$id];

        $user = I('search');

        if (!empty($user)){

            $where['currency_users.users'] = $user;

        }

        $count = $memorylist_M
            ->where($where)
            ->join('currency_xnb on currency_memory_list.xnb_id = currency_xnb.id')
            ->join('currency_users on  currency_memory_list.user_id = currency_users.id')
            ->field('
                currency_memory_list.memory_id,
                currency_xnb.name,
                currency_users.users,
                currency_memory_list.number,
                currency_memory_list.time
            ')
            ->count();
        $Page = new Page($count,15,['id'=>$id,'search'=>$user]);// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();

        $data = $memorylist_M
            ->where($where)
            ->join('currency_xnb on currency_memory_list.xnb_id = currency_xnb.id')
            ->join('currency_users on  currency_memory_list.user_id = currency_users.id')
            ->field('
                currency_memory_list.memory_id,
                currency_xnb.name,
                currency_users.users,
                currency_memory_list.number,
                currency_memory_list.time
            ')
            ->limit($Page->firstRow.','.$Page->listRows)

            ->select();

        $this->assign('data',$data);

        $this->assign('id',$id);

        $this->assign('page',$show);

        $this->display();
    }









}