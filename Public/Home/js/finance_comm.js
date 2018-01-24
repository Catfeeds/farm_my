/**
 * Created by yvdedu.com on 2017/7/26.
 */
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

$("#xnb_table").on("click","td",function () {    //选择虚拟币后发起请求
    $("#xnb").html($(this).html());
    $('input[name=xnb]').val($(this).attr('id'));
    $('.form-inline').submit();

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
