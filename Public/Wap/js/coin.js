//委托管理
$(document).ready(function(){
	$(".tab-entrust .swiper-container .tab-entrustlist a").click(function(){
		var mestab_index=$(this).index();
		$(this).addClass("pitch").siblings().removeClass("pitch");
		$(".entrust-coin ul").eq(mestab_index).show().siblings(".entrust-coin ul").hide();
	})
})




