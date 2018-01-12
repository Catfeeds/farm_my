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
var ssaemail=0;

function emailcodes(ss) {
    var emailnum=  $('#input_identity_email').val();
    $.ajax({
        url:"/Home`Register`emailreue",
        type:"post",
        data:{emailnum:emailnum},
        dataType:'json',
        success:function (data) {
            if (data.status==true){
                $(ss).parent().find("span.red_tips").hide();
                $(ss).parent().find("span.green_tips").show();
                ssaemail=1
            }else{
                $(ss).parent().find("span.red_tips").show();
                $(ss).parent().find("span.green_tips").hide();
                return false
            }
        }
    });
}
function findemailpwd() {
    if($('#email_name').val()==""){
        set_poput_yanzhen("账号不可为空",false);
        return false;
    }
    if($('#yanzheng_email_num').val()==""){
        set_poput_yanzhen("验证码不可为空",false);
        return false;
    }
    if($('#input_identity_email').val()==""){
        set_poput_yanzhen("邮箱验证码不可为空",false);
        return false;
    }
    if(ssaemail==1 && emailname==1){
        $("#input_sm").hide();
        $("#set_pw").show()
    }

}
function Regfindphone(ss) {
    var pattern =  /^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){

            // reguser
            $.ajax({
                url:"/Home`Register`regemailnamezhaohui",
                type:"post",
                data:{email:$(ss).val()},
                dataType:"json",
                success:function (data) {
                    if (data.status==true){
                        /*set_poput_yanzhen("手机号可用",true);*/
                        $(ss).parent().find("span.red_tips").hide();
                        $(ss).parent().find("span.green_tips").find('span').text(data.info);
                        $(ss).parent().find("span.green_tips").show();
                    }else{
                        $(ss).parent().find("span.red_tips").find('span').text(data.info);
                        $(ss).parent().find("span.red_tips").show();
                        $(ss).parent().find("span.green_tips").hide();
                        return false;
                    }
                }
            })


        }else{
           /* set_poput_yanzhen("手机号不正确",false);*/
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}
function tijiaofindphone() {
    var input_identity=$('#input_identity').val();
    if(input_identity!=""){
        $("#input_sm").hide();
        $("#set_pw").show()
    }
}
function tijiaoreturn() {
    var form=$('.ajaxaaaa_form');
    var pwdold=$('#new_pw').val();
    var pwdnew=$('#re_new_pw').val();
    if( oldpwd==1 && newpwd==1){
        if(pwdold==pwdnew){
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:form.serialize(),
                dataType:'json',
                success:function (data) {
                    if (data.status==true){
                        //请求失败
                        set_poput_yanzhen(data.info,true);
                        phone=0;
                        oldpwd=0;
                        newpwd=0;
                        olddeal=0;
                        newdeal=0;
                        userphone=0;
                        idcardtrue=0;
                        location.href="/Home`Index`index";
                    }else{
                        set_poput_yanzhen(data.info,false);
                        return false
                    }
                }
            });
        }
    }else {
        set_poput_yanzhen("信息不完整",false);
    }
}
function pwdfind() {
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#input_identify_code').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0){
        $.ajax({
            url:"/Home`Register`pwdpind",
            type:"post",
            data:{phone_num:phone_num,cipher_code:cipher_code,yanzheng_num:yanzheng_num},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){
                    set_poput_yanzhen(data.info,false);
                    $('.yzm_zj').find('span').trigger("click")
                    return false
                }
                set_poput_yanzhen(data.info,true);
                userphone=1;
                yanjzetime=57;
                var ss=  setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#input_identify_code').html(yanjzetime+"秒")
                        $('#input_identify_code').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#input_identify_code').html("免费获取验证码");
                        $('#input_identify_code').css({"pointer-events": "auto" }); //移除click
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
        $(ss).parent().find("span.red_tips").hide();
        $(ss).parent().find("span.green_tips").show();
        $.ajax({
            url:"/Home`Register`regemailname",
            type:"post",
            data:{email:$(ss).val()},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_yanzhen(data.info,false);
                    return false
                }
                set_poput_yanzhen(data.info,true);
                emailname=1;
            }
        });
    }else {
        $(ss).parent().find("span.red_tips").show();
        $(ss).parent().find("span.green_tips").hide();
        return false;
    }
}
function keytijiaoemail(ss) {
    var pattern = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
    if(pattern.test($(ss).val())){
        $(ss).parent().find("span.red_tips").hide();
        $(ss).parent().find("span.green_tips").show();
    }else {
        $(ss).parent().find("span.red_tips").show();
        $(ss).parent().find("span.green_tips").hide();
        return false;
    }
}
function Regemail() {
    var ss=$('#email_name').val();
    var yanzheng_num=$('#yanzheng_email_num').val();
    var pattern = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
    if(pattern.test(ss)){
        if(yanjzetime==0){
            $.ajax({
                url:"/Home`Register`regemail",
                type:"post",
                data:{email:ss,yanzheng_num:yanzheng_num},
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        set_poput_yanzhen(data.info,false);
                        $('.yzm_zj').find('span').trigger("click")
                        return false
                    }
                    set_poput_yanzhen(data.info,true);
                    yanjzetime=57;
                    var ss= setInterval(function () {
                        if(yanjzetime>0){
                            yanjzetime= yanjzetime-1;
                            $('#input_identify_email').html(yanjzetime+"秒")
                            $('#input_identify_email').css({"pointer-events": "none" }); //移除click
                        }else if(yanjzetime<=0){
                            clearInterval(ss);
                            $('#input_identify_email').html("免费获取验证码");
                            $('#input_identify_email').css({"pointer-events": "auto" }); //移除click
                            yanjzetime=0;
                        }
                    },1000)
                }
            });
        }
    }else{
        set_poput_yanzhen("请输入正确的邮箱",false);
    }
}
function Regphone(ss) {
    var pattern = /^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            $.ajax({
                url:"/Home`Register`reguser",
                type:"post",
                data:{users:$(ss).val()},
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        set_poput_yanzhen(data.info,false);
                        return false
                    }
                    set_poput_yanzhen(data.info,true);
                    phone=1
                }
            });
        }else{
     /*       set_poput_yanzhen("手机号不正确，必须11位手机格式数字",false);*/
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}

function keyRegphone(ss){
    var pattern = /^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        $(ss).parent().find("span.green_tips").find('span').text("手机号码格式正确");
        if(pattern.test($(ss).val())){
            $(ss).parent().find("span.red_tips").hide();

            $(ss).parent().find("span.green_tips").show();
        }else{
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}
function yanzheng() {
//        get_identify_code
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#input_identify_code').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0){
        $.ajax({
            url:"/Home`Photoma`sms",
            type:"post",
            data:{phone_num:phone_num,cipher_code:cipher_code,yanzheng_num:yanzheng_num},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){
                    set_poput_yanzhen(data.info,false);
                    $('.yzm_zj').find('span').trigger("click")
                    return false
                }
                set_poput_yanzhen(data.info,true);
                yanjzetime=57;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#input_identify_code').html(yanjzetime+"秒")
                        $('#input_identify_code').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#input_identify_code').html("免费获取验证码");
                        $('#input_identify_code').css({"pointer-events": "auto" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }
        });
    }


}
function tijiaophone(ss) {
    var phone_num=$('#phone_num').val();
    var cipher_code=$(ss).val();
    if($(ss).val() != "" && $(ss).val() != null){
        $.ajax({
            url:"/Home`photoma`reg",
            type:"post",
            data:{phone_num:phone_num,cipher_code:cipher_code},
            dataType:'json',
            success:function (data) {
                if (data.status==true){
                 /*   set_poput_yanzhen(data.info,true);*/
                    $(ss).parent().find("span.red_tips").hide();
                    $(ss).parent().find("span.green_tips").show();
                    // $(ss).val('1');
                }else{
                   /* set_poput_code(data.info,false);*/
                    $(ss).parent().find("span.red_tips").show();
                    $(ss).parent().find("span.green_tips").hide();
                    return false
                }
            }
        });
    }
}
function regcodeemail(ss) {
    var emailnum=$('#input_identity_email').val();
    $.ajax({
        url:"/Home`Register`emailreue",
        type:"post",
        data:{emailnum:emailnum},
        dataType:'json',
        success:function (data) {
            if (data.status==true){
                $(ss).parent().find("span.red_tips").hide();
                $(ss).parent().find("span.green_tips").show();
                //set_poput_yanzhen(data.info,true);
                emailcode=1;
            }else{
               // set_poput_code(data.info,false);
                $(ss).parent().find("span.red_tips").show();
                $(ss).parent().find("span.green_tips").hide();
                return false
            }
        }
    });
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
                if (data.status==true){
                    //请求失败
                    set_poput_yanzhen(data.info,true);
                    var phonenum=$('#phone_num').val();
                    var email=$('#email_name').val();
                    var idtype=$('#id_type').val();
                    var idnum=$('#id_num').val();
                    var $step_div=$("#register_steps").find(".register_step");
                    if(emailcode==1){
                        $('#users').html(email)

                    }else if(phone==1){
                        $('#users').html(phonenum)
                    }
                    $('#username').html(name);
                    $('#idtype').html(idtype);
                    $('#idcard').html(idnum);
                    $step_div.removeClass("selected");
                    $($step_div[3]).addClass("selected");
                     phone=0;
                     oldpwd=0;
                     newpwd=0;
                     olddeal=0;
                     newdeal=0;
                     idcardtrue=0;
                }else{
                    set_poput_yanzhen(data.info,false);
                    return false
                }
            }
        });
    }else if(name==null && name=="" && idcardtrue==1 && yanzheng_num!=null && yanzheng_num!=""){
        set_poput_yanzhen("真实姓名不可为空",false);
    }else if(name!=null && name!="" && idcardtrue==1 && yanzheng_num==null && yanzheng_num==""){
        set_poput_yanzhen("验证码不可为空",false);
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
                        /*set_poput_yanzhen(data.info,false);*/
                        $(ss).parent().find("span.red_tips").show();
                        $(ss).parent().find("span.green_tips").hide();
                        return false
                    }
                    /*set_poput_yanzhen(data.info,true);*/

                    $(ss).parent().find("span.red_tips").hide();
                    $(ss).parent().find("span.green_tips").show();
                    idcardtrue=1;
                }
            });
    return false;
}
function RegeMatch(ss){
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
           /* set_poput_yanzhen("密码可用",true);*/
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            oldpwd=1;
            // if($(ss).val()==$(mm).val()){
            //     $(mm).parent().find("span.red_tips").hide();
            //     $(mm).parent().find("span.green_tips").show();
            // }else {
            //     if($(mm).val() != "" && $(mm).val() != null){
            //         $(mm).parent().find("span.green_tips").hide();
            //         $(mm).parent().find("span.red_tips").show().children("span").text("密码不一致");
            //     }
            //
            // }
        }else{
     /*       set_poput_yanzhen("密码格式不正确",false);*/
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            // if($(mm).val() != "" && $(mm).val() != null){
            //     $(mm).parent().find("span.green_tips").hide();
            //     $(mm).parent().find("span.red_tips").show().children("span").text("密码不一致");
            // }
            return false;
        }
    }
}
function newpwdReg(ss,mm) {
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
           /* set_poput_yanzhen("密码可用",true);*/
            if($(ss).val()==$(mm).val()){
                $(ss).parent().find("span.red_tips").hide();
                $(ss).parent().find("span.green_tips").show();
                newpwd=1;
            }else{
                $(ss).parent().find("span.green_tips").hide();
                $(ss).parent().find("span.red_tips").show().children("span").text("密码不一致");
            }

        }else{
            $(ss).parent().find("span.red_tips").show().children("span").text("密码必须以字母开头,长度为6-18位");
            $(ss).parent().find("span.green_tips").hide();
            /*set_poput_yanzhen("密码格式不正确",false);*/
            return false;
        }
    }
}
function Regdeal(ss) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
           /* set_poput_yanzhen("交易密码可用",true);*/
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            olddeal=1;
        }else{
          /*  set_poput_yanzhen("密码格式不正确",false);*/
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}
function Regnewdeal(ss,mm) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            /*set_poput_yanzhen("交易密码可用",true);*/
            if($(ss).val()==$(mm).val()){
                $(ss).parent().find("span.red_tips").hide();
                $(ss).parent().find("span.green_tips").show();
                newdeal=1;
            }else{
                $(ss).parent().find("span.green_tips").hide();
                $(ss).parent().find("span.red_tips").show().children("span").text("密码不一致");
            }


        }else{
        /*    set_poput_yanzhen("密码格式不正确",false);*/
            $(ss).parent().find("span.red_tips").show().children("span").text("交易密码必须是6位数字");
            $(ss).parent().find("span.green_tips").hide();

            return false;
        }
    }
}
function nextdeal() {
    if(newdeal==1 && olddeal==1 ){
        var $step_div=$("#register_steps").find(".register_step");
        $step_div.removeClass("selected");
        $($step_div[2]).addClass("selected");
    }else {
        set_poput_yanzhen("两次密码不一致",false);
    }
}
function nextren() {
    var newss=$('#input_identity').val();
    var check=$("#agree1_checkbox").attr("value");
    // newss==1 && check==1 && newpwd==1 && oldpwd==1 &&
    if($("#phone_num").val()==""){
        set_poput_yanzhen("账号不可为空",false);
        return false;
    }
    if(phone==0){
        set_poput_yanzhen("该账号已存在",false);
        return false
    }
    if(newss==""){
        set_poput_yanzhen("手机验证码错误无法下一步",false);
        return false
    }
    if( newpwd==0 && oldpwd==1 || newpwd==1 && oldpwd==0 ){
        set_poput_yanzhen("两次密码不一致",false);
        return false
    }

    if(check==""){
        set_poput_yanzhen("请阅读注册协议",false);
        return false
    }
    if(newss!="" && check==1 && newpwd==1 && oldpwd==1 && phone==1){
        var $step_div=$("#register_steps").find(".register_step");
        $step_div.removeClass("selected");
        $($step_div[1]).addClass("selected");
    }
}
function enext() {
    var check=$("#agree2_checkbox").attr("value");
    if($("#email_name").val()==""){
        set_poput_yanzhen("账号不可为空",false);
        return false;
    }
    if(emailname==0){
        set_poput_yanzhen("该账号已存在",false);
        return false
    }
    if(emailcode==""){
        set_poput_yanzhen("邮箱验证码错误无法下一步",false);
        return false
    }
    if( newpwd==0 && oldpwd==1 || newpwd==1 && oldpwd==0 ){
        set_poput_yanzhen("两次密码不一致",false);
        return false
    }

    if(check==""){
        set_poput_yanzhen("请阅读注册协议",false);
        return false
    }
    if(check==1 && oldpwd==1 && newpwd==1 && emailname==1 &&  emailcode==1){
        var $step_div=$("#register_steps").find(".register_step");
        $step_div.removeClass("selected");
        $($step_div[1]).addClass("selected");
    }
}
/*email find pw email reg*/
function emailfindpw(ss) {
    var pattern = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
    if(pattern.test($(ss).val())){
        $(ss).parent().find("span.red_tips").hide();
        $(ss).parent().find("span.green_tips").show();
        $.ajax({
            url:"/Home`Register`regemailnamezhaohui",
            type:"post",
            data:{email:$(ss).val()},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_yanzhen(data.info,false);
                    return false
                }
                set_poput_yanzhen(data.info,true);
                emailname=1;
            }
        });
    }else {
        $(ss).parent().find("span.red_tips").show();
        $(ss).parent().find("span.green_tips").hide();
        return false;
    }
}




