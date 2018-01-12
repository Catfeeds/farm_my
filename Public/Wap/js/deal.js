$(document).ready(function() {
	$(".tabdeal a").click(function() {
		var sub_index = $(this).index();
		$(this).addClass("pitch").siblings().removeClass("pitch");
		$(".rank ul").eq(sub_index).show().siblings("ul").hide();
		$(".district").eq(sub_index).show().siblings(".district").hide();
	})
	//币交易
	$(".transaction-tab span").click(function(){
		var index=$(this).index();
		$(this).addClass("pitch").siblings().removeClass("pitch");
		$(".Switchtrading>div").eq(index).show().siblings().hide();
	})
	$(".tab_de").click(function(){
		$(".take_notes").show();
	})
	$(".tab_revoke").click(function(){
		$(".take_notes").hide();

		
	})
	
	var coin_cata_state=0;
	$("#coin_catalog").click(function(){
		if(coin_cata_state==0){
			$(".coin-list").stop().animate({"right":'0%'},100);
			$(".coin-cover").stop().animate({"right":"0%"},100);
			coin_cata_state=1;
		}
		else{
			$(".coin-list").stop().animate({"right":'-50%'},100);
			$(".coin-cover").stop().animate({"right":"-100%"},100);
			coin_cata_state=0;
		}
	})
	$(".coin-cover").click(function(){
		$(".coin-list").stop().animate({"right":'-50%'},100);
		$(".coin-cover").stop().animate({"right":"-100%"},100);
		coin_cata_state=0;
	})
	//显示
	$(".edit-currency ul li").click(function(){
		$(this).find(".edit-state").toggle();
	})
})
