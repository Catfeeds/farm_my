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
/**
 * 行为控制器
 * @author huajie <banhuajie@163.com>
 */
class FinanceController extends AdminController {
    //充值申请 初审
    public function cnyintint(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('rechargeapply'); // 实例化Data数据对象  date 是你的表名
        $id=$this->strFilter(I('id'));
        if (IS_POST && $id!=""){   //审核
            $Data->startTrans();
            $lock_back=$Data->lock(true)->where(array('id'=>$id))->find();
            if ($lock_back==false){
                $Data->rollback();
                $this->error('审核失败！');
            }
            //修改状态
            $save_back=$Data->where(array('id'=>$lock_back['id']))->save(array('status'=>1));
            if ($save_back==false){
                $Data->rollback();
                $this->error('审核失败！');
            }
//            action_log('currency_carryapply','currency_carryapply',$lock_back['orderfor'],UID,$lock_back['id'],'《初审》《通过》，'); //第一个id修改那条数据
            $Data->commit();
            $this->success('审核成功！');
            exit();
        }
        $Finance=new FinancemethodController();
        $Finance->cnyint_page($Data,0);
        $this->display(); // 输出模板
    }
    //人名币充值复审
    public function cnyintVerify(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('rechargeapply'); // 实例化Data数据对象  date 是你的表名
        $id=$this->strFilter(I('id'));
        $Financeme= new FinancemethodController();
        if (IS_POST && $id!=""){   //审核
            $Financeme->cnyintint_fuse($id,$Data,3,'《复审》《通过》');
        }
        $Financeme->cnyint_page($Data,1);

        $this->display(); // 输出模板
    }
    //人民币充值拒绝
    public function cnyintfase(){
        $id=$this->strFilter(I('id'));
        if (IS_POST && $id!=''){
            $Data =   M('rechargeapply'); // 实例化Data数据对象  date 是你的表名
            $Financeme= new FinancemethodController();
            $Financeme->cnyintint_fuse($id,$Data,2,'《复审》《拒绝》');
        }
    }
    //人民币提现申请  初审
    public function cnyintout(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('carryapply'); // 实例化Data数据对象  date 是你的表名
        $id=$this->strFilter(I('id'));
        if (IS_POST && $id!=""){   //审核
            $Data->startTrans();
            $lock_back=$Data->lock(true)->where(array('id'=>$id))->find();
            if ($lock_back==false){
                $Data->rollback();
                $this->error('审核失败！');
            }
            //修改状态
            $save_back=$Data->where(array('id'=>$lock_back['id']))->save(array('status'=>2));
            if ($save_back==false){
                $Data->rollback();
                $this->error('审核失败！');
            }
            action_log('currency_carryapply','currency_carryapply',$lock_back['orderfor'],UID,$lock_back['id'],'《初审》《通过》，'); //第一个id修改那条数据
            $Data->commit();
            $this->success('审核成功！');
            exit();
        }
        $Finance=new FinancemethodController();
        $Finance->cnyout_page($Data,1);
        $this->display(); // 输出模板

    }

    //人民币提现申请  复审
    public function cnyintoutVerify(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('carryapply'); // 实例化Data数据对象  date 是你的表名
        $id=$this->strFilter(I('id'));

        $Financeme= new FinancemethodController();
        if (IS_POST && $id!=""){   //审核
            $Financeme->cnyintout_fuse($id,$Data,3,'《复审》《通过》');
        }
        $Financeme->cnyout_page($Data,2);
        $this->display(); // 输出模板

    }

    //人民币提现拒绝地址
    public function cnyintoutfase(){
        $id=$this->strFilter(I('id'));
        if (IS_POST && $id!=''){
            $Data =   M('carryapply'); // 实例化Data数据对象  date 是你的表名
            $Financeme= new FinancemethodController();
            $Financeme->cnyintout_fuse($id,$Data,4,'《复审》《拒绝》');
        }
    }

    //人民币提现配置
    public function cnyconfigure(){
        $cnyconfigure=M('cnyconfigure');
        if (IS_POST){
            $save_data['id']=1;
            $save_data['minmoney']=(I('minmoney'));
            $save_data['maxmoney']=(I('maxmoney'));
            $save_data['times']=(I('times'));
            $save_data['fastpoundage']=(I('fastpoundage'));
            $save_data['slowpoundage']=(I('slowpoundage'));
            if ($save_data['minmoney'] >= $save_data['maxmoney']) {
                $this -> error("最小提现金额不可以大于或等于最大提现金额");
                exit();
            }

//            $save_data['maxoder']=(I('maxoder'));
//            $save_data['poundatatype']=(I('poundatatype'));
//            $save_data['poundata']=(I('poundata'));
            foreach ($save_data as $k=>$v){
                if (check_number($v)!=$v){
                 
                    $this->error('非法字符');
                }
            }
                $save_back=$cnyconfigure->save($save_data);

            if ($save_back===false){
                $this->error("保存失败！");
                exit();
            }
            $this->success("保存成功！");
            exit();
        }

        $data=$cnyconfigure->find();
        $this->assign('data',$data);
        $this->display();
    }

    //虚拟币转入申请 初审
    public function xnbint(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('xnbrollin'); // 实例化Data数据对象  date 是你的表名
        $financeMethod=new FinancemethodController();
        $id=$this->strFilter(I('id'))?$this->strFilter(I('id')):"";
        if (IS_POST && $id!=""){   //虚拟币初审方法
            $Data->startTrans();

            $lock_back=$Data->lock(true)->where(array(   //将该条记录锁死
                'id'=>$id
            ))->find();
            if ($lock_back==false){
                $Data->rollback();
                $this->error('审核失败！1');
                exit();
            }
            $id_back=$Data->where(array(     //状态
                'id'=>$id
            ))->save(array('status'=>2,'towtime'=>time()));

            action_log('currency_xnbrollin','currency_xnbrollin',$lock_back['orderfor'],UID,$lock_back['id'],'《初审》《通过》，');

            if ($id_back==false){
                $Data->rollback();
                $this->error('审核失败！2');
                exit();
            }
            $Data->commit();
            $this->success('审核成功！');
            exit();
        }

        $financeMethod->paging_data($Data,1);
        $this->display(); // 输出模板
    }

    //虚拟币转入申请 复审
    public function xnbintVerify(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('xnbrollin'); // 实例化Data数据对象  date 是你的表名
        $FinanceMethod=new FinancemethodController();

       if (IS_POST){
           $FinanceMethod->xnbintrefuse(3,'《复审》《通过》');
       }
        $FinanceMethod->paging_data($Data,2);
        $this->display(); // 输出模板
    }

    //虚拟币转入申请 复审方法（拒绝）
    public function xnbintrefuse($type=4,$text='《复审》《拒绝》'){
        $FinanceMethod=new FinancemethodController();
        $FinanceMethod->xnbintrefuse(4,$text);
    }

    //虚拟币转出 初审
    public function xnbout(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('xnbrollout'); // 实例化Data数据对象  date 是你的表名
        $FinanceMethod=new FinancemethodController();
        $id=$this->strFilter(I('id'))?$this->strFilter(I('id')):"";
        if (IS_POST && $id!=""){
            $Data->startTrans();
            $lock_back=$Data->lock(true)->where(array(   //将该条记录锁死
                'id'=>$id
            ))->find();
            if ($lock_back==false){
                $Data->rollback();
                $this->error('审核失败！1');
                exit();
            }

            $id_back=$Data->where(array(     //状态
                'id'=>$id
            ))->save(array('status'=>2,'towtime'=>time()));

            action_log('currency_xnbrollin','currency_xnbrollin',$lock_back['orderfor'],UID,$lock_back['id'],'《初审》《通过》，');

            if ($id_back==false){
                $Data->rollback();
                $this->error('审核失败！2');
                exit();
            }
            $Data->commit();
            $this->success('审核成功！');
            exit();
        }

        $FinanceMethod->paging_data_out($Data,1);
        $this->display(); // 输出模板
    }

    //虚拟币转出 复审
    public function xnboutVerify(){
        import('ORG.Util.Page');// 导入分页类
        $Data =   M('xnbrollout'); // 实例化Data数据对象  date 是你的表名
        $FinanceMethod=new FinancemethodController();
        if (IS_POST){
            $FinanceMethod=new FinancemethodController();
            $FinanceMethod->xnbintrefuse_out(3,'《复审》《通过》');
        }

        $FinanceMethod->paging_data_out($Data,2);
        $this->display(); // 输出模板
    }

    //虚拟币装出的拒绝方法
    public function xnboutrefuse(){
        $FinanceMethod=new FinancemethodController();
        $FinanceMethod->xnbintrefuse_out(4,'《复审》《拒绝》');
    }

    //人民币收款地址
    public function cnyreceivables() {
        $bankrecevie = M("bankreceive");
        $banktype = M("banktype");
        $id = I("id") ? I("id") : "";

        $bank = $banktype -> where("status = 1")  -> field("id, bankname") -> select();
        $bankcard =$bankrecevie -> field("id, bank, bankcard, sort, payee") -> order("sort desc") -> where("status = 1") -> select();

        //如果当前没有默认地址，则设第一个地址为默认地址
        $default_count = $bankrecevie -> field("sort") -> where("sort = 1") -> count();
        if ($bankcard && $default_count <= 0) {
            $res = $bankrecevie -> where("id = ". $bankcard[0]['id']) -> save(array("sort" => 1));
            if (!$res) {
                $this -> error("设置默认失败");
            }
        }
        //添加、修改地址
        if ($_POST) {
            $data['bank'] = I("bankid");
            $data['bankcard'] = I("bankcard");
            $data['payee'] = $this -> strFilter(I("payee")) ? $this -> strFilter(I("payee")) : "";
            $data['addtime'] = time();
            $data['endtime'] = time();
            $data['status'] = 1;
            if ($id != "") {
                $res = $bankrecevie -> where(array("id" => $id)) -> save($data);
                $msg = "修改";
            } else {
                $res = $bankrecevie -> add($data);
                $msg = "添加";
            }
            if ($res) {
                $this -> success($msg. "成功");
            } else {
                $this -> error($msg. "失败");
                exit;
            }
        }
        $this -> assign("bankcard", $bankcard);
        $this -> assign("bank", $bank);
        $this -> display();
    }

    //点击删除人民币收款地址
    public function deletereceivables() {
        $id = I("id");

        if (M("bankreceive") -> where("id = ". $id) -> delete()) {
            $this -> success("删除成功");
        } else {
            $this -> error("删除失败");
        }
    }

    //设置默认
    public function setdefault() {
        $id = I("id");

        $res = M("bankreceive") -> where("status = 1") -> save(array("sort" => 0));
        if ($res !== false) {
            $res2 = M("bankreceive") -> where("id = ". $id) -> save(array("sort" => 1));
            if ($res2 !== false) {
                $this -> success("设置成功");
            } else {
                $this -> error("设置失败1");
            }
        } else {
            $this -> error("设置失败2");
        }
    }

    //修改
    public function editreceivables() {
        $id = $this -> strFilter(I("id")) ? $this -> strFilter(I("id")) : "";
        //查询要修改的地址信息
        if ($id != "") {
            $bankedit = M("bankreceive") -> where(array("id" => $id)) -> field("id, bank, bankcard, payee") -> find();
            echo json_encode($bankedit);
        }
    }
}
