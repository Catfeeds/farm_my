<?php
namespace Wap\Controller;

class CommunicationController extends WapController {
    //在线客服
    public function service() {
        //查询常用问题
        $question = M("texttype") -> field('id') -> where('toptype = 16') -> select();
        $questionid = "";
        foreach ($question as $key => $value) {
            $questionid .= $value['id'].',';
        }
        $map['tt.id'] = array("in", $questionid);
        $map['t.status'] = 1;
        $list = M()
            -> table("currency_text as t")
            -> join("left join currency_texttype as tt on t.type = tt.id")
            -> field("t.*, tt.title as type")
            -> where($map)
            -> order("t.sort desc")
            -> limit(6)
            -> select();
        $this -> assign("question", $list);
        $this->display();
    }
    /*
     * 客服常用问题答案
     */
    public function answer() {
        $helpid = I("helpid");

        $data = M("text") -> where("id = ". $helpid) -> field("type, text") -> find();

        $sametype = M("text") -> where("type = ". $data['type'] ." and id <> ". $helpid. " and status = 1") -> field("id, title") -> limit(6) -> select();
        $data['sametype'] = json_encode($sametype);
        $data = json_encode($data);
        $this -> ajaxReturn($data);
    }
}