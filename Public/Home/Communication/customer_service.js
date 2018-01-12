/**
 * Created by 杨尚云 on 2017/6/27.
 */
$(document).ready(function () {
    //发表按钮事件
send_information();
   //常见问题点击事件
   normal_problemClick();
});
//常见问题点击事件
function normal_problemClick(){
    var customer_service_findpasswordId=$("#customer_service_findpasswordId");
    var customer_service_newbieId=$("#customer_service_newbieId");
    var customer_service_findpassword_show=$("#customer_service_findpassword_show");
    var customer_service_newbieShow=$("#customer_service_newbieShow");
    var normal_problemClick=$(".normal_problemClick");
    var clickTimes=1;
    normal_problemClick.click(function () {
        if($(this).find("p").show==true){
            $(".normal_problemClick").find("p").show();
            $(this).find("p").hide();
        }
        else{
            $(".normal_problemClick").find("p").hide();
            $(this).find("p").show();
        }
    });//可以用

    //normal_problemClick.click(function () {
    //    if(clickTimes==1){
    //        $(".normal_problemClick").find("p").hide();
    //        $(this).find("p").show();
    //        clickTimes=0;
    //    }else{
    //        $(".normal_problemClick").find("p").hide();
    //        //$(this).find("p").show();
    //        clickTimes=1;
    //    }

    //});

    //if(clickTimes==1){
    //    if(customer_service_findpasswordId.show||customer_service_newbieId.show){
            //normal_problemClick.find("p").hide();

        //}
    //}
//customer_service_findpasswordId.click(function () {
//    var times=0;
//    if(times==0){
//        normal_problemClick.find("p").hide();
//        customer_service_findpasswordId.next("p").show();
//        times=1;
//    }else{
//        customer_service_findpasswordId.next("p").hide();
//        times=0;
//    }
////});
//    customer_service_newbieId.click(function () {
//        var times=0;
//        if(times==0){
//            normal_problemClick.find("p").hide();
//            customer_service_newbieId.next("p").show();
//            times=1;
//        }else{
//            customer_service_newbieId.next("p").hide();
//            times=0;
//        }
//    });
    //normal_problemClick.click(function () {
    //    if(customer_service_findpasswordId.show&&customer_service_newbieId.hide){
    //        customer_service_findpasswordId.next("p").hide();
    //        customer_service_newbieId.next("p").show();
    //        //alert("测试1");
    //    }
    //    if(customer_service_findpasswordId.hide&&customer_service_newbieId.show){
    //        customer_service_findpasswordId.next("p").show();
    //        customer_service_newbieId.next("p").hide();
    //    }
    //    if(customer_service_findpasswordId.hide&&customer_service_newbieId.hide){
    //        $(this).next("p").show();
    //        //customer_service_newbieId.next("p").show();
    //    }
    //});
}
$(window).resize(function () {
    customer_service_mainBody_position();
});
//位置函数
function customer_service_mainBody_position(){
    var windowHeight = $(this).height();//网页可视区域高度
    var customer_service_mainBody_top=$(".customer_service_mainBody");
    var customer_service_mainBody_height=customer_service_mainBody_top.outerHeight();
    if(windowHeight<=700){
        customer_service_mainBody_top.css("margin-top","0px");
    }
    else{
        var result=windowHeight-customer_service_mainBody_height;
        if(result>=110){
            customer_service_mainBody_top.css("margin-top","110px");
        }else{
            customer_service_mainBody_top.css("margin-top",result+"px");
        }
    }
}
//关闭当前窗口

$(window).bind('beforeunload',function(){return '您输入的内容尚未保存，确定离开此页面吗？';});


////发送信息事件
function  send_information(){
    var mine_informationID=$("#mine_informationID");
    var send_informationId=$("#send_informationId");
    mine_informationID.mouseleave(function () {
        send_informationId.attr("disabled",false);
    });

}