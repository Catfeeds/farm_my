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
class XnbaddrController extends AdminController
{
   public function index()
   {
      $nxb_m = M('xnb');
      $xnb_data = $nxb_m->where(['id' => ['neq', 1]])->field('id,name')->select();
      $this->assign('xnb_data', $xnb_data);
      $this->display();
   }
   function addrnum(){
      $nxb_m = M('address');
      $where['xnb']=I('xnb')?I('xnb'):62;
      $where['userid']=array("eq",0);
      $xnb_data = $nxb_m->where($where)->field('id')->select();
      $xnb_no = $nxb_m->where(['userid' => ['neq', 0],'xnb' => $where['xnb']])->field('id')->select();
      $data['no']=count($xnb_data);
      $data['yes']=count($xnb_no);
      $data['zong']=count($xnb_no)+count($xnb_data);
      $this->ajaxReturn($data);
   }
   public function topfile()
   {
      $file = $_FILES['file'];
      $xnb = I('xnb');

      if ($file['size']<=0){
         $this->error('非法文件');
         exit();
      }

      //验证虚拟币是否合法！
      if (positive($xnb) != 1) {
         $this->error('非法字符');
         exit();
      }

      $xnb_m = M('xnb');
      $check_xnb = $xnb_m->field('id')->where(['id' => $xnb])->find();
      if ($check_xnb['id'] == "") {
         $this->error('虚拟币不存在！');
         exit();
      }
      $pash='./Public/lock/importfile.txt';
      $fp = fopen($pash,'r+');     //文件锁解决并发，脏读问题！每个币种有独立的文件，用于分流不同币种的并发和脏读
      if (flock($fp,LOCK_EX)){
         $address_m=M('address');
         $check_edition=$address_m->field('id')->where(['edition'=>$file['name']])->find();
         if ($check_edition['id']!=""){
            $this->error('该文件已被上传！请勿重复提交数据！');
            exit();
         }

         $data=$this->import($file,$xnb);

         $ad_back=$address_m->addall($data);
         if ($ad_back==false){
            $this->error('上传失败!');
            exit();
         }

         $this->success("上传成功！");
         fclose($fp);//解锁
         exit();
      }else{
         $this->error('系统文件（importfile.txt）不存在!');
      }

   }


   //excle函数处理
   public function import($file,$xnb_id)
   {
      vendor('PHPExcel.PHPExcel');

      $objPHPExcel = \PHPExcel_IOFactory::load($file['tmp_name']);

      $currentSheet = $objPHPExcel->getSheet(0); //第一个工作薄

      $allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
      $allRow = $currentSheet->getHighestRow(); //取得一共有多少行

      $k=$data = array();
      for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {

         /**从第A列开始输出*/
         for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {

            $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65, $currentRow)->getValue();

            if ($currentColumn=="A"){
               $k=[];
               $k['label']= $val?$val:"";
            }

            if ($currentColumn=="B"){
               $k['address']=$val?$val:"";
               $k['xnb']=$xnb_id;
               $k['edition']=$file['name'];//版本标示防止重复提交
               $k['time']=time();//版本标示防止重复提交
               array_push($data,$k);
            }
            /**ord()将字符转为十进制数*/
//                $val=iconv('utf-8','gb2312', $val);

            /**如果输出汉字有乱码，则需将输出内容用iconv函数进行编码转换，如下将gb2312编码转为utf-8编码输出*/
            //echo iconv('utf-8','gb2312', $val)."\t";
         }
      }
      return $data;
   }


}

