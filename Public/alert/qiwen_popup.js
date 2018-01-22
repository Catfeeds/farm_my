/**
 * 
 */

function cashier_shop(){
	var iframe=$("#content iframe:visible").attr('name');
	window.frames[iframe].aaa();
}

$(function(){
	
	//灰色背景弹出层
	$("body").append('<div id="qiwen_poput"></div>');
/*	$("body").append('<div class="qiwen_poput_code">返回状态提示</div>');*/
	$("#qiwen_poput").append('<div class="qiwen_poput_div"></div><div class="qiwen_poput_form"><h2 class="qiwen_poput_title"><span class="title">华联全球商贸市场</span><span class="qiwen_poput_title_close" style="position: relative;top: -18px">&times;</span></h2><p><span>华联全球商贸市场提示您：</span></p><div class="tips_content_alert"><span class="glyphicon glyphicon-info-sign"></span></div></div>');
	$(".tips_content_alert").append('<div class="qiwen_poput_code">返回状态提示</div>');

	$(".qiwen_poput_title_close").click(function(){
	/*	set_iframe_close();*/
	$("#qiwen_poput").hide()
	})
	
});

function set_poput_code(title,code,reload){
    $("#qiwen_poput").fadeIn(100);
	var reload = arguments[2] ? arguments[2] : true;
	if(code){
		$(".tips_content_alert").css({'color':'#45bf4a'});
		if(reload){
			setTimeout(function () {
				location.reload()
			},3100)
		}
	}else{
		$(".tips_content_alert").css({'color':'#FA4223'});
	}
	$(".qiwen_poput_code").html(title);
	//$(".qiwen_poput_code").show(500);
	setTimeout(function () { 
		//$(".qiwen_poput_code").hide(500);
        $("#qiwen_poput").fadeOut(300);
	}, 3000);

}


function iframe_load(){
	$(".qiwen_poput_form").show();
	var w = $("#qiwen_poput_iframe").contents().find('body').attr('width');
	var h = $("#qiwen_poput_iframe").contents().find('body').attr('height');
	w = w ? w : $("#qiwen_poput_iframe").contents().width();
	h = h ? h : $("#qiwen_poput_iframe").contents().find('html').height();
	iframe_size(w,h);
	$(".qiwen_poput_form").hide();
	setTimeout(function () { 
		$(".qiwen_poput_form").show(500);
    }, 10);


}
function iframe_size(w,h){
	if(w < 500){
		w = 500;
	}
	if(w > 1000){
		w = 1000;
	}
	if(h <300){
		h=300;
	}
	if(h > 600){
		h=600;
		w = parseInt(w) + 20;
	}
	$("#qiwen_poput_iframe").height(h);
	$("#qiwen_poput_iframe").width(w);
	//$(".qiwen_poput_form").css({'margin-left':"-"+(w/2)+"px"});//,'top':'-'+(h+200)+'px','display':'block'
}

