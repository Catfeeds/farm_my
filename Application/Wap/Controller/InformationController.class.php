<?php
namespace Wap\Controller;

use Think\Controller;

class InformationController extends WapController {
//    //资讯中心
//    public function arlist() {
//        $ofset1 = 8;
//        $ofset2 = 40;
//        //资讯列表
//        $arlist = M("text")
//            -> where("type = 3")
//            -> field("id, brief, addtime, title, type")
//            -> order("endtime desc")
//            -> limit($ofset1)
//            -> select();
//
//        //币种列表
//        $xnblist = M("xnb")
//            -> field("id, name, imgurl, brief, totalmoney")
//            -> limit($ofset2)
//            -> select();
//
//        $this -> assign("xnblist", $xnblist);
//        $this -> assign("arlist", $arlist);
//        $this -> display();

//    }

    public function arlist_more() {
        $groupNumber = I("groupNumber") ? I("groupNumber") : 1;
        $ofset2 = 20;
        //是否是刷新
        $reload = I("reload") ? I("reload") : 0;
        $refresh = I("refresh") ? I("refresh") : 0;
        if ($reload) {
            $firsttime = session("ar_first_time");
            $arlist = M("text")
                -> where("type = 2 and status = 1 and addtime > ". $firsttime)
                -> field("id, brief, addtime, title, type")
                -> order("addtime")
                -> limit(5)
                -> select();
            if ($arlist) {
                session("ar_first_time", $arlist[4]['addtime']);
            }
        } else {
            $ofset = 8;
            $arlist = M("text")
                -> where("type = 2 and status = 1")
                -> field("id, brief, addtime, title, type")
                -> order("addtime desc")
                -> limit(($ofset * ($groupNumber - 1)), $ofset)
                -> select();
            if ($groupNumber == 1 && !empty($arlist)) {
                session('ar_first_time', $arlist[0]['addtime']);
            }
        }

        if (!$refresh) {
            $xnblist = M("xnb")
                ->field("id, name, imgurl, brief")
                ->where("id not in(1)")
                ->limit(($ofset2 * ($groupNumber - 1)), $ofset2)
                ->select();
            $res = M()->query("select tt.xnb, substring_index(group_concat( tt.price ), ',', 1) lastest_price from (select t.xnb, t.price, t.time from currency_transactionrecords as t where t.standardmoney = 1 order by t.time desc) as tt group by tt.xnb");
            for ($i = 0; $i < count($res); $i++) {
                for ($j = 0; $j < count($xnblist); $j++) {
                    if ($xnblist[$j]['id'] == $res[$i]['xnb']) {
                        $xnblist[$j]['price'] = $res[$i]['lastest_price'];
                    }
                }
            }
        } else {
            $xnblist = M("xnb")
                ->field("id, name, imgurl, brief")
                ->where("id not in(1)")
                ->select();
            $res = M()->query("select tt.xnb, substring_index(group_concat( tt.price ), ',', 1) lastest_price from (select t.xnb, t.price, t.time from currency_transactionrecords as t where t.standardmoney = 1 order by t.time desc) as tt group by tt.xnb");
            for ($i = 0; $i < count($res); $i++) {
                for ($j = 0; $j < count($xnblist); $j++) {
                    if ($xnblist[$j]['id'] == $res[$i]['xnb']) {
                        $xnblist[$j]['price'] = $res[$i]['lastest_price'];
                    }
                }
            }
        }
        $data['arlist'] = $arlist;
        $data['xnblist'] = $xnblist;
        if (empty($data['arlist'])) {
            $data['arlist'] = 2;
        }
        if(empty($data['xnblist'])) {
            $data['xnblist'] = 3;
        }
        $this -> ajaxReturn($data);
    }
    //资讯详情
    public function ardetail() {
        $id = I('id');
        $type = I('type');

        $table = M("text");
        $map   = array("id" => $id);

        $ardetail = $table
            -> where($map)
            -> find();

        $typename = M("texttype") -> where("id = ". $type) -> field("title") -> find();

        $this -> assign("typename", $typename);
        $this -> assign("ardetail", $ardetail);
        $this -> display();
    }
    //公告
    public function notice() {
        $id = I("id");

        $notice = M("text") -> where("id = ". $id) -> find();

        $this -> assign("notice", $notice);
        $this -> display();
    }
    //币种介绍
    public function introduce() {
        $id = I("id");

        $table = M("xnb");
        $map = array("id" => $id);

        $xnbdetail = $table
            -> where($map)
            -> find();

        $this -> assign("xnbdetail", $xnbdetail);
        $this -> display();
    }
    //资讯
    public function moreInfo() {
//        $groupNumber = I("groupNumber") ? I("groupNumber") : 1;
//        $reload = I("reload") ? I("reload") : 0;
//        if ($reload) {
//            $firsttime = session("ar_first_time");
//            $list = M("text")
//                -> field("id, title, addtime, brief, type")
//                -> where("(type = 3 or type = 4) and status = 1 and addtime >". $firsttime)
//                -> order("addtime desc")
//                -> limit(5)
//                -> select();
//            if ($list) {
//                session("ar_first_time", $list[4]['addtime']);
//            }
//        } else {
//            $ofset = 8;
//            $list = M("text")
//                -> field("id, title, addtime, brief, type")
//                -> where("(type = 3 or type = 4) and status = 1")
//                -> order("addtime desc")
//                -> limit(($ofset * ($groupNumber - 1)), $ofset)
//                -> select();
//            if ($groupNumber == 1 && !empty($list)) {
//                session('ar_first_time', $list[0]['addtime']);
//            }
//        }
//
//
////        $this -> assign("list", $list);
        $this -> display();
//        if (empty($list)) {
//            $this -> ajaxReturn(2);
//        } else {
//            $this -> ajaxReturn($list);
//        }
    }
    public function moreInfo_more() {
        $groupNumber = I("groupNumber") ? I("groupNumber") : 1;
        $reload = I("reload") ? I("reload") : 0;
        if ($reload) {
            $firsttime = session("ar_first_time");
            $list = M("text")
                -> field("id, title, addtime, brief, type")
                -> where("(type = 3 or type = 4) and status = 1 and addtime >". $firsttime)
                -> order("addtime desc")
                -> limit(5)
                -> select();
            if ($list) {
                session("ar_first_time", $list[4]['addtime']);
            }
        } else {
            $ofset = 8;
            $list = M("text")
                -> field("id, title, addtime, brief, type")
                -> where("(type = 3 or type = 4) and status = 1")
                -> order("addtime desc")
                -> limit(($ofset * ($groupNumber - 1)), $ofset)
                -> select();
            if ($groupNumber == 1 && !empty($list)) {
                session('ar_first_time', $list[0]['addtime']);
            }
        }


//        $this -> assign("list", $list);
//        $this -> display();
        if (empty($list)) {
            $this -> ajaxReturn(2);
        } else {
            $this -> ajaxReturn($list);
        }
    }
}