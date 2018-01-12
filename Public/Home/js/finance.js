$(function () {
    /*left list*/
    slide("#finance_list", 10, 0, 180, .8);
    var $list=$("#finance_list"),$li=$list.children("li");
    $("#center_title").click(function () {
        $list.toggle();
    });
    /*left nav change*/
    var url=window.location.href,index=url.indexOf("tag");
    if(index != -1){
        var tat= url.split("tag%60")[1].split(".")[0];
        $li.removeClass("selected").addClass("similar");
        $($li[tat]).removeClass("similar").addClass("selected")
        
    }

    //tip show
    $("#info_question").on("mouseenter",function () {
        $(this).find("em").show()
    }).on("mouseleave",function () {
        $(this).find("em").hide()
    });

    // xnb classify show
    var iftrue=false;
    var ifin=false;

    $("#xnb").on({
        "mouseenter":function () {
            ifin=true;
        $("#xnb_table").show();
        $(this).addClass("selected")
    },
        "mouseleave":function () {
            ifin=false;
            setTimeout(function () {
                if(iftrue==false&&ifin==false){
                    $("#xnb_table").hide();
                    $("#xnb").removeClass("selected")
                }
            },100);
        }
    });

$("#xnb_table").on("click","td",function () {
    $("#xnb").children(".xnb_content").html($(this).html());
    $('input[name=xnb]').val($(this).attr('id'));
    $("#xnb_table").hide();

}).on({
    "mouseenter":function () {
        iftrue=true;
        $("#xnb").trigger("mouseenter")
    },
    "mouseleave":function () {
        iftrue=false;
        $("#xnb_table").hide();
        $("#xnb").removeClass("selected")
    }
});

    //充值方式
/*    $("#ToRecharge").click(function(){
        $("#PrepaidBox").show();
    });*/
/*    $(".prepaid-box-close").click(function(){
        $("#PrepaidBox").removeClass("BouncedEffect");
    });*/

    /****
     * 人民币提现认证
     *****/
    $("#rmbtx").click(function () {
        $.ajax({
            url:"/Home`Property`rmbwithdrawal",
            type:"post",
            success:function (data) {
                
            }
        })
    });



    /****
     * 人民币提现认证
     *****/
    $("#rmbtx").click(function () {
        $.ajax({
            url:"/Home`Property`rmbwithdrawal",
            type:"post",
            success:function (data) {

            }
        })
    });
    /*弹出关闭*/
    $("#curr_close_in").click(function () {
        $('#PrepaidBox').hide();
    });

    $("#curr_close_in_card").click(function () {
        $('#bank_recharge').hide();
    });
    $("#curr_close_in_wx").click(function () {
        $('#wechat_show').hide();
    });

    $("#curr_close_out").click(function () {
        $('#withdrawal_record').hide();
    });

/*人民币提示框定时器*/






});

/*keyup yanzheng*/
function tradePwReg(ss) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            olddeal=1;
        }else{
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}









/*	ListStyle()

    function ListStyle(){
	  	  $("#finance").parent().addClass("pitchList").siblings().removeClass("pitchList");
	      $("#finance").addClass("pitchList-a").parent().siblings().find("a").removeClass("pitchList-a");
	      $("#finance").find(".finance-side-boult").show().parents().siblings().find(".finance-side-boult").hide();
	      $("#finance").addClass("pitchList-a").parent().siblings().find("a").removeClass("pitchList-a");
	      $("#finance").parent().addClass("pitchList").siblings().removeClass("pitchList");
	      $("#finance").find(".finance-side-boult").show().parent().parent().siblings().find(".finance-side-boult").hide();
    }*/



