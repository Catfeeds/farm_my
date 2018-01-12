$(function () {
    /*email num switch*/

    $("#register-title").on("click","span",function () {
        formChange(this,"手机号注册","邮箱注册","#register_form")

    });

    /*find password */
    $("#findPw-title").on("click","span",function () {
        formChange(this,"手机号找回","邮箱找回","#findPw_form")
    });
});
function formChange(element,selTitle,ChaTitle,chaDiv) {
    $(element).parent().find(".selected").removeClass("selected");
    $(element).addClass("selected");
    var span_test=$(element).text();
    switch (span_test){
        case selTitle:
            $(chaDiv).stop(true,true).animate({"left": "0"}, 300);
            break;
        case ChaTitle:
            $(chaDiv).stop(true,true).animate({"left": "-450px"}, 300);
            break;
    }
}
var phone=0;
var oldpwd=0;
var newpwd=0;
var olddeal=0;
var newdeal=0;
var idcardtrue=0;
var emailcode=0;
var userphone=0;
var yanjzetime=0;
var emailname=0;
function findemailpwd() {
    var emailnum=  $('#input_identity_email').val();
    if($('#email_name').val()==""){
        ShowHintBox("账号不可为空",false);
        return false
    }
    if($('#yanzheng_email_num').val()==""){
        ShowHintBox("验证码不可为空",false);
        return false
    }
    if(emailnum==""){
        ShowHintBox("邮箱验证码不可为空",false);
        return false
    }

    $.ajax({
        url:"/Wap`Register`emailreue",
        type:"post",
        data:{emailnum:emailnum},
        dataType:'json',
        success:function (data) {
            if (data.status==true){
                ShowHintBox(data.info);
                $(".retrieve-method").hide();
                $("#set_pw").show()
            }else{
                ShowHintBox(data.info,false);
                return false
            }
        }
    });
}
function Regfindphone(ss) {
    var pattern =  /^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            ShowHintBox("手机号可用");

        }else{
            ShowHintBox("手机号不正确",false);
            return false;
        }
    }
}
function tijiaofindphone() {
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#input_identity').val();
    if(phone_num==""){
        ShowHintBox("账号不可为空",false);
        return false
    }
    if($('#yanzheng_num').val()==""){
        ShowHintBox("验证码不可为空",false);
        return false
    }
    if(cipher_code==""){
        ShowHintBox("手机验证码不可为空",false);
        return false
    }
    if(cipher_code != "" && cipher_code != null){
        $.ajax({
            url:"/Wap`Photoma`reg",
            type:"post",
            data:{phone_num:phone_num,cipher_code:cipher_code},
            dataType:'json',
            success:function (data) {
                if (data.status==true){
                    ShowHintBox(data.info);
                    $(".retrieve-method").hide();
                    $("#set_pw").show()
                }else{
                    ShowHintBox(data.info,false);
                    return false
                }
            }
        });
    }
}
function tijiaoreturn() {

    var form=$('.ajaxaaaa_form');
    var pwdold=$('#cipher_code').val();
    var pwdnew=$('#re_new_pw').val();
    if(pwdold==""  ||  pwdnew==""){
        ShowHintBox("交易密码不可为空",false);
        return false
    }
    if( oldpwd==1 && newpwd==1){
        if(pwdold==pwdnew){
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:form.serialize(),
                dataType:'json',
                success:function (data) {
                    console.log(data);
                    if (data.status==true){
                        //请求失败
                        ShowHintBox(data.info);
                        phone=0;
                        oldpwd=0;
                        newpwd=0;
                        olddeal=0;
                        newdeal=0;
                        userphone=0;
                        idcardtrue=0;
                        location.href="/Wap`Profile`profile";
                    }else{
                        ShowHintBox(data.info,false);
                        return false
                    }
                }
            });
        }
    }else {
        ShowHintBox("信息不完整",false);
    }
}
function pwdfind() {
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#input_identify_code').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0){
        $.ajax({
            url:"/Wap`Register`pwdpind",
            type:"post",
            data:{phone_num:phone_num,cipher_code:cipher_code,yanzheng_num:yanzheng_num},
            dataType:'json',
            success:function (data) {
                $('.yzm_zj').find('img').trigger("click");
                if (data.status!=true){
                    ShowHintBox(data.info,false);

                    return false
                }
                ShowHintBox(data.info);
                userphone=1;
                yanjzetime=59;
                var ss=  setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#input_identify_code').html(yanjzetime+"秒")
                        $('#input_identify_code').css({"pointer-events": "none", "background":"#999"}); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#input_identify_code').html("获取验证码");
                        $('#input_identify_code').css({"pointer-events": "auto","background":"#FF8C00" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }
        });
    }



}
function tijiaoemail(ss) {
    var pattern = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
    if(pattern.test($(ss).val())){
        $.ajax({
            url:"/Home`Register`regemailname",
            type:"post",
            data:{email:$(ss).val()},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    ShowHintBox(data.info,false);
                    return false
                }
                ShowHintBox(data.info,true);
                emailname=1;
            }
        });
    }
}
function Regemail() {
    var ss=$('#email_name').val();
    var yanzheng_num=$('#yanzheng_email_num').val();
    var pattern = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
    if(pattern.test(ss)){
        if(yanjzetime==0) {
            $.ajax({
                url: "/Wap`Register`regemail",
                type: "post",
                data: {email: ss, yanzheng_num: yanzheng_num},
                dataType: 'json',
                success: function (data) {
                    $('.yzm_zj').find('img').trigger("click");
                    if (data.status != true) {   //请求失败
                        ShowHintBox(data.info, false);

                        return false
                    }

                    ShowHintBox(data.info);
                    yanjzetime=59;
                    var ss=  setInterval(function () {
                        if(yanjzetime>0){
                            yanjzetime= yanjzetime-1;
                            $('#input_identify_email').html(yanjzetime+"秒")
                            $('#input_identify_email').css({"pointer-events": "none" ,"background": "#999"}); //移除click
                        }else if(yanjzetime<=0){
                            clearInterval(ss);
                            $('#input_identify_email').html("获取验证码");
                            $('#input_identify_email').css({"pointer-events": "auto" ,"background": "#FF8C00"}); //移除click
                            yanjzetime=0;
                        }
                    },1000)
                }
            });
        }
    }else{
        ShowHintBox("请输入正确的邮箱",false);
    }
}
function emailfindpw(ss) {
    var pattern = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
    if(pattern.test($(ss).val())){
        $(ss).parent().find("span.red_tips").hide();
        $(ss).parent().find("span.green_tips").show();
        $.ajax({
            url:"/Wap`Register`regemailnamezhaohui",
            type:"post",
            data:{email:$(ss).val()},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    ShowHintBox(data.info,false);
                    return false
                }
                ShowHintBox(data.info);
                emailname=1;
            }
        });
    }else {
        $(ss).parent().find("span.red_tips").show();
        $(ss).parent().find("span.green_tips").hide();
        return false;
    }
}
function Regphone(ss) {
    var pattern = /^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            $.ajax({
                url:"/Wap`Register`reguser",
                type:"post",
                data:{users:$(ss).val()},
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        ShowHintBox(data.info,false);
                        return false
                    }
                    ShowHintBox(data.info);
                    phone=1
                }
            });
        }else{
            ShowHintBox("手机号不正确，必须11位手机格式数字",false);
            return false;
        }
    }
}
function yanzheng() {
//        get_identify_code
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#input_identify_code').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0 && phone==1){
        $.ajax({
            url:"/Wap`Photoma`sms",
            type:"post",
            data:{phone_num:phone_num,yanzheng_num:yanzheng_num},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){
                    ShowHintBox(data.info,false);
                    $('.yzm_zj').find('span').trigger("click")
                    return false
                }
                ShowHintBox(data.info);
                yanjzetime=59;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#input_identify_code').html(yanjzetime+"秒")
                        $('#input_identify_code').css({"pointer-events": "none","background":"#999" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#input_identify_code').html("发送验证码");
                        $('#input_identify_code').css({"pointer-events": "auto","background":"#FF8C00" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }
        });
    }else if(yanjzetime==0 && phone==0) {
        ShowHintBox("用户已经注册，请登录",false);
    }


}
function tijiaophone(ss) {
    var phone_num=$('#phone_num').val();
    var cipher_code=$(ss).val();
    if($(ss).val() != "" && $(ss).val() != null){
        $.ajax({
            url:"/Wap`Photoma`reg",
            type:"post",
            data:{phone_num:phone_num,cipher_code:cipher_code},
            dataType:'json',
            success:function (data) {
                if (data.status==true){
                    ShowHintBox(data.info);
                    $(ss).attr('value','1');
                }else{
                    ShowHintBox(data.info,false);
                    return false
                }
            }
        });
    }
}
function enext() {
    var emailnum=$('#input_identity_email').val();
    var check=$("#agree2_checkbox").attr("value");
    if(check==1 && oldpwd==1 && newpwd==1 && emailname==1){
        $.ajax({
            url:"/Home`Register`emailreue",
            type:"post",
            data:{emailnum:emailnum},
            dataType:'json',
            success:function (data) {
                if (data.status==true){
                    ShowHintBox(data.info,true);
                    emailcode=1;
                    var $step_div=$("#register_steps").find(".register_step");
                    $step_div.removeClass("selected");
                    $($step_div[1]).addClass("selected");
                }else{
                    ShowHintBox(data.info,false);
                    return false
                }
            }
        });
    }
}
///
function tijiaozhuce() {
    var form=$('.ajax_zhuce');
    var name=$('#real_name').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if (name!=null && name!="" && idcardtrue==1 ){
        $.ajax({
            url:form.attr('action'),
            type:form.attr('method'),
            data:form.serialize(),
            dataType:'json',
            success:function (data) {
                console.log(data);
                if (data.status==true){
                    //请求失败
                    ShowHintBox(data.info);
                    var phonenum=$('#phone_num').val();
                    var email=$('#email_name').val();
                    var idtype=$('#idtpe').val();
                    var idnum=$('#id_num').val();
                    if(emailcode==1){
                        $('#users').html(email)

                    }else if(phone==1){
                        $('#users').html(phonenum)
                    }
                    console.log( $('#users').val());
                    $('#username').html(name);
                    $('#idtype').html(idtype);
                    $('#idcard').html(idnum);
                    $('#lastregis').css({"display":"none"});
                    $('#housregis').css({"display":"block"});
                     phone=0;
                     oldpwd=0;
                     newpwd=0;
                     olddeal=0;
                     newdeal=0;
                     idcardtrue=0;
                }else{
                    ShowHintBox(data.info,false);
                    return false
                }
            }
        });
    }else if(name==null && name=="" && idcardtrue==1 && yanzheng_num!=null && yanzheng_num!=""){
        ShowHintBox("真实姓名不可为空",false);
    }else if(name!=null && name!="" && idcardtrue==1 && yanzheng_num==null && yanzheng_num==""){
        ShowHintBox("验证码不可为空",false);
    }
}
function cardReg(ss) {
            $.ajax({
                url:"/Home`Register`regidcard",
                type:"post",
                data:{idcard:$(ss).val()},
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        ShowHintBox(data.info,false);
                        return false
                    }
                    ShowHintBox(data.info);
                    idcardtrue=1;
                }
            });
    return false;
}
function RegeMatch(ss){
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            ShowHintBox("密码可用");
            oldpwd=1;
        }else{
            ShowHintBox("密码格式不正确",false);
            return false;
        }
    }
}
function newpwdReg(ss) {
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    var cipher_code=$('#cipher_code').val();
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            if(cipher_code==$(ss).val()){
                ShowHintBox("密码可用");
                newpwd=1;
            }else{
                ShowHintBox("两次密码不一致",false);
                return false;
            }
        }else{
            ShowHintBox("密码格式不正确",false);
            return false;
        }
    }
}
function Regdeal(ss) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            ShowHintBox("交易密码可用");
            olddeal=1;
        }else{
            ShowHintBox("密码格式不正确",false);
            return false;
        }
    }
}
function Regnewdeal(ss) {
    var pattern = /^\d{6}$/;
    var set_trade_pw=$("#set_trade_pw").val();
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            if(set_trade_pw==$(ss).val()){
            ShowHintBox("交易密码可用");
                newdeal=1;
            }else{
                ShowHintBox("两次密码不一致",false);
                return false;
            }
        }else{
            ShowHintBox("密码格式不正确",false);
            return false;
        }
    }
}
function nextdeal() {
    if(newdeal==1 && olddeal==1 ){
        $("#nextdeal").css({"display":"none"});
        $("#lastregis").css({"display":"block"});
    }else {
        ShowHintBox("两次密码不一致",false);
    }
}
function nextren() {
    var newss=$('#input_identity').val();
    var check=$("#agree1_checkbox").attr("value");
    // newss==1 && check==1 && newpwd==1 && oldpwd==1 &&
    if(newss!="" && check==1 && newpwd==1 && oldpwd==1 && phone==1){
        $('#firstregist').css({"display":"none"})
        $('#nextdeal').css({"display":"block"})
    }else if(newss=="" && check==1 && newpwd==1 && oldpwd==1 && phone==1){
        ShowHintBox("手机验证码错误无法下一步",false);
    }else if(newss!="" && check=="" && newpwd==1 && oldpwd==1 && phone==1){
        ShowHintBox("请阅读注册协议",false);
    } else if(newss!="" && check==1 && newpwd==0 && oldpwd==1 && phone==1){
        ShowHintBox("两次密码不一致",false);
    } else if(newss!="" && check==1 && newpwd==1 && oldpwd==0 && phone==1){
        ShowHintBox("两次密码不一致",false);
    }else if(newss!="" && check==1 && newpwd==1 && oldpwd==1 && phone==0){
        ShowHintBox("该账号已存在",false);
    }
}
function set_poput_yanzhen(title,code){
    if(code){
        $(".qiwen_poput_code").css({'background':'#45bf4a'});
    }else{
        $(".qiwen_poput_code").css({'background':'red'});
    }
    $(".qiwen_poput_code").html(title);
    $(".qiwen_poput_code").show(500);
    setTimeout(function () {
        $(".qiwen_poput_code").hide(500);
    }, 3000);
}






