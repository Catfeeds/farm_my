$(function(){
	$(".bank-del").click(function(){
		var id=	$(this).attr("value");
		$.post("/Wap`Safety`deletebank",{id:id},function (data) {
			if (data.status!=true){   //请求失败
				ShowHintBox(data.info,false);
				return false
			}
			ShowHintBox(data.info,true);
		})
	})

})
