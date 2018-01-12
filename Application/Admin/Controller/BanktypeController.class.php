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
class BanktypeController extends AdminController {
    //银行卡配置
    public function index(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('banktype'); // 实例化Data数据对象  date 是你的表名
        $name=$this->strFilter(I('name'))?$this->strFilter(I('name')):"";
        $where['id']=array('like',"%".$name."%");;
        $where['bankname'] =array('like',"%".$name."%");
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
        $this->meta_title = '银行配置';
        $this->display();
    }

    //添加、修改银行配置
    function add(){
        $id['id']=$this->strFilter(I('id'))?$this->strFilter(I('id')):"";
        $model=M('banktype');

        if ( $_FILES['bankimg']['size'] > 0 ){
            import('ORG.Net.UploadFile');
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            //         $upload->rootPath  =     '/Publick/img'; // 设置附件上传根目录
            $upload->savePath  =     ''; // 设置附件上传（子）目录
            // 上传文件
            $infotop   =   $upload->upload(I('bankimg'));
            if ( $infotop ) {
                $data['bankimg']='Uploads/'.$infotop['bankimg']['savepath'].$infotop['bankimg']['savename'];   //文件路径
                if ($id!=""){    //
                    $imgurl_back = $model  -> where( array( 'id' => $id['id'] ) ) -> field('bankimg') -> find();
                    unlink( $imgurl_back[ 'bankimg' ] );
                }
            } else {
                $this -> error("上传文件失败");
            }
        }

        $data['status'] = $this -> strFilter(I('status')) ? $this -> strFilter(I('status')) : "";
        $data['bankname']=$this->strFilter(I('bankname'))?$this->strFilter(I('bankname')):"";
        $data['bankurl']=base64_encode(I('bankurl'))?base64_encode(I('bankurl')):"";

        if ($id['id']!=""){
            $redata=$model->where($id)->select();
            if ($redata){
                $this->assign("data",$redata);
            }
        }
        if(IS_POST){
            if ($id['id']!="" ){
                $save_redata=$model->where($id)->save($data);
                if ($save_redata){
                    $this->success("修改成功");
                }else{
                    $this->error("修改失败");
                }
            }else{
                $where['bankname']=$data['bankname'];
                $redata=$model->where($where)->select();

                if ($redata){
                    $this->error("银行名已存在");
                }else{
                    $data['addtime']=time();
                    $add_redata=$model->add($data);
                    if ($add_redata){
                        $this->success("添加成功");
                    }else{
                        unlink($data['bankurl']);
                        $this->error("添加失败");
                    }
                }

            }

        }


        $this->display();
    }

    //启用、禁用、批量删除
    public function statusChange($method = null) {
        $id = array_unique((array)I('id',0));
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] =   array('in',$id);
        $map_bank['bank'] = array('in', $id);
        $count_bank = M("bank") -> where($map_bank) -> count();
        $count_bankreceive = M("bankreceive") -> where($map_bank) -> count();
        $count = $count_bank + $count_bankreceive;
        switch ( strtolower($method) ){
            case 'forbid':
                //判断是否可以禁用
                if ($count > 0){
                    $this->error('该银行卡已被绑定，不可禁用');
                    exit();
                }
                $this->forbid('banktype', $map );
                break;
            case 'resume':
                $this->resume('banktype', $map );
                break;
            case 'delete':
                //删除要同时删掉bankimg
                if ($count > 0){
                    $this->error('该银行卡已被绑定，不可删除');
                    exit();
                }
                //查询bankimg
                $xnb_bcak=M("banktype")->lock(true)->where($map)->field('bankimg')->select();
                $res_del = M("banktype") -> where($map) -> delete();
                if ($res_del) {
                    foreach ($xnb_bcak as $key => $value) {
                        $img_del = unlink($value['bankimg']);
                        if ($img_del === false) {
                            M("banktype") -> rollback();
                            $this -> error("删除失败!1");
                            exit;
                        }
                    }
                    $this -> success("删除成功");
                } else {
                    $this -> error("删除失败!2");
                }
                break;
            default:
                $this->error('参数非法');
        }
    }
    protected function strUrl($str,$type=false,$error="含有非法字符请重输"){
        if($type){
            if($str==""){
                $this->error($error);
            }
        }
        $reg=" /\ |\￥|\……|\、|\‘|\’|\；|\：|\【|\】|\（|\）|\！|\·|\-|\/|\~|\!|\@|\#|\\$|\^|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\/|\;|\'|\`|\-|\=|\\\|\|/";
        //允许通过的特殊字符   。，《 》 “ ”
        $REGold=preg_match($reg,$str);
        if($REGold==1){
            $this->error($error);
        }else{
            return $str;
        }
    }
}
