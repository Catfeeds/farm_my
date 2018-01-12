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
use Think\Page;

class InformationController extends HomeController {
    private $type = 2;
    private $search = "";
    private $Page;

    /*
     * 文章列表
     */
    public function arlist() {
        //当前类型和搜索条件
        $this -> type   = $this -> strFilter( I( 'type' ) ) ? $this -> strFilter( I( 'type' ) ) : 2;
        $this -> search = $this -> strFilter( I( 'search' ) ) ? $this -> strFilter( I( 'search' ) ) : "";
        $current_type    = M("texttype") -> where("id = ". $this -> type) -> field("id, title") -> find();

        //左侧菜单栏
        $table      = M( 'texttype' );
        $map_side   = array( "toptype" => 1 , "status" => 1);
        $list       = $table -> where( $map_side ) -> select();

        //查询数据条件
        $map_1['t.title'] = array( 'like', "%". $this -> search ."%" );
        if ($this -> search == "系统") {
            $map_1['t.label'] = "";
        } else {
            $map_1['tt.title'] = array( 'like', "%". $this -> search ."%" );
        }
        if ($this -> search == "公告") {
            $map_1['t.xnbid'] = 0;
        } else {
            $map_1['x.name'] = array( 'like', "%". $this -> search ."%" );
        }
        $map_1['_logic'] = "OR";
        $map['_complex'] = $map_1;
        $map['t.type']   = $this -> type;
        $map['t.status'] = 1;

        //分页
        $count    = M( "text as t" ) -> where( $map ) -> count();
        $show     = $this  -> getPage( $count );

        //查询
        $textlist = M()
            -> table( "currency_text as t" )
            -> join( "left join currency_xnb as x on t.xnbid = x.id " )
            -> join( "left join currency_texttype as tt on t.label = tt.id" )
            -> field( "t.*, x.name as xnbname, tt.title as labelname" )
            -> where( $map )
            -> order("id desc")
            -> limit( $this -> Page -> firstRow.','. $this -> Page -> listRows )
            -> select();
        $count = count( $textlist );
        for ( $i = 0; $i < $count; $i ++ ) {
            $textlist[$i]['xnbname'] = $textlist[$i]['xnbname'] == NULL ? "【公告】" : "【". $textlist[$i]['xnbname'] ."】";
            $textlist[$i]['labelname'] = $textlist[$i]['labelname'] == NULL ? "【系统】" : "【". $textlist[$i]['labelname'] ."】";
        }

        $this -> assign( "search",       $this -> search );
        $this -> assign( "page",          $show );
        $this -> assign( "artype",        $list );
        $this -> assign( "text",          $textlist );
        $this -> assign( "current_type", $current_type );
        $this -> redata();
        $this -> display();
    }

    /*
     * 文章详情
     */
    public function ardetail() {
        $textid = $this -> strFilter( I( 'id' ) );
        //查询详情
        $textdetail = M()
            -> table( "currency_text as t " )
            -> join( "left join currency_xnb as x on t.xnbid = x.id" )
            -> join( "left join currency_texttype as tt on t.type = tt.id" )
            -> field( "t.*, x.name as xnbname, x.id as xnbid, x.text as xnbtext, x.imgurl, tt.title as typename" )
            -> where( "t.id = ". $textid )
            -> find();
        if ( $textdetail['xnbname'] == "" ) {
            $textdetail['xnbname'] = "公告";
        }
        //查询同类型的资讯
        $textsame = M()
            -> table( "currency_text as t" )
            -> join( "left join currency_xnb as x on t.xnbid = x.id" )
            -> join( "left join currency_texttype as tt on t.label = tt.id" )
            -> field( "t.id, t.title, t.endtime, x.name as xnbname, tt.title as labelname" )
            -> where( "t.type = ". $textdetail['type'] )
            -> order( "id desc" )
            -> limit(4)
            -> select();
        $count = count( $textsame );

        $this -> assign( "textsame", $textsame );
        $this -> assign( "textdetail", $textdetail );
        $this -> redata();
        $this -> display();
    }

    /*
     * 分页处理
     */
    private function getPage($count) {
        import('ORG.Util.Page');
        $rows = 10;
        $this -> Page = new Page($count, $rows, array('type' => $this -> type, 'search' => $this -> search));
        
        //设置左右
        $this -> Page -> setConfig('prev', "&laquo;");
        $this -> Page -> setConfig('next', "&raquo;");
        $show = $this -> Page -> show();
        $ex_show = explode("<a class", $show);
        for ($i = 0; $i < count($ex_show); $i ++) {
            $ex_show[$i] = "<li><a class= " . $ex_show[$i] . "</li>";
        }
        unset($ex_show[0]);
        $show = implode("", $ex_show);
        return $show;
    }

}