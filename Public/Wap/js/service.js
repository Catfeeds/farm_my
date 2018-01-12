$(function(){
	var  input_box=document.getElementById("input_box");//输入框
	var  $send=$("#send")//发送按钮
	var $connecting=$(".connecting");//正在连接请稍等
	var $servicechat=$(".service-chat");//聊天内容

	
	input_box.oninput = function(){
		if(input_box.value.length!=0){
			$send.css("background","#ff8c00");
		}
		else{
			$send.css("background","#ffd7a7");
		}
	}

		
	//兼容手机键盘挡住输入框
	$('input[type="text"],textarea').on('click', function () {
		  var target = this;
		 	 setTimeout(function(){
		        target.scrollIntoViewIfNeeded();
//		        console.log('scrollIntoViewIfNeeded');
		      },300);
	});
	

})
