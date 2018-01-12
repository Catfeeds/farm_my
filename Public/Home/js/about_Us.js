/**
 * Created by 杨尚云 on 2017/6/28.
 */

$(document).ready(function () {
    /*left-nav fixed*/
    var $container=$("#about_content")
        ,$nav=$("#nav_left");

    $(window).scroll(function () {
        var height=$(document).scrollTop()
            ,bottom=$(document).height()-$(window).height()
            ,x=$container.offset().left;
        if(height>310&&height<(bottom-300)){
            $nav.addClass("fix_left");
            $nav.css({"position":"fixed","top":"0","left":x})
        }else {
            $nav.removeClass("fix_left");
            $nav.css({"position":"static"})
        }
    });
    $(window).resize(function () {
        var x_resize=$container.offset().left;
        if($nav.hasClass("fix_left")){
            $nav.css({"left":x_resize})
        }

    });
});
