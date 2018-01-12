/**
 * Created by 杨尚云 on 2017/6/23.
 */

$(document).ready(function () {
    //位置完成
    $(window).scroll(function(){
        var scrollTop = $(this).scrollTop();//滚动高度
        var scrollHeight = $(document).outerHeight();//浏览器总高度
        var windowHeight = $(this).height();//网页可是区域高度
        var passageHeight=$("#passage_article").outerHeight();//文本高度
        var passage_content=$("#passage_content");
        //var passage_contentTop=passage_content.height;
        if(200>scrollTop) {
            $("#nav_left").css("margin-top",20+"px")
        }else if(200<scrollTop&&scrollTop<passageHeight){
            $("#nav_left").css("margin-top",scrollTop-200+"px")
        }
        //console.log(scrollTop+":"+windowHeight+":"+scrollHeight+":"+passageHeight)
    });

//侧边导航栏的激活
    $(".passage_nav_click").click(function () {
        $(".passage_nav_click").removeClass("passage_nav_active");
        $(this).addClass("passage_nav_active");
    });

});

//$(window).load(function () {
//    var passageHeight=$("#passage_article").outerHeight();//文本高度
//    var scrollTop = $(this).scrollTop();//滚动高度
//    var left_navHeight=$(".nav_left").outerHeight();//导航文本高度
//    var windowHeight = $(this).height();//网页可是区域高度
//
//    var ressultHeight=passageHeight-scrollTop-left_navHeight;
//    //if(ressultHeight>left_navHeight){
//    //    $(".nav_left").css("height",windowHeight+"px");
//    //}else {
//    //    if(ressultHeight>=left_navHeight){
//    //        $(".nav_left").css("height",left_navHeight+"px");
//    //    }else{
//    //        $(".nav_left").css("height",ressultHeight+"px");
//    //
//    //    }
//    //
//    //}
//
//});