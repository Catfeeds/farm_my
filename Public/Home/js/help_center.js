$(document).ready(function () {
    $(".guide_content img").addClass("img-responsive center-block");
    // left list
    slide("#help_list", 10, 0, 180, .8);

    var $list=$("#help_list"),$li=$list.children("li");

    $("#center_title").click(function () {
        $list.toggle();
    });

    /*页面传参*/
    var url=window.location.href,index =url.indexOf("tag");
    if(index != -1){
        var tat= url.split("tag%60")[1].split(".")[0];
        $($li).removeClass("selected").addClass("similar");
        $($li[tat]).removeClass("similar").addClass("selected")
    }

    /*false_num table*/
    $("#false_num").find("tr:even").addClass("bg_grey");



    /*left-nav fixed*/
    var $container=$("#help_content")
        ,$nav=$("#help_nav");

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

