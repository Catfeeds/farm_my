$(function() {
	$(".mestab span").click(function() {
		var mestab_index = $(this).index();
		$(this).addClass("pitch").siblings().removeClass("pitch");
		$(".message-content>div").eq(mestab_index).show().siblings().hide();
	});
	var choose = 1;
	var choose2 = $(".choose").attr("data");
	if (choose2 != '') {
		choose = $(".choose").attr("data");

	}
	$(".mestab>div>span").eq(choose - 1).addClass("pitch").siblings().removeClass("pitch");
	$(".message-content>div").eq(choose - 1).show().siblings().hide();
});
//$(function(){
//		$("p").css("color","red !important")
//		if($(".message-detail p").has("span")){
//			$(this).find("span").css("text-indent","2em");
//		}
//})
	