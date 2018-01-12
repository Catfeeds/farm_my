/**
 * Created by 杨尚云 on 2017/6/24.
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
/**
 * Created by 杨尚云 on 2017/6/24.
 */
$(document).ready(function () {
    $(".listTurn_click").click= function () {
        $(".listTurn_click").removeClass("listActive");
        $(this).addClass("listActive");

    }
//
});