<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/3 0003
 * Time: 上午 9:23
 */

namespace Admin\Controller;


use Think\Exception;
use Think\Page;

class DistributeController extends AdminController
{
    /**
     * 推广配置
     */
    function index(){
        $distribute_m=M('distribute');
        if (IS_POST){
            $data['data']=I('data');
            if ($data['data']===""){
                $this->error('非法操作！');
                exit();
            }

            $sava_back=$distribute_m->where(array('id'=>1))->save($data);
            if ($sava_back===false){
                $this->error('保存失败！');
                exit();
            }
            $this->success('保存成功！');
            exit();
        }
        $set_data=$distribute_m->where(['id'=>1])->find();
        $set_data['data']=json_decode($set_data['data'],true);
        ksort($set_data['data']);
        $this->assign('data',$set_data['data']);
        $this->display();
    }


    /**
     *新币配置
     */
    function nameInfo(){
        $distribute_m=M('distribute');
        if (IS_POST){

            try{

                $data=[];
               if ($_FILES['wx_img']['size']>0){

                   if (!empty(I('old_url'))){

                       $back = unlink(I('old_url'));

                       if (!$back){
                           throw new Exception('删除文件失败！');
                       }

                   }

                   $upload = new \Think\Upload();// 实例化上传类
                   $upload->maxSize   =     3145728 ;// 设置附件上传大小
                   $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                   $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
                   // 上传单个文件
                   $info   =   $upload->uploadOne($_FILES['wx_img']);
                   if(!$info) {// 上传错误提示错误信息
                       throw new Exception($upload->getError());
                   }// 上传成功 获取上传文件信息    $info['savepath'].$info['savename'];

                   $data['img'] = 'Uploads/'.$info['savepath'].$info['savename'];

               }

               $data['name']= I('name');
               $data['brief'] = I('brief');
               $data['saver_name'] = I('saver_name');
               $data['img'] = 'Uploads/'.$info['savepath'].$info['savename'];

               foreach ( $data as $v){
                   if (empty($v)){
                       throw new Exception('非法操作！');
                   }
               }


                $data=json_encode($data);

                $sava_back=$distribute_m->where(array('id'=>2))->save(['data'=>$data]);
                if ($sava_back===false){
                    throw new Exception('保存失败！');
                }
                return    $this->success('保存成功！');
            }catch (\Exception $e){
               return    $this->error($e->getMessage());
            }

        }
        $set_data=$distribute_m->where(['id'=>2])->find();
        $set_data['data']=json_decode($set_data['data'],true);
        ksort($set_data['data']);
        $this->assign('data',$set_data['data']);
        $this->display();
    }


    /**
     * 赠送记录
     */

    function present(){

        $where = $page_where = [];

        if (!empty(I('users'))){
            $where['prent.users'] = I('users');
            $page_where['users'] = I('users');
        }

        if (!empty(I('start_time')) && !empty(I('end_time'))){
            $where['currency_userindex.time'] =[ ['egt',strtotime(I('start_time'))] ,['elt',strtotime(I('end_time'))+86400]];
            $page_where['start_time'] = I('start_time');
            $page_where['start_time'] = I('end_time');
        }



        $userindex_m  =  M('userindex');

        $count = $userindex_m->where($where)->count();

        $page = new Page($count,15,$page_where);

        $show = $page->show();

        $data=  $userindex_m->where($where)
                            ->field('currency_userindex.*,prent.users as prent,child.users as child ')
                            ->join('left join currency_users as prent on currency_userindex.user_id = prent.id')
                            ->join('left join currency_users as child on currency_userindex.child_id = child.id')
                            ->limit($page->firstRow,$page->listRows)
                            ->select();

        $this->assign('page',$show);
        $this->assign('data',$data);

        $this->display();
    }

}