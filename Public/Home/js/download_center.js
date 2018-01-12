$(document).ready(function () {
    // left list
    slide("#download_list", 10, 0, 180, .8);

    var $list=$("#download_list"),$li=$list.children("li");

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




    /*left-nav fixed*/
    var $container=$("#help_content")
        ,$nav=$("#help_nav");

    $(window).scroll(function () {
        var height=$(document).scrollTop()
            ,bottom=$(document).height()-$(window).height()
            ,x=$container.offset().left;
        if(height>160&&height<(bottom-400)){
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


    var $Arr = $("#panel_img").find("li")
        , index = 0;

    function change(index) {
        $Arr.css("display", "none");
        $($Arr[index]).fadeIn();
    }

    function autoChange() {
        index = index + 1;
        if (index < $Arr.length) {
            change(index)
        } else {
            index = 0;
            change(index)
        }
    }

    setInterval(autoChange, 3000)



});

