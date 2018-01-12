/**
 * Created by asus on 2017/6/24.
 */
$(function () {
    $('.ajax_form').find('button[type=submit]').click(function () {
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
                    set_poput_code(data.info,false);
                    return false
                }
                set_poput_code(data.info,true);
            }
        });
        return false
    })
    $('.ajax_back').find('button[type=submit]').click(function () {
        var form=$(this).parents().parent('.ajax_back');
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
    $('.ajaxlogin_form').find('button[type=submit]').click(function () {
        var href=$('.property_centre').attr('href');
        var form=$(this).parents().parent('.ajaxlogin_form');
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
                    set_poput_yanzhen(data.info,false);
                    return false
                }
                set_poput_yanzhen(data.info,true);
                location.href=href;
            }
        });
        return false
    })
});
$(function () {
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
                    set_poput_code(data.info,false);
                    return false
                }
                set_poput_code(data.info,true);
            }
        });
        return false
    })
});


function set_poput_yanzhen(title,code){
    $("#qiwen_poput").fadeIn(300);
    if(code){
        $(".tips_content_alert").css({'color':'#45bf4a','display':'block'});
        //$(".qiwen_poput_code").css({'background':'#45bf4a'});
    }else{
        //$(".qiwen_poput_code").css({'background':'red'});
        $(".tips_content_alert").css({'color':'#FA4223','display':'block'});
    }
    $(".qiwen_poput_code").html(title);
    //$(".qiwen_poput_code").show(500);
    setTimeout(function () {
        //$(".qiwen_poput_code").hide(500);
        $("#qiwen_poput").fadeOut(300);
    }, 3000);
}