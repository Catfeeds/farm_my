<?php
/**
 * Created by PhpStorm.
 * User: DENG
 * Date: 2017/7/14
 * Time: 22:34
 */
namespace Admin\Controller;

use Think\Page;

class RecController extends AdminController{
    //人民币提现
    public function cnyout_page($Data){
        $name=$this->strFilter(I('name'))?$this->strFilter(I('name')):"";
//        $case = $this -> strFilter(I('case')) ? $this -> strFilter(I('case')) : "";
        $map_1['currency_carryapplywater.id']=array('like',"%".$name."%");;
        $map_1['currency_carryapplywater.username'] =array('like',"%".$name."%");
        $map_1['currency_users.username'] =array('like',"%".$name."%");
        $map_1['_logic'] = 'OR';
        $map['_complex']=$map_1;
        $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name));// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data
            ->field('
            currency_carryapplywater.id as currency_carryapplywater_id,
            currency_carryapplywater.orderfor,
            currency_carryapplywater.userid,
            currency_carryapplywater.username,
            currency_carryapplywater.allmoney,
            currency_carryapplywater.poundage,
            currency_carryapplywater.money,
            currency_carryapplywater.bankaddr,
            currency_carryapplywater.bankcard,
            currency_carryapplywater.bankuser,
            currency_carryapplywater.addtime,
            currency_carryapplywater.endtime,
            currency_carryapplywater.status,
            currency_carryapplywater.bank as currency_carryapplywater_bank,
            currency_bank.type as currency_bank_type,
            currency_bank.bank as currency_bank_bank,
            currency_bank.bankname as currency_bank_bankname,
            currency_bank.bankcard as currency_bank_bankcard,
            currency_users.username as currency_users_username
            ')
            ->join(' left join currency_bank on currency_carryapplywater.userid=currency_bank.userid and currency_carryapplywater.bank=currency_bank.type')
            ->join(' left join currency_users on currency_carryapplywater.userid=currency_users.id')
            ->where($map)
            ->order('currency_carryapplywater.addtime desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $this->assign('_list',$list);// 赋值数据集,委托的数据
        $this->assign('_page',$show);// 赋值分页输出
    }

    //成交查询
    public function records($Data){
        //查询虚拟币，交易市场
        $xnb_list = M("xnb") -> field("id, name, brief") -> select();
        $market_list = M("markethouse") -> field("id, name") -> select();
        $map_1['t.buyoderfor'] = array("neq", '');

        //查询
        $search_mar = I("search_mar");
        $xnbid = $this -> strFilter(I('xnbid')) ? $this -> strFilter(I('xnbid')) : "";
        $search_user_type = I('search_user_type');
        $search = $this->strFilter(I('search')) ? trim($this -> strFilter(I('search'))) : "";
        if ($search_mar != "") {
            $map_1['t.market'] = $search_mar;
        }
        if ($xnbid != "") {
            $map_1['t.xnb'] = $xnbid;
        }
        switch ($search_user_type) {
            case 1:
                $uid = M("users") -> where("users like '%". $search ."%'") -> field("id") -> select();
                $uid = $this -> implode_id($uid, "id");
                $map_1['t.buy'] = array('in', $uid);

                break; //买家
            case 2:
                $uid = M("users") -> where("users like '%". $search ."%'") -> field("id") -> select();
                $uid = $this -> implode_id($uid, "id");
                $map_1['t.sell'] = array('in', $uid);

                break; //卖家
            default:break;
        }
        $count = M("transactionrecords as t") -> where($map_1) -> count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count, 15, array('search' => $search, 'search_type' => $search_user_type, 'xnbid' => $xnbid));// 实例化分页类 传入总记录数 传入状态；
        $show = $Page->show();// 分页显示输出

        //进行分页数据查询
        $list = M()
            -> table("currency_transactionrecords as t")
            -> join("left join currency_users as u on t.buy = u.id")
            -> join("left join currency_users as su on t.sell = su.id")
            -> join("left join currency_xnb as x on t.xnb = x.id")
            -> join("left join currency_markethouse as m on t.market = m.id")
            -> where($map_1)
            -> field("t.*, u.users as buy, su.users as sell, x.name as xnb, m.name as market")
            -> order("t.time desc")
            -> limit($Page -> firstRow, $Page -> listRows)
            -> select();
        $count_list = count($list);
        for ($i = 0; $i < $count_list; $i ++) {
            switch ($list[$i]['type']) {
                case 1:$list[$i]['type'] = "买";break;
                case 2:$list[$i]['type'] = "卖";break;
                default:break;
            }
        }

        $this -> assign( "market_list", $market_list );
        $this -> assign( "xnb_list",     $xnb_list );
        $this -> assign( "_list",        $list );
        $this -> assign( "_page",        $show );
    }

    //手续费查询
    public function poundage() {
        $search_type = I('search_type');
        $search = $this -> strFilter(I('search')) ? trim($this -> strFilter(I('search'))) : "";
        $start_time = I('start_time') ? I('start_time') : "";
        $end_time = I('end_time') ? I('end_time') : "";

        switch ($search_type) {
            case 0:
                $map = "substring(ODERFOR, 1, 50) like '%". $search ."%'";
                $map .= $this -> search_time($start_time, $end_time, 2);

                break; //买家订单号
            case 1:
                $map = "substring(ODERFOR, 42, 97) like '%". $search ."%'";
                $map .= $this -> search_time($start_time, $end_time, 2);

                break; //卖家订单号
            case 2:
                $map['p.username'] = array('like', '%'. $search .'%');
                $map['p.time'] = $this -> search_time($start_time, $end_time);


                break; //用户
            case 3:
                $mid = M('markethouse') -> where('name like "%'. $search .'%" ') -> field('id') -> select();
                $mid = $this -> implode_id($mid, "id");
                $map['p.market'] = array('in', $mid);
                $map['p.time'] = $this -> search_time($start_time, $end_time);

                break; //市场
            case 4:
                if (strpos($search, "买") !== false) {
                    $map = array('p.type' => 1);
                } elseif(strpos($search, "卖") !== false) {
                    $map = array('p.type' => 2);
                } elseif(strpos($search, "转出") !== false) {
                    $map = array('p.type' => 3);
                } elseif(strpos($search, "提现") !== false) {
                    $map = array('p.type' => 4);
                }
                $map['p.time'] = $this -> search_time($start_time, $end_time);

                break; //类型
            case 6:
                $xib = M("xnb") -> where('name like "%'. $search .'%" ') -> field('id') -> select();
                $xib = $this -> implode_id($xib, "id");
                $map['p.xnb'] = array('in', $xib);
                $map['p.time'] = $this -> search_time($start_time, $end_time);

                break; //币种
            default:break;
        }
        $count_page = M('poundage as p') -> where($map) -> count();
        $Page = new Page($count_page, 15, array('search' => $search, 'search_type' => $search_type, 'start_time' => $start_time, 'end_time' => $end_time));
        $show = $Page -> show();

        $list = M()
            -> table('currency_poundage as p')
            -> join('left join currency_xnb as x on p.xnb = x.id') //虚拟币名称
            -> join('left join currency_markethouse as m on p.market = m.id')
            -> where($map)
            -> field('p.*, x.name as xnb, m.name as market')
            -> order('p.time desc')
            -> limit($Page -> firstRow, $Page -> listRows)
            -> select();

        $this -> assign("page", $show);
        $this -> assign("poundage", $list);
    }

    //用户资产明细
    public function property() {
        $search_type = I('search_type');
        $search = $this -> strFilter(I('search')) ? trim($this -> strFilter(I('search'))) : "";
        $start_time = I('start_time') ? I('start_time') : "";
        $end_time = I('end_time') ? I('end_time') : "";

        switch ($search_type) {
            case 0:
                $map_1['p.userid'] = array('like', '%'. $search .'%');

                break; //用户ID
            case 1:
                $map_1['p.username'] = array('like', '%'. $search .'%');

                break; //用户名
            case 2:
                $xib = M("xnb") -> where('name like "%'. $search .'%"') -> field('id') -> select();
                $xib = $this -> implode_id($xib, "id");
                $map_1['p.xnb'] = array('in', $xib);

                break; //虚拟币
            case 3:
                $map_1['p.operatetype'] = array('like', '%'. $search .'%');

                break; //操作类型
            default:break;
        }
        if ($start_time != "" || $end_time != "") {
            $map_1['p.time'] = $this -> search_time($start_time, $end_time);
        }

        $count_page = M('property as p') -> where($map_1) -> count();
        $Page = new Page($count_page, 15, array('search' => $search, 'search_type' => $search_type, 'start_time' => $start_time, 'end_time' => $end_time));
        $show = $Page -> show();
        $list = M()
            -> table("currency_property as p")
            -> join("left join currency_xnb as x on p.xnb = x.id")
            -> where($map_1)
            -> field("p.*, x.name as xnb")
            -> order("p.time desc")
            -> limit($Page -> firstRow, $Page -> listRows)
            -> select();
        
        $this -> assign("page", $show);
        $this -> assign("property", $list);
    }

    //转入虚拟币
    public function paging_data($Data){

        $name=$this->strFilter(I('name'));

        $xnbname = $this -> strSearch(I('search_xnb')) ? $this -> strSearch(I("search_xnb")) : "";
        $xnbid = $this -> strFilter(I('xnbid')) ? $this -> strFilter(I('xnbid')) : "";

        $map_1['currency_xnbrollinwater.orderfor']=array('like',"%".$name."%");;
        $map_1['currency_xnbrollinwater.username'] =array('like',"%".$name."%");
        $map_1['currency_xnbrollinwater.admin'] =array('like',"%".$name."%");
        $map_1['_logic'] = 'OR';
        $map['_complex']=$map_1;
        if ($xnbid) {
            $map['currency_xnbrollinwater.xnb'] = $xnbid;
        }

        $count = $Data->where($map)
            ->join('left join currency_xnb on currency_xnbrollinwater.xnb=currency_xnb.id')
            ->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name, 'search_xnb' => $xnbname, 'xnbid' => $xnbid));// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)
            ->field('
            currency_xnb.id as currency_xnb_id,
            currency_xnb.brief,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrollinwater.id as currency_xnbrollinwater_id,
            currency_xnbrollinwater.allnumber,
            currency_xnbrollinwater.number,
            currency_xnbrollinwater.addr,
            currency_xnbrollinwater.remarks as currency_xnbrollinwater_remarks,
            currency_xnbrollinwater.addtime ,
            currency_xnbrollinwater.endtime ,
            currency_xnbrollinwater.orderfor,
            currency_xnbrollinwater.deliver,
            currency_xnbrollinwater.username,
            currency_xnbrollinwater.userid,
            currency_xnbrollinwater.admin,
            currency_xnbrollinwater.status
            ')
            ->join('left join currency_xnb on currency_xnbrollinwater.xnb=currency_xnb.id')
            ->order('currency_xnbrollinwater.addtime DESC')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $xnb = M("xnb") -> field("id, name, brief") -> select();
        $this -> assign("xnb_list", $xnb);
        $this->assign('data',$list);// 赋值数据集,委托的数据
        $this->assign('page',$show);// 赋值分页输出
    }

    //虚拟币转出记录；
    public function paging_data_out($Data){
        $map=array();
        $name=$this->strFilter(I('name'));
        $xnbname = $this -> strSearch(I('search_xnb')) ? $this -> strSearch(I("search_xnb")) : "";
        $xnbid = $this -> strFilter(I('xnbid')) ? $this -> strFilter(I('xnbid')) : "";

        $map_1['currency_xnbrolloutwater.username'] =array('like',"%".$name."%");
        $map_1['currency_xnbrolloutwater.admin'] =array('like',"%".$name."%");
        $map_1['currency_xnb.name']=array('like',"%".$name."%");
        $map_1['_logic'] = 'OR';
        $map['_complex']=$map_1;
        if ($xnbid) {
            $map['currency_xnbrolloutwater.xnb'] = $xnbid;
        }
        $count = $Data->where($map)
            ->join('left join currency_xnb on currency_xnbrolloutwater.xnb=currency_xnb.id')
            ->count();// 查询满足要求的总记录数 $map表示查询条件
        $Page = new Page($count,15,array('name'=>$name, 'search_xnb' => $xnbname, 'xnbid' => $xnbid));// 实例化分页类 传入总记录数 传入状态；

        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询
        $list = $Data->where($map)
            ->field('
            currency_xnb.id as currency_xnb_id,
            currency_xnb.brief,
            currency_xnb.name as currency_xnb_name,
            currency_xnbrolloutwater.id as currency_xnbrolloutwater_id,
            currency_xnbrolloutwater.allnumber,
            currency_xnbrolloutwater.number,
            currency_xnbrolloutwater.addr,
            currency_xnbrolloutwater.remarks as currency_xnbrolloutwater_remarks,
            currency_xnbrolloutwater.endtime,
            currency_xnbrolloutwater.addtime,
            currency_xnbrolloutwater.orderfor,
            currency_xnbrolloutwater.username,
            currency_xnbrolloutwater.userid,
            currency_xnbrolloutwater.poundage,
            currency_xnbrolloutwater.admin,
            currency_xnbrolloutwater.status
            ')
            ->join('left join currency_xnb on currency_xnbrolloutwater.xnb=currency_xnb.id')
            ->order('currency_xnbrolloutwater.addtime desc')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
        $xnb = M("xnb") -> field("id, name, brief") -> select();

        $this -> assign("xnb_list", $xnb);
        $this->assign('data',$list);// 赋值数据集,委托的数据
        $this->assign('page',$show);// 赋值分页输出
    }

    //搜索时间
    function search_time($start_time, $end_time, $status = 1) {
        $start_time = $start_time != "" ? $start_time : "2000-01-01";
        $end_time = $end_time != "" ? $end_time : "";
        $start_time_code = strtotime($start_time);
        if ($end_time != "") {
            $end_time_code = strtotime($end_time);
        } else {
            $end_time_code = time();
        }

        if ($status == 1) {
            $map = array('between', array("$start_time_code", "$end_time_code"));
        } else {
            $map = " AND p.time BETWEEN '". $start_time_code ."' AND '". $end_time_code ."'";
        }

        return $map;
    }

    //拼接id
    function implode_id($id, $vid) {
        if ($id) {
            $str = "";
            foreach ($id as $key => $value) {
                $str .= $value[$vid] . ",";
            }
            $id = substr($str, 0, strlen($str) - 1);
        } else {
            $id = "";
        }
        return $id;
    }

    protected function strSearch($str,$type=false,$error="含有非法字符请重输"){
        if($type){
            if($str==""){
                $this->error($error);
            }
        }
        $reg=" /\ |\￥|\……|\、|\‘|\’|\；|\：|\【|\】|\（|\）|\！|\·|\-|\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        //允许通过的特殊字符   。，《 》 “ ”
        $REGold=preg_match($reg,$str);
        if($REGold==1){
            $this->error($error);
        }else{
            return $str;
        }
    }
}