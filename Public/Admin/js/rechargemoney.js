/**
 * Created by 48930 on 2017/7/14.
 */
var href;
$(function () {

    $('.ajax_money').find('button[type=submit]').click(function () {
        var form=$(this).parents().parent('.ajax_money');
        if (form.get(0)==undefined){
            form=$(this).parent();
        }
        var userid=$('#Recharge_id').val();
        var username=$('#Recharge_user').val();
        var ruiduserid=$('#userid').attr('value');
        var ruidusername=$('#username').attr('value');
        var cny=$('#cny').val();
        var xnb=$('.xnb').val();
        // console.log(xnb);
        href=document.referrer;
        if(cny<0){
            set_poput_code("充值金额不可为负数",false);
        }else{
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:{userid:userid,username:username,ruiduserid:ruiduserid,ruidusername:ruidusername,cny:cny,href:href,xnb:xnb},
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        set_poput_code(data.info,false);
                        return false
                    }
                    set_poput_code(data.info,true);

                }
            });
        }

        return false
    })
})
$('#Recharge_user').change(function () {
    $.ajax({
        url:"/Admin`Userqian`reuid",
        type:"post",
        data:{username:$(this).val(),userid:""},
        dataType:'json',
        success:function (data) {
            if (data.length==1){
                //请求失败
                $('#Recharge_id').val(data[0].id)
                return true;
            }else{
                set_poput_yanzhen("充值关联失败",false)
            }

        }
    });
})
$('#Recharge_id').change(function () {
    $.ajax({
        url:"/Admin`Userqian`reuid",
        type:"post",
        data:{userid:$(this).val(),username:""},
        dataType:'json',
        success:function (data) {
            if (data.length==1){
                //请求失败
                $('#Recharge_user').val(data[0].users)
                return true;
            }else{
                set_poput_yanzhen("充值关联失败",false)
            }

        }
    });
})
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