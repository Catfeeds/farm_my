/**
 * Created by asus on 2017/7/5.
 */
var oldpwd=0;
var newpwd=0;
var olddeal=0;
var newdeal=0;
var apply=0;
var wechat=0;
var bankre=0;
var bankphone=0;
var yanjzetime=0;
$('#confirm_delete').click(function () {
    var id=$('#confirm_delete').parent().parent().parent().find('.user_info').attr('value');
    var deal=$('.body').find('input[type=password]').val();
    $.post("/Home`Safety`deletebank",{id:id,deal:deal},function (data) {
        if (data.status!=true){   //请求失败
            set_poput_code(data.info,false);
            return false
        }
        set_poput_code(data.info,true);
    })
})
$('#deletebank').click(function () {
    var id=$('#confirm_delete').attr('value');
    // var deal=$('.body').find('input[type=password]').val();
    
})
function tijiaophone(ss) {
    var phone_num=$('#phone_num').val();
    var cipher_code=$(ss).val();
    if($(ss).val() != "" && $(ss).val() != null){
        $.ajax({
            url:"/Home`Register`reg",
            type:"post",
            data:{phone_num:phone_num,cipher_code:cipher_code},
            dataType:'json',
            success:function (data) {
                if (data.status==true){
                    //set_poput_yanzhen(data.info,true);
                    $(ss).parent().find("span.red_tips").hide();
                    $(ss).parent().find("span.green_tips").show();
                    bankphone=1;
                }else{
                    //set_poput_code(data.info,false);
                    $(ss).parent().find("span.red_tips").show();
                    $(ss).parent().find("span.green_tips").hide();
                    return false
                }
            }
        });
    }
}
function regbankphone() {

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
                yanjzetime=59;
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
function jiechuWap() {
    var phone_num=$('#get_identify_code_unbind').val();
    var cipher_code=$('#cipher_code_unbind').val();
    $.ajax({
        url:"/Wap`Safety`jiechu",
        type:"POST",
        dataType:'json',
        data:{phone_num:phone_num,cipher_code:cipher_code},
        async: false,
        success:function (data) {
            if(data.status==true){
                set_poput_code(data.info,true);
            }else {
                set_poput_code(data.info,false);
                return false;
            }

        }
    });
}
function jiechuphone() {
    var phone_num=$('#get_identify_code_unbind').val();
    var cipher_code=$('#cipher_code_unbind').val();
    $.ajax({
        url:"/Home`Safety`jiechu",
        type:"POST",
        dataType:'json',
        data:{phone_num:phone_num,cipher_code:cipher_code},
        async: false,
        success:function (data) {
            if(data.status==true){
                set_poput_code(data.info,true);
            }else {
                set_poput_code(data.info,false);
                return false;
            }

        }
    });
}
function phonejiechu() {
    var phone_num=$('#get_identify_code_unbind').val();
    var cipher_code=$('#get_identify_code').val();
    var yanzheng_num=$('#yanzheng_num_unbind').val();
    if(yanjzetime==0){
        $.post("/Wap`Photoma`sms",{phone_num:phone_num,yanzheng_num:yanzheng_num},function (data) {
            if(data.status==true){
                set_poput_yanzhen(data.info,true);
                yanjzetime=59;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#get_identify_code').html(yanjzetime+"秒")
                        $('#get_identify_code').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#get_identify_code').html("免费获取验证码");
                        $('#get_identify_code').css({"pointer-events": "auto" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }else {
                set_poput_yanzhen(data.info,false);
            }
        })

    }

}
function jiechureg() {
//        get_identify_code
    var phone_num=$('#get_identify_code_unbind').val();
    var cipher_code=$('#get_identify_code').val();
    var yanzheng_num=$('#yanzheng_num_unbind').val();
    if(yanjzetime==0){
        $.post("/Home`Photoma`sms",{phone_num:phone_num,yanzheng_num:yanzheng_num},function (data) {
            if(data.status==true){
                set_poput_yanzhen(data.info,true);
                yanjzetime=59;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#get_identify_code_unbind').html(yanjzetime+"秒")
                        $('#get_identify_code_unbind').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#get_identify_code_unbind').html("免费获取验证码");
                        $('#get_identify_code_unbind').css({"pointer-events": "auto" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }else {
                set_poput_yanzhen(data.info,false);
                $('.yzm_zj').find('span').trigger("click")
            }
        })

    }


}
function phoneyanzheng() {
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#get_identify_code').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0){
        $.post("/Wap`Photoma`sms",{phone_num:phone_num,cipher_code:cipher_code,yanzheng_num:yanzheng_num},function (data) {
            if(data.status==true){
                set_poput_yanzhen(data.info,true);
                yanjzetime=59;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#get_identify_code').html(yanjzetime+"秒")
                        $('#get_identify_code').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#get_identify_code').html("免费获取验证码");
                        $('#get_identify_code').css({"pointer-events": "auto" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }else {
                set_poput_yanzhen(data.info,false);
            }
        })

    }
}
function yanzheng() {
//        get_identify_code
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#get_identify_code').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0){
        $.post("/Home`Photoma`sms",{phone_num:phone_num,cipher_code:cipher_code,yanzheng_num:yanzheng_num},function (data) {
            if(data.status==true){
                set_poput_yanzhen(data.info,true);
                yanjzetime=59;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#get_identify_code').html(yanjzetime+"秒")
                        $('#get_identify_code').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#get_identify_code').html("免费获取验证码");
                        $('#get_identify_code').css({"pointer-events": "auto" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }else {
                set_poput_yanzhen(data.info,false);
                $('.yzm_zj').find('span').trigger("click")
            }
        })

    }


}
function rest() {
//        get_identify_code
    var phone_num=$('#id_card').attr("value");
    var cipher_code=$('#get_identify_code_id').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0){
        $.post("/Home`Safety`reset",{idcard:phone_num,cipher_code:cipher_code,yanzheng_num:yanzheng_num},function (data) {
            if(data.status==true){
                set_poput_yanzhen(data.info,true);
                yanjzetime=59;
              var ss=  setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#get_identify_code_id').html(yanjzetime+"秒")
                        $('#get_identify_code_id').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#get_identify_code_id').html("免费获取验证码");
                        $('#get_identify_code_id').css({"pointer-events": "auto" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }else {
                set_poput_yanzhen(data.info,false);
                $('.yzm_zj').find('span').trigger("click")
            }
        })
    }
   

}
$('.ajaxpass_form').find('button[type=submit]').click(function () {
    var form=$(this).parents().parent('.ajaxpass_form');
    if (form.get(0)==undefined){
        form=$(this).parent();
    }
    $.ajax({
        url:form.attr('action'),
        type:form.attr('method'),
        data:form.serialize(),
        dataType:'json',
        success:function (data) {
            if (data!=true){   //请求失败
                set_poput_code(data.info,false);
                return false
            }else {
                $("#reset_trade_pw").show();
                $("#find_tradePw_form").hide();
                return false
            }
//                set_poput_code(data.info,true);
//
        }
    });
    return false
});
function Regbankss(ss) {
    var pattern = /^([1-9]{1})(\d{15,20})$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            bankre=1;
            //set_poput_yanzhen("银行卡号格式正确",true);
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
        }else{
            //set_poput_yanzhen("银行卡号格式不正确",false);
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }

    }else{
        set_poput_yanzhen("银行卡号不可为空",false);
        return false;
    }
}
function tijiaobank() {
    if($('#deposit_bank').val()==""){
        set_poput_yanzhen("开户银行不能为空",false);
        return false;
    }
    if($('#bank_number').val()==""){
        set_poput_yanzhen("银行卡号不能为空",false);
        return false;
    }
    if($('#phone_num').val()==""){
        set_poput_yanzhen("手机号码不能为空",false);
        return false;
    }
    if($('#yanzheng_num').val()==""){
        set_poput_yanzhen("验证码不能为空",false);
        return false;
    }
    if($('#input_identity').val()==""){
        set_poput_yanzhen("手机验证码不能为空",false);
        return false;
    }
    if($('#bank_trade_pw').val()==""){
        set_poput_yanzhen("交易密码不能为空",false);
        return false;
    }
     if(bankre==0 && olddeal==1){
        set_poput_yanzhen("银行卡号格式不正确",false);
         return false;
    }
    if(bankre==1 && olddeal==0){
        set_poput_yanzhen("交易密码格式不正确",false);
        return false;
    }
    if(bankre==1 && olddeal==1 && bankphone==1){
        var form=$('#ajaxbank').parents().parent('.ajaxa_form');
        if (form.get(0)==undefined){
            form=$('#ajaxbank').parent();
        }
        $.ajax({
            url:form.attr('action'),
            type:form.attr('method'),
            data:form.serialize(),
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_code(data.info,false);
                    return false
                }
                set_poput_code(data.info,true);
                olddeal=0;
                bankre=0;
            }
        });
        return false
    }
}
function returndeal() {
    var newdealobj=$('#new_trade_num').val();
    var newdealobj2=$('#re_new_trade').val()
    if (newdealobj==newdealobj2){
            var form=$('#reset_trade_btn').parents().parent('.ajaxa_form');
            if (form.get(0)==undefined){
                form=$('#reset_trade_btn').parent();
            }
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:form.serialize(),
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        set_poput_code(data.info,false);
                        return false
                    }
                    set_poput_code(data.info,true);
                }
            });
            return false

    }else{
        set_poput_yanzhen("两次密码不一致",false);
    }
}


function Renphone(ss) {
    var pattern = /^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            //set_poput_yanzhen("手机号可用",true);
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            $('.ajaxa_form').find('button[type=submit]').click(function () {
                var form=$(this).parents().parent('.ajaxa_form');
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
                            set_poput_code(data.info,false);
                            return false
                        }
                        set_poput_code(data.info,true);
                    }
                });
                return false
            })
        }else{
           // set_poput_yanzhen("手机号不正确，必须11位手机格式数字",false);
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}

function keyRenphone(ss) {
    var pattern = /^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
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
function Regwechat(ss) {
    var pattern = /^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}$|^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|^1(3|4|5|7|8)\d{9}$|^[1-9][0-9]{4,9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            set_poput_yanzhen("微信账号可用",true);
            wechat=1;
        }else{
            set_poput_yanzhen("微信账号格式不正确",false);
            return false;
        }
    }
}
function ajaxwechat() {
    if(wechat==1 && olddeal==1){
        var form=$('#bind_wechat_btn').parents().parent('.ajaxa_form');
        if (form.get(0)==undefined){
            form=$('#bind_wechat_btn').parent();
        }
        $.ajax({
            url:form.attr('action'),
            type:form.attr('method'),
            data:form.serialize(),
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_code(data.info,false);
                    return false
                }
                set_poput_code(data.info,true);
                olddeal=0;
                wechat=0;
            }
        });
        return false
    }else if(wechat==0 && olddeal==1){
        set_poput_yanzhen("账号格式不正确",false);
    }else if(wechat==1 && olddeal==0){
        set_poput_yanzhen("交易密码格式不正确",false);
    }
}
function Regapply(ss) {
    var pattern = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+|^1(3|4|5|7|8)\d{9}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            set_poput_yanzhen("支付宝账号可用",true);
            apply=1;
        }else{
            set_poput_yanzhen("支付宝账号格式不正确",false);
            return false;
        }
    }
}
function ajaxapply() {
        if(apply==1 && olddeal==1){
            var form=$('#bind_alipay_btn').parents().parent('.ajaxa_form');
            if (form.get(0)==undefined){
                form=$('#bind_alipay_btn').parent();
            }
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:form.serialize(),
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        set_poput_code(data.info,false);
                        return false
                    }
                    set_poput_code(data.info,true);
                    olddeal=0;
                    apply=0;
                }
            });
            return false
    }else if(apply==0 && olddeal==1){
            set_poput_yanzhen("账号格式不正确",false);
        }else if(apply==1 && olddeal==0){
            set_poput_yanzhen("交易密码格式不正确",false);
        }
}
function Reg(ss) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            //set_poput_yanzhen("交易密码可用",true);
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            olddeal=1;
        }else{
            //set_poput_yanzhen("密码格式不正确",false);
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}
function newdealReg(ss,mm) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            /* set_poput_yanzhen("交易密码可用",true);*/
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            if($(ss).val()==$(mm).val()){
                $(mm).parent().find("span.red_tips").hide();
                $(mm).parent().find("span.green_tips").show();
            }else {
                if($(mm).val() != "" && $(mm).val() != null){
                    $(mm).parent().find("span.green_tips").hide();
                    $(mm).parent().find("span.red_tips").show().children("span").text("密码不一致");
                }
            }
            olddeal=1;
        }else{
            /*  set_poput_yanzhen("密码格式不正确",false);*/
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            if($(mm).val() != "" && $(mm).val() != null){
                $(mm).parent().find("span.green_tips").hide();
                $(mm).parent().find("span.red_tips").show().children("span").text("密码不一致");
            }

            return false;
        }
    }
}
function renewdealReg(ss,mm) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            /*set_poput_yanzhen("交易密码可用",true);*/
            if($(ss).val()==$(mm).val()){
                $(ss).parent().find("span.red_tips").hide();
                $(ss).parent().find("span.green_tips").show();
            }else{
                $(ss).parent().find("span.green_tips").hide();
                $(ss).parent().find("span.red_tips").show().children("span").text("密码不一致");
            }

            newdeal=1;
        }else{
            /*    set_poput_yanzhen("密码格式不正确",false);*/
            $(ss).parent().find("span.red_tips").show().children("span").text("交易密码必须是6位数字");
            $(ss).parent().find("span.green_tips").hide();

            return false;
        }
    }
}
function tijiaodeal() {
    var newdealobj=$('#new_trade_pw').val();
    var newdealobj2=$('#re_new_trade_pw').val()
    if (newdealobj==newdealobj2){
        newdeal=1;
        if(newdeal==1 && olddeal==1){
            var form=$('#trade_pw_btn').parents().parent('.ajaxa_form');
            if (form.get(0)==undefined){
                form=$('#trade_pw_btn').parent();
            }
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:form.serialize(),
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        set_poput_code(data.info,false);
                        return false
                    }
                    set_poput_code(data.info,true);
                     olddeal=0;
                     newdeal=0;
                }
            });
            return false
        }
    }else{
        set_poput_yanzhen("两次密码不一致",false);
    }
}
function RegeMatch(ss){
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            //set_poput_yanzhen("密码可用",true);
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            oldpwd=1;
        }else{
            //set_poput_yanzhen("密码格式不正确",false);
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}
function newpwdReg(ss,mm) {
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            /* set_poput_yanzhen("密码可用",true);*/
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            oldpwd=1;
            if($(ss).val()==$(mm).val()){
                $(mm).parent().find("span.red_tips").hide();
                $(mm).parent().find("span.green_tips").show();
            }else {
                if($(mm).val() != "" && $(mm).val() != null){
                    $(mm).parent().find("span.green_tips").hide();
                    $(mm).parent().find("span.red_tips").show().children("span").text("密码不一致");
                }

            }
        }else{
            /*       set_poput_yanzhen("密码格式不正确",false);*/
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            if($(mm).val() != "" && $(mm).val() != null){
                $(mm).parent().find("span.green_tips").hide();
                $(mm).parent().find("span.red_tips").show().children("span").text("密码不一致");
            }
            return false;
        }
    }
}
function renewpwReg(ss,mm) {
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            /* set_poput_yanzhen("密码可用",true);*/
            if($(ss).val()==$(mm).val()){
                $(ss).parent().find("span.red_tips").hide();
                $(ss).parent().find("span.green_tips").show();
            }else{
                $(ss).parent().find("span.green_tips").hide();
                $(ss).parent().find("span.red_tips").show().children("span").text("密码不一致");
            }
            newpwd=1;
        }else{
            $(ss).parent().find("span.red_tips").show().children("span").text("密码必须以字母开头,长度为6-18位");
            $(ss).parent().find("span.green_tips").hide();
            /*set_poput_yanzhen("密码格式不正确",false);*/
            return false;
        }
    }
}
function Regbanknew(ss) {
    var bankcard=$('#bank_number').val();
    var pattern = /^([1-9]{1})(\d{14}|\d{18})$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            // bankre=1;
            if(bankcard==$(ss).val()){
                set_poput_yanzhen("银行卡号格式正确",true);
            }else {
                set_poput_yanzhen("两次卡号不一致",false);
                return false;
            }

        }else{
            set_poput_yanzhen("银行卡号格式不正确",false);
            return false;
        }

    }else{
        set_poput_yanzhen("银行卡号不可为空",false);
        return false;
    }
}
function tijiaobankphone() {
    if(bankre==1 && olddeal==1){
        var form=$('#ajaxbank').parents().parent('.ajaxa_form');
        if (form.get(0)==undefined){
            form=$('#ajaxbank').parent();
        }
        $.ajax({
            url:form.attr('action'),
            type:form.attr('method'),
            data:form.serialize(),
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_code(data.info,false);
                    return false
                }
                set_poput_code(data.info,true);
                olddeal=0;
                bankre=0;
            }
        });
        return false
    }else if(bankre==0 && olddeal==1){
        set_poput_yanzhen("银行卡号格式不正确",false);
    }else if(bankre==1 && olddeal==0){
        set_poput_yanzhen("交易密码格式不正确",false);
    }else if (bankre==0 && olddeal==0){
        set_poput_yanzhen("请填写完整信息",false);
    }
}
function comparison() {
    var newpwdobj=$('#new_enter_pw').val();
    var newpwdobj2=$('#re_new_enter_pw').val();
    if (newpwdobj==newpwdobj2){
        newpwd=1;
        if(newpwd==1 && oldpwd==1){
                var form=$('#enter_pw_btn').parents().parent('.ajaxa_form');
                if (form.get(0)==undefined){
                    form=$('#enter_pw_btn').parent();
                }
                $.ajax({
                    url:form.attr('action'),
                    type:form.attr('method'),
                    data:form.serialize(),
                    dataType:'json',
                    success:function (data) {
                        if (data.status!=true){   //请求失败
                            set_poput_code(data.info,false);
                            return false
                        }
                        set_poput_code(data.info,true);
                        oldpwd=0;
                        newpwd=0;
                    }
                });
                return false
        }
    }else{
        set_poput_yanzhen("两次密码不一致",false);
    }
}
