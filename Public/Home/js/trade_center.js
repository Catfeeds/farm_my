$(function () {

/*    /!*trade_title*!/
    $("#trade_info_title").on("click", "li.double_arrow", function () {
        var $span = $(this).find("span.glyphicon");
        if ($(this).hasClass("selected")) {
            $span.toggleClass("selected")
        } else {
            $(this).parent().find(".selected").removeClass("selected");
            $(this).addClass("selected");
            $($span[1]).addClass("selected")
        }
    });*/

    /*trade area bg_grey*/
    var $rmb_area=$("#RMB_area"),$py_area=$("#PY_area");
    $rmb_area.children("li:odd").addClass("grey_bg");
    $py_area.children("li:odd").addClass("grey_bg");

    // /*RMB trade click*/
    // $rmb_area.on('click',"li.rmb_classify",function () {
    //     window.location.href='btb_trade.shtml';
    // });

    /*btb trade*/
    var $record_table=$("#tbody_out");
    $record_table.find("tr:odd").addClass("bg_grey");

    /*buy sell form*/
    $("#trade_buy_sell").on("click","div.input-group-addon",function () {
        /*    alert()*/
        $(this).prev("input").val($(this).find("span").text())
    });



});
function clearNoNum(obj){
    obj.value = obj.value.replace(/^[\.]/g,"");   //第一个不能是0；
    obj.value = obj.value.replace(/[^\d.]/g,"");  //清除“数字”和“.”以外的字符
    obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
    obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
    obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d\d\d\d\d).*$/,'$1$2.$3');//只能输入两个小数
    if(obj.value.indexOf(".")< 0 && obj.value !=""){//以上已经过滤，此处控制的是如果没有小数点，首位不能为类似于 01、02的金额
        obj.value= parseFloat(obj.value);
    }
}
function checkMumber(obj) {
    obj.value = parseFloat(obj.value);
}
/**
 * Created by Administrator on 2017/6/27 0027.
 */
