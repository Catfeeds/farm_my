$(function () {
    /*market-area*/

    $("#market_type").on("click","li",function () {
        $(this).parent().find(".cur").removeClass("cur");
        $(this).addClass("cur");
    }).children("li:first-child").addClass("cur");
/*    $("body *:not('#market')").click(function (e) {
        $("#kxiantu").css("pointer-events","none");
        console.log(11111);
    /!*    alert(1)*!/
    });*/
    $("#market").click(function (e) {
        $("#kxiantu").css("pointer-events","auto");
        e.stopPropagation();
    });
    $("#contact_enter").find(".contact_body").scroll(function (e) {
        e.stopPropagation();
    });

});/**
 * Created by Administrator on 2017/6/15.
 */
// 记住密码

// $("#remember_password").click(function () {
//     var pass=$("#user_password").value();
//     var user=$("#user_name").value();
//     $.post("{:U('Index/login')}",{pass:pass,user:user},function (data) {
//         console.log(data);
//     },'json');
//
//     // $.ajax({
//     //         url:"{:U('Index/login')}",
//     //         data:pass&user,
//     //         type:"POST",
//     //         success:function (data) {
//     //
//     //         }
//     //
//     // })
// });

$(function(){
	$(".room-title-shrink").click(function(){
		$(".chat-room").hide();
	})
})

