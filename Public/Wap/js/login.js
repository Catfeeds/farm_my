$(function() {
//	var tabway = document.getElementById("retrievetab").getElementsByTagName("li");
//	var emailfind = document.getElementById("email_find");
//	var iphonefind = document.getElementById("iphone_find");
//	//显示邮箱找回
//	tabway[0].onclick = function() {
//		tabfind(0, 'block', 'none')
//	}
//	//显示手机找回
//	tabway[1].onclick = function() {
//
//		tabfind(1, 'none', 'block')
//	}
// //
// //	function tabfind(num, email, iphone) {
// //		for(var i = 0; i < tabway.length; i++) {
// //			tabway[i].className = "";
// //		}
// //		tabway[num].className = "pitch";
// //		emailfind.style.display = email;
// //		iphonefind.style.display = iphone;
// //	}
	$("#retrievetab li").click(function(){
		var retrieve_index=$(this).index();
		$(this).addClass("pitch").siblings().removeClass("pitch");
		$(".retrieve-method").eq(retrieve_index).show().siblings(".retrieve-method").hide();
	});

	$('.ajax_form').find('input[type=submit]').click(function () {

		var form=$(this).parents().parent('.ajax_form');
		if (form.get(0)==undefined){
			form=$(this).parent();
		}
		$.ajax({
			url:form.attr('action'),
			type:form.attr('method'),
			data:form.serialize(),
			dataType:'json',
			success:function (data) {
				if (data.status!=true){   //请求失败
					ShowHintBox(data.info,false);
					return false
				}
				
				setTimeout(function () {
					window.location.href="/Wap`Profile`profile";
				}, 1500);
			}
		});
		return false
	})
})


function set_poput_yanzhen(title,code){
	if(code){
		$(".qiwen_poput_code").css({'color':'#45bf4a','display':'block'});
	}else{
		$(".qiwen_poput_code").css({'color':'red','display':'block'});
	}
	$(".qiwen_poput_code").html(title);
	$(".qiwen_poput_code").show(500);
	setTimeout(function () {
		$(".qiwen_poput_code").hide(500);
	}, 3000);
}