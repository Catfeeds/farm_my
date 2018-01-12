$(function () {
    ( function () {
        var $nav=$("#nav");
        $nav.on("mouseenter",".similar",function (e) {
            $(this).find("a").stop(true).animate({top:"-50px"},100);
        }).on("mouseleave",".similar",function (e) {
            $(this).find("a").stop(true).animate({top:"0"},100);
        })
    })();


//置顶按钮和右侧显示框
    (function () {

        var $weixin=$("#weixin_ewm"),$ewm=$("#ewm_img"),$contact=$("#contact_wid"),$wid=$("#contact_enter"),$close=$("#contact_wid_close");
        $("#right_float").on("mouseenter","div.t-hand",function () {
            var $this_id=$(this).attr("id");
            switch ($this_id){
                case "contact_wid":
                    $wid.show();
                    $(this).addClass("selected");

                    break;
                case "weixin_ewm":

                    $ewm.show();
                    $(this).addClass("selected");
                    break;
                case "footerConnect":
                    $(this).addClass("selected");
                    break;
                case "footerHelp":
                    $(this).addClass("selected");
                    break;
            }
        }).on("mouseleave","div.t-hand",function () {
            var $this_id=$(this).attr("id");
            switch ($this_id){
                case "contact_wid":

                    $wid.hide();
                    $(this).removeClass("selected");
                    break;
                case "weixin_ewm":
                    $ewm.hide();
                    $(this).removeClass("selected");
                    break;
                case "footerConnect":
                    $(this).removeClass("selected");
                    break;
                case "footerHelp":
                    $(this).removeClass("selected");
                    break;
            }

        });
        $ewm.on({
            mouseenter:function () {
                $weixin.trigger("mouseenter")
            },
            mouseleave:function () {
                $weixin.trigger("mouseleave")
            }
        });
        $wid.on({
            mouseenter:function () {
                $contact.trigger("mouseenter")
            },
            mouseleave:function () {
                $contact.trigger("mouseleave")
            }
        });
        $close.click(function () {
            $contact.trigger("mouseleave")
        });
        /*er wei ma*/
       /* var $weixin=$("#weixin_ewm"),$ewm=$("#ewm_img");
        $weixin.on({
            mouseenter:function () {
                $ewm.show();
                $(this).addClass("selected")
            },
            mouseleave:function () {
                $ewm.hide();
                $(this).removeClass("selected")
            }
        });
        $ewm.on({
            mouseenter:function () {
                $weixin.trigger("mouseenter")
            },
            mouseleave:function () {
                $weixin.trigger("mouseleave")
            }
        });
        /!*liaotianshi*!/
        var $contact=$("#contact_wid"),$wid=$("#contact_enter"),$close=$("#contact_wid_close");
        $contact.on({
            mouseenter:function () {
                $wid.show();
                $(this).addClass("selected")
            },
            mouseleave:function () {
                $wid.hide();
                $(this).removeClass("selected")
            }
        });
        $wid.on({
            mouseenter:function () {
                $contact.trigger("mouseenter")
            },
            mouseleave:function () {
                $contact.trigger("mouseleave")
            }
        });
        $close.click(function () {
            $contact.trigger("mouseleave")
        });*/


        /*to_top*/
        var $top=$("#to_top");
        $(window).scroll(function () {
            var w_height = $(window).height();//浏览器高度
            var scroll_top = $(document).scrollTop();//滚动条到顶部的垂直高度
            if (scroll_top > w_height) {
                $top.fadeIn(500);
            } else {
                $top.fadeOut(500);
            }
        });

        //置顶
        $top.click(function () {
            $("body,html").animate({
                scrollTop: 0
            }, 200);
            return false;
        });
    })();

    /*delay alert*/

    showLockScreen('#lock_screen_delay',"#delay_form_out","#delay_btn")

});/**
 * Created by Administrator on 2017/6/15.
 */

function showLockScreen(lockDiv, alertDiv, closeBtn) {
    var x = $(document).width();
    var y = $(document).height();
    $(lockDiv).css({"width": x, "height": y, "display": "block"});

    $(window).resize(function () {
        if ($(lockDiv).css("display") == "block") {
            var a = $(document).width();
            var b = $(document).height();
            $(lockDiv).css({"width": a, "height": b});
        }
    });
    $(lockDiv).click(function () {
        $(alertDiv).hide();
    });
    $(closeBtn).click(function () {
        $(lockDiv).trigger("click")
    });
	setTimeout(function () {
        $(alertDiv).hide();
    },5000)
}
//按钮
$(function(){
	$(".timing button").click(function(){
		$(this).addClass("activebtn").siblings().removeClass("activebtn");
	})
})
//聊天室自适应高度
$(function(){
	var $browserHeight=$(window).height();//浏览器高度
	var	$roomList=$(".room-list");//聊天区域
		if($browserHeight<300){
			$roomList.css({"height":"20%"})
		}
		else  if($browserHeight<350){
			$roomList.css({"height":"30%"})
		}
		else if($browserHeight<400){
			$roomList.css({"height":"40%"})
		}
		else if($browserHeight<450){
			$roomList.css({"height":"45%"})
		}
		else if($browserHeight<500){
			$roomList.css({"height":"52%"})
		}
		else if($browserHeight<550){
			$roomList.css({"height":"57%"})
		}
		else if($browserHeight<600){
			$roomList.css({"height":"62%"})
		}
		else if($browserHeight<650){
			$roomList.css({"height":"64%"})
		}
		else if($browserHeight<700){
			$roomList.css({"height":"66%"})
		}
		else if($browserHeight<750){
			$roomList.css({"height":"69%"})
		}
		else if($browserHeight<800){
			$roomList.css({"height":"72%"})
		}
		else if($browserHeight<850){
			$roomList.css({"height":"74%"})
		}
		else if($browserHeight<900){
			$roomList.css({"height":"75%"})
		}
		else{
			$roomList.css({"height":"77%"})
		}
	$(window).resize(function(){
		var $browserHeight=$(window).height();
		if($browserHeight<300){
			$roomList.css({"height":"20%"})
		}
		else  if($browserHeight<350){
			$roomList.css({"height":"30%"})
		}
		else if($browserHeight<400){
			$roomList.css({"height":"40%"})
		}
		else if($browserHeight<450){
			$roomList.css({"height":"45%"})
		}
		else if($browserHeight<500){
			$roomList.css({"height":"52%"})
		}
		else if($browserHeight<550){
			$roomList.css({"height":"57%"})
		}
		else if($browserHeight<600){
			$roomList.css({"height":"62%"})
		}
		else if($browserHeight<650){
			$roomList.css({"height":"64%"})
		}
		else if($browserHeight<700){
			$roomList.css({"height":"66%"})
		}
		else if($browserHeight<750){
			$roomList.css({"height":"69%"})
		}
		else if($browserHeight<800){
			$roomList.css({"height":"72%"})
		}
		else if($browserHeight<850){
			$roomList.css({"height":"74%"})
		}
		else if($browserHeight<900){
			$roomList.css({"height":"75%"})
		}
		else{
			$roomList.css({"height":"77%"})
		}
	})

})