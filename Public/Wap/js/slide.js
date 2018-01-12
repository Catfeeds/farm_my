$(function(){
	    // 滑动设置
	    var index=0;
	    if(sessionStorage.getItem("index")){	
	        index=parseInt(sessionStorage.getItem("index"))-1;
	        
	    }
	    var swiper = $(".swiper2 .swiper-container").swiper({
	        pagination: '.swiper2 .swiper-pagination',
	        slidesPerView: 2,
//	        paginationClickable: true,
			freeModeSticky:true,//自动贴合
	        spaceBetween: 0,//边距
	        freeMode: true,//slide会根据惯性滑动且不会贴合
	        nextButton: '.swiper-button-next',
        	prevButton: '.swiper-button-prev',
        	initialSlide: index,//设定初始化时slide的索引,从0开始
//      	centeredSlides: true
	    });
		$(".swiper2 .swiper-container .swiper-wrapper a").bind("click",function(){
	       var i= $(this).index();
	       sessionStorage.setItem("index",i);
    	})
		
	if($(".swiper2 .swiper-container .swiper-wrapper a").length<3){
		swiper.detachEvents();
	}
	else{
		$(".swiper2 .swiper-button-next").show();
		$(".swiper2 .swiper-button-prev").show();
	}
})

