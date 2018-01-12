$(document).ready(function(){
	    // 轮播图设置
	    var mySwiper = $(".swiper1 .swiper-container").swiper({
	    direction: 'horizontal',
	    loop: true,
	    autoplay : 2000,
//	    autoplayDisableOnInteraction : false,用户操作后停止自动播放
	    pagination: '.swiper1 .swiper-pagination',
	    speed:500,
//	    effect : 'flip',切换效果
	    });
	 
	$(".tabcut_list a").click(function(){
		var sub_index=$(this).index();
		$(this).addClass("pitch").siblings().removeClass("pitch");
		$(".district").eq(sub_index).show().siblings(".district").hide();
	})
})
