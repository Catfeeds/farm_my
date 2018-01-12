$('#photo_submit').click(function () {
    var picture1=$('#myImg').attr("src");
    var picture2=$('#fanImg').attr("src");
    var picture3=$('#idImg').attr("src");
    var form=$(this).parents().parent('.ajax_photo');
    var formData= new FormData($('.ajax_photo').get(0));
    if(picture1!="" && picture2!="" && picture3!=""){
        $('#photo_submit').html("照片上传中")
        $('#photo_submit').css({"pointer-events": "none" }); //移除click
        $.ajax({
            url:form.attr('action'),
            type:form.attr('method'),
            data:formData,
            processData:false,
            contentType:false,
            // async: false,
            dataType:"json",
            success:function (data) {
                if (data.status!=true){  //失败
                    ShowHintBox(data.info,false);
                    setTimeout(function () {
                        history.go(0)
                    },2000);
                    return false;
                }
                ShowHintBox(data.info,true)
                $('#photo_submit').html("确认提交");
                $('#photo_submit').css({"pointer-events": "auto" }); //移除click

            }
        })
    }else {
        ShowHintBox("请放入图片",false);
    }
});
function picture(ss) {
    var asd=$(ss).parent().find('dt').find('input[type=file]');
    asd.trigger('click');
}
$('#front_photo').change(function () {
    var  fr= new FileReader();
    fr.readAsDataURL(this.files[0]);
    fr.onload=function () {
        $('#myImg').show();
        $('#myImg').parent().css("background","none");
        $('#myImg').attr("src",fr.result);
        /*    $("#myImg").css({height:"100%"});
         $("#myImg").css({width:"100%"});*/
        $(".zheng_card_titlez").css({display:"none"});
    }
});
$('#negative_photo').change(function () {
    var  fr= new FileReader();
    fr.readAsDataURL(this.files[0]);
    fr.onload=function () {
        $('#fanImg').show();
        $('#fanImg').parent().css("background","none");
        $('#fanImg').attr("src",fr.result);
        /*        $("#fanImg").css({height:"100%"});
         $("#fanImg").css({width:"100%"});*/
        $(".fan_card_titlez").css({display:"none"});
    }
});
$('#id_photo').change(function () {
    var  fr= new FileReader();
    fr.readAsDataURL(this.files[0]);
    fr.onload=function () {
        $('#idImg').show();
        $('#idImg').parent().css("background","none");
        $('#idImg').attr("src",fr.result);
        /*    $("#idImg").css({height:"100%"});
         $("#idImg").css({width:"100%"});*/
        $(".id_card_titlez").css({display:"none"});
    }
});