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

//添加虚拟币
class SetupController extends AdminController  {
   //虚拟币列表 
   public function xnb(){
      import('ORG.Util.Page');// 导入分页类
      $Data =   M('xnb');// 实例化Data数据对象  date 是你的表名
      $brief=$this->strFilter(I('brief'));
      $map_1['brief'] =array('like',"%".$brief."%");
      $map_1['name'] = array('like', '%'. $brief .'%');
      $map_1['_logic'] = "OR";
      $map['_complex'] = $map_1;
      $map['id'] = ['not in',[1,2,3,4]];

      $count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
      $Page = new Page($count,10,array('brief'   =>$brief));// 实例化分页类 传入总记录数 传入状态；
      $show = $Page->show();// 分页显示输出
      // 进行分页数据查询
      $list = $Data->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
      $this->assign('data',$list);// 赋值数据集
      $this->assign('page',$show);// 赋值分页输出
      $this->display(); // 输出模板
   }

   //添加虚拟币
   public function addxnb(){
      $xnb_m=  M('xnb');
      $id=$this->strFilter(I('id'));  //接受修改货币的id
      $markethouse_m=M('markethouse');
      $markedata=$markethouse_m->field('id,name')->select();
      if (IS_POST){   //添加货币

         $add_data=array();

         $add_data['sort']=I('sort');  //排序
//         $add_data['ip']=I('ip');
//         $add_data['port']=I('port');
//         $add_data['password']=I('password');
//         $add_data['selltop']=I('selltop');  //卖出上限
//         $add_data['buytop']=I('buytop'); //买入上限
         $add_data['riserange']= I('riserange'); //涨停幅度
         $add_data['fallrange']= I('fallrange'); //跌停幅度

         $add_data['price_up']=I('price_up'); //涨停额度
         $add_data['price_dow']=I('price_dow'); //跌停额度

         $add_data['memory_day']=I('memory_day'); //锁定周期
         $add_data['memory_back']=I('memory_back')/100; //解锁日返率


         $add_data['status']=I('status');   //是否启用 1.2
         $add_data['changestatus']=I('changestatus');  //转入状态1.2
         $add_data['poundage']=I('poundage');  //转出手续费
//         $add_data['voluntarily']=I('voluntarily');  //转出自动
//         $add_data['market']=I('market');   //市场
         $add_data['minnumber']=I('minnumber');  //转出最小数
         $add_data['maxnumber']=I('maxnumber');  //转出最大数
         $add_data['inminnumber']=I('inminnumber');  //转入最小数
         $add_data['inmaxnumber']=I('inmaxnumber');  //转入最大数
         if ($add_data['maxnumber'] <= $add_data['minnumber']) {
            $this -> error("最大转出量应大于最小转出量");
            exit();
         }
         $add_data['scale']=I('scale');
         $add_data['buypoundage']=I('buypoundage');
         $add_data['sellpoundage']=I('sellpoundage');
         $add_data['totalmaxnumber']=I('totalmaxnumber');
         foreach ($add_data as $k=>$v){
            if (!is_numeric($v)){
               $this->error($this->getname($k).'：含有非法字符！');
            }
         }

         $add_data['brief']=trim(I('brief')); //币种简称
         $p_back=preg_match("/^[A-Za-z]+$/",$add_data['brief']);

         if ($p_back!=1){
            $this->error('币种简称：非法字符!');
         }
         $add_data2['author']=I('author'); //研发者
         $add_data2['algorithm']=I('algorithm'); //核心算法
         $add_data2['blocktime']=I('blocktime'); //区块时间、
         $add_data2['blockreward']=I('blockreward'); //区块奖励
         $add_data2['totalmoney']=I('totalmoney'); //货币总量、
         $add_data2['advantage']=I('advantage'); //主要特色、
         $add_data2['disadvantage']=I('disadvantage'); //不足之处、
         $add_data2['name']=I('name');  //币种名称
         $add_data2['text']=I('text'); //币种介绍
         $add_data2['enname']=I('enname'); //英文名、
         foreach ($add_data2 as $k => $v) {
            $this -> strFilter($v, true, $this -> getname2($k). '：含有非法字符！');
         }
         $add_data = $add_data + $add_data2;
         $add_data['address']=base64_encode(I('address'));     //钱包地址

         $add_data['createtime']=I('createtime'); //推出时间
         $add_data['releasedate']=I('releasedate'); //发布时间
         if ($add_data['createtime']==""){
            $this->error("推出时间：含有非法字符！");
         }
         if ($add_data['releasedate']==""){
            $this->error("发布时间：含有非法字符！");
         }

         $name_back=$xnb_m->field('id')->where(array(     //币种名称不能相同
            'name'=>$add_data['name']
         ))->find();

         $brief_back=$xnb_m->field('id')->where(array(   //币种简称不能相同
             'brief'=>$add_data['brief']
         ))->find();


         if ($id!=""){
            if ($name_back['id']!=$id && $name_back['id']!=""){
               $this->error("币种已存在！");
               exit();
            }

            if ($brief_back['id']!=$id && $brief_back['id']!=""){
               $this->error("币种简称已存在！");
               exit();
            }

         }else{

            if ($name_back['id']!=""){
               $this->error("币种已存在！1");
               exit();
            }
            if ($brief_back['id']!=""){
               $this->error("币种简称已存在！1");
               exit();
            }
         }

         if ($_FILES['imgurl']['size']>0){           //有文件被上传时才执行文件操作！
            if ($id!=""){    //
               $imgurl_back=$xnb_m->field('imgurl')->where(array(
                   'id'=>$id
               ))->find();
               unlink($imgurl_back['imgurl']);
            }
            import('ORG.Net.UploadFile');
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
   //         $upload->rootPath  =     '/Publick/img'; // 设置附件上传根目录
            $upload->savePath  =     ''; // 设置附件上传（子）目录
            // 上传文件
            $info   =   $upload->upload(I('imgurl'));  //上传文件功能！
            $add_data['imgurl']='Uploads/'.$info['imgurl']['savepath'].$info['imgurl']['savename'];   //文件路径
         }

         $xnb_m->startTrans();  //开启事务

         if ($id!=""){
            $add_data['id']=$id;
            $xnb_databack=$xnb_m->field('currency_xnb.imgurl,brief,currency_xnb.market as currency_xnb_market,openingquotation')
                                  ->join('left join currency_markethouse on currency_xnb.market=currency_markethouse.id')
                                 ->where(array('currency_xnb.id'=>$id))->find();   //关联查询如果没有闭盘，不允许修改信息

//            if ($xnb_databack['openingquotation']==1){
//               $this->error('该虚拟币未闭盘,不能修改基础信息!');
//            }
            $save_list=true;

            if ($xnb_databack['brief']!=$add_data['brief']){   //如果简称未修改，就不去修改列名
               $save_list=M()->execute('alter table `currency_userproperty` change `'.$xnb_databack['brief'].'` `'.$add_data['brief'].'` NUMERIC(17,6)  NOT NULL');
            }
            $xnb_sava_back=$xnb_m->save($add_data);
            if ($xnb_sava_back===false || $save_list===false){
               $xnb_m->rollback();
               $this->error("修改失败！6");
               exit();
            }
            $xnb_m->commit();
            $this->success("修改成功！");
         }else{
            $url='./Public/trade/'.md5(uniqid(rand(),true)).'.text';    //购买生成配置文件
            $urls='./Public/trade/'.md5(uniqid(rand(),true)).'.text';    //卖出生成配置文件

            $url_back=fopen($url,'w');
            $url_backs=fopen($urls,'w');

            if ($url_back === false || $url_backs === false){
               unlink( $add_data['imgurl']);   //如果并发文件创建失败，那么删除刚才上传的虚拟币图片！
               $xnb_m->rollback();
               $this->error('系统故障请联系我们!1');
               exit();
            }

            $Model = M()->execute('ALTER TABLE `currency_userproperty` ADD `'.$add_data['brief'].'` NUMERIC(17,6)  NOT NULL default 0');   //添加字段

            if ($Model===false){
               unlink($add_data['imgurl']);   //如果上传数据库失败，则删除图片文件
               unlink($url);                      //如果上传数据库失败，则删除配置文件
               unlink($urls);                      //如果上传数据库失败，则删除配置文件
               $xnb_m->rollback();
               $this->error('添加失败!2');
               exit();
            }

            $add_data['buycomplicated']=$url;
            $add_data['sellcomplicated']=$urls;
            $xnb_add_back=$xnb_m->add($add_data);//添加币种
            if ($xnb_add_back==false){
               unlink($add_data['imgurl']);   //如果上传数据库失败，则删除图片文件
               unlink($url);                      //如果上传数据库失败，则删除配置文件
               unlink($urls);                      //如果上传数据库失败，则删除配置文件
               $xnb_m->rollback();
               $this->error('添加失败!3');
               exit();
            }
            $xnb_m->commit();
            $this->success('添加成功！');
            exit();
         }
         }

         if ($id!="" && $id!=null){ //修改货币的信息,单一货币的信息查询
            $data=$xnb_m->where(array(
               'id'=>$id
            ))->find();
            $this->assign('data',$data);
         }

         $this->assign('markedata',$markedata);
         $this->display();
   }
   
   private function getname($value){
      $error=array(
          'sort'=>'排序',
          'selltop'=>'单笔卖出上限',
          'buytop'=>'单笔买入上限',
          'riserange'=>'涨停幅度',
          'fallrange'=>'跌停幅度',
          'status'=>'是否启用',
          'changestatus'=>'转入状态',
          'poundage'=>'转出手续费',
          'minnumber'=>'单笔转出最小数量',
          'maxnumber'=>'单笔转出最大数量',
          'totalmaxnumber' => '转出最大数',
          'scale' => '挂单比例',
          'buypoundage' => '买家手续费',
          'sellpoundage' => '卖家手续费',
          'memory_day' => '锁定周期',
          'memory_back' => '解锁日返率',

      );
      foreach ($error as $k=>$v){
         if ($k==$value){
            return $v;
         }
      }
   }

   //判断非数字类型的名字
   private function getname2($value) {
      $error = array(
          'name'=>'币种名称',
          'text'=>'币种介绍',
          'address'=>'钱包地址',
          'author' => '研发者',
          'algorithm' => '核心算法',
          'blocktime' => '区块时间',
          'blockreward' => '区块奖励',
          'totalmoney' => '货币总量',
          'advantage' => '主要特色',
          'disadvantage' => '不足之处'
      );
      foreach($error as $k => $v) {
         if ($k == $value) {
            return $v;
         }
      }
   }

   //删除虚拟币、支持批量删除
   public function deletexnb(){
      $id = array_unique((array)I('id',0));
      $xnb_id = is_array($id) ? implode(',',$id) : $id;

      $xnb_m=  M('xnb');  //虚拟币
      $userproperty_m=M('userproperty');
      $xnb_address = M('xnbaddress');

      $map['id'] = array("in", $xnb_id);
      $map_address = array('xnbid' => $xnb_id);
      $xnb_bcak=$xnb_m->lock(true)->where($map)->field('brief,buycomplicated,sellcomplicated,imgurl')->select();  //将该条记录锁死
      $property = 0;
      foreach ($xnb_bcak as $key => $value) {
         $property +=$userproperty_m->sum($value['brief']);
      }

      if ($property>0){
         $this->error('用户拥有该虚拟币资产，无法删除！');
         exit();
      }
      $xnb_m->startTrans();

      $delete_back=$xnb_m->where($map)->delete();
      $delete_address = $xnb_address -> where($map_address) -> delete();

      foreach ( $xnb_bcak as $key => $value ) {
         $sql='ALTER TABLE `currency_userproperty` DROP COLUMN `'.$value['brief'].'`';
         $property_back=$userproperty_m->execute($sql);

         $fiel=unlink($value['buycomplicated']);
         $fiels=unlink($value['sellcomplicated']);
         $delimg = unlink($value['imgurl']);
         if ($delete_back==false || $property_back==false || $fiel==false || $fiels==false || $delimg == false || $delete_address === false){
            $xnb_m->rollback();
            $this->error('删除失败！请联系管理员');
            exit;
         }
      }
      $xnb_m->commit();
      $this->success('删除成功！');
   }

   //虚拟币启用、禁用
   public function statusxnb($method=null) {
      $id = array_unique((array)I('id',0));
      $id = is_array($id) ? implode(',',$id) : $id;
      if ( empty($id) ) {
         $this->error('请选择要操作的数据!');
      }
      $map['id'] =   array('in',$id);
      switch ( strtolower($method) ){
         case 'forbiduser':
            //判断是否可以禁用
            $xnb_bcak=M("xnb")->lock(true)->where($map)->field('brief,buycomplicated,sellcomplicated')->select();  //将该条记录锁死
            $property = 0;
            foreach ($xnb_bcak as $key => $value) {
               $property += M('userproperty')->sum($xnb_bcak['brief']);
            }

            if ($property>0){
               $this->error('用户拥有该虚拟币资产，无法禁用！');
               exit();
            }
            $this->forbid('xnb', $map );
            break;
         case 'resumeuser':
            $this->resume('xnb', $map );
            break;
         default:
            $this->error('参数非法');
      }
   }

   //虚拟币地址列表
   public function xnbaddress() {
      $search = $this -> strFilter(I("search")) ? $this -> strFilter(I("search")) : "";
      $map['x.name'] = array("like", "%". $search ."%");
      $xnbaddress = M()
          -> table("currency_xnbaddress as xa")
          -> join("left join currency_xnb as x on xa.xnbid = x.id")
          -> where($map)
          -> field("xa.*, x.name")
          -> select();
      $this -> assign("xnbaddress", $xnbaddress);
      $this -> display();
   }

   //虚拟币地址添加/修改
   public function addxnbaddress() {
      $id = $this -> strFilter(I("id")) ? $this -> strFilter(I("id")) : "";
      $str_notice = "添加";
      if ($id != "") {
         $str_notice = "修改";
         $data = M("xnbaddress") -> where(array("id" => $id)) -> find();
         $this -> assign("data", $data);
      }
      $xnblist = M("xnb") -> field("id, name") -> where("id <> 1") -> select();
      if (IS_POST) {
         $data['xnbid'] = $this -> strFilter(I("xnbid")) ? $this -> strFilter(I("xnbid")) : "";
         $data['downadd'] = base64_encode(I("downadd")) ? base64_encode(I("downadd")) : "";
         $data['webadd'] = base64_encode(I("webadd")) ? base64_encode(I("webadd")) : "";
         $data['status'] = $this -> strFilter(I("status")) ? $this -> strFilter(I("status")) : "";
         $table = M("xnbaddress");

         if ($id != "") {
            $res = $table -> where(array("id" => $id)) -> save($data);
         } else {
            $res = $table -> add($data);
         }
         if ($res) {
            $this -> success($str_notice. "成功");
         } else {
            $this -> error( $str_notice. "失败");
         }
      }
      $this -> assign("xnblist", $xnblist);
      $this -> display();
   }

   //虚拟币地址删除
   public function delxnbaddress() {
      $id = $this -> strFilter(I("id")) ? $this -> strFilter(I("id")) : "";
      $res = M("xnbaddress") -> where(array("id" => $id)) -> delete();
      if ($res) {
         $this -> success("删除成功");
      } else {
         $this -> error("删除失败");
      }
   }

   //虚拟币地址状态修改与批量删除
   public function statusxnbaddress($method=null) {
      $id = array_unique((array)I('id',0));
      $xnb_id = is_array($id) ? implode(',',$id) : $id;
      $map['id'] =   array('in',$xnb_id);

      if (IS_POST){
         switch ( strtolower($method) ){
            case 'forbid':
               $this->forbid('xnbaddress', $map );
               break;
            case 'resume':
               $this->resume('xnbaddress', $map );
               break;
            case 'delete':
               $res = M('xnbaddress') -> where($map) -> delete();
               if ($res) {
                  $this -> success("删除成功");
               } else {
                  $this -> error("删除失败");
               }
               break;
            default:
               $this->error("参数错误");
         }
      }
   }

   protected function strUrl($str,$type=false,$error="含有非法字符请重输"){
      if($type){
         if($str==""){
            $this->error($error);
         }
      }
      $reg=" /\ |\￥|\……|\、|\‘|\’|\；|\：|\【|\】|\（|\）|\！|\·|\-|\/|\~|\!|\@|\#|\\$|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\/|\;|\'|\`|\-|\=|\\\|\|/";
      //允许通过的特殊字符   。，《 》 “ ”
      $REGold=preg_match($reg,$str);
      if($REGold==1){
         $this->error($error);
      }else{
         return $str;
      }
   }
}
