$(function () {
    //底部导航
    being()
    function being() {
        $("#home img").attr('src', '/Public/Wap/img/shouye2.png');
        $("#home").addClass("ce25217");
        $("#deal img").attr('src', '/Public/Wap/img/jiaoyi2.png');
        $("#deal").addClass("ce25217");
        $("#message img").attr('src', '/Public/Wap/img/zixun2.png');
        $("#message").addClass("ce25217");
        $("#personage img").attr('src', '/Public/Wap/img/geren2.png');
        $("#personage").addClass("ce25217");

    }

    //底部导航
    $("input,textarea").focus(function () {
        $(".railing").hide();
        $("section").addClass("marginbottom0");
    })
    $("input,textarea").blur(function () {
        $(".railing").show();
        $("section").removeClass("marginbottom0");
    })


//移动端解决事件延迟
//$(function () {
//      window.addEventListener("load", function () {
//          FastClick.attach(document.body);
//      }, false);
//})
    
//提示框

    $(function () {
        //所有的返回按钮
        $("#back").click(function () {
            window.history.back();

        })
    })


});
//提示框
function ShowHintBox(hint, a) {
    var a = arguments[1] ? arguments[1] : false;//设置参数a的默认值为false
    var $prompt_box = $(".prompt_box");//提示框
    var $cover = $(".cover");//遮罩
    var $hint = $(".prompt_box_hint");//提示内容
    var $btn = $(".prompt_box_btn");//按钮内容;

    $hint.html(hint);
    $prompt_box.show();
    $cover.show();
    $(document).bind("touchmove", function (e) {
        e.preventDefault();
    });

    $btn.click(function () {
        $prompt_box.hide();
        $cover.hide();
        if (a) {
            window.location.reload();
        }
        $(document).unbind("touchmove");
    });
};



