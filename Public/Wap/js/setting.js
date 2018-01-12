$(function(){
	$(".cost-tab span").click(function(){
		var cost_index=$(this).index();
		$(this).addClass("pitch").siblings().removeClass("pitch");
		$(".explain-hint").eq(cost_index).show().siblings(".explain-hint").hide();
		$(".setting-cost .explain-list").eq(cost_index).show().siblings(".explain-list").hide();
	})
})
