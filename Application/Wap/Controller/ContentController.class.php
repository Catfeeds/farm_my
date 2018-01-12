<?php
namespace Wap\Controller;

use Think\Controller;

class ContentController extends WapController {
    //关于我们
    public function about() {
        $about = M("text") -> where("id = 12") -> field("title, text") -> find();

        $this -> assign("about", $about);
        $this -> display();
    }
    //用户注册协议
    public function registrationProtocol() {
        $register = M("text") -> where("id = 15") -> field("title, text") -> find();

        $this -> assign("register", $register);
        $this -> display();
    }
}