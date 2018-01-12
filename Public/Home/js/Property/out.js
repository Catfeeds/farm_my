/**
 * Created by DENG on 2017/7/13.
 */

$(function () {

    // 选定某个虚拟币 加载的信息
    $("#case").find('tr').find('td').click(function () {
        console.log('ssssssss');
        var alen = $(this).children('span:last-child').html();
        $.ajax({
            url:"/Home`Property`get_currency",
            data:{case:alen},
            type:"post",
            success:function (data) {
                $('#buy_xnb').html(parseFloat(data.cny)>0?data.cny: '0.00');  //可用虚拟币
                $('#no_xnb').html(parseFloat(data.move)>0?data.move: '0.00');   //冻结虚拟币
            }
        })
    });


   // 插入转出虚拟币信息 请求
    $('#submit').click(function () {
        var i = true;
        $('[name=address],[name=number],[name=password],[name=yanzheng_num],[name=input_identity]').each(function () {
            if ($(this).val() == "") {
                return i = false
            }
        })
        if (!i) {
            set_poput_code('请输入完整信息！', false);
            return i;
        }

        $.ajax({
            url: "/Home`Property`order",
            data: {
                'bid': $('#ids').val(),
                'address': $('#address').val(),
                'number': $('#number').val(),
                'password': $('#password').val(),
                'phonenum': $('#input_identity').val()
            },
            type: "post",
            success: function (data) {
                if (data.status != true) {   //请求失败
                    set_poput_code(data.info, false);
                    return false;
                }
                set_poput_code(data.info, true);
            }
        })
    });
})

var yanjzetime=0;

function yanzhen() {//验证码

    var cipher_code=$('#input_identify_code').val();//验证码比对
    var yanzheng_num=$('#yanzheng_num').val();//验证码
    if(yanjzetime==0){
        $.ajax({
            url:"/Home`Photoma`sms",
            type:"post",
            data:{phone_num:cipher_code,yanzheng_num:yanzheng_num},
            dataType:'json',
            success:function (data) {
                if(data.status==true){
                    set_poput_yanzhen(data.info,true);
                    yanjzetime=57;
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

            }
        })
    }

}

function gophone(ss) {
    // var phone_num=$('#phone_num').val();//电话号码
    var cipher_code=$(ss).val();//电话号码比对信息
    if($(ss).val() != "" && $(ss).val() != null){
        $.ajax({
            url:"/Home`Message`reg",
            type:"post",
            data:{cipher_code:cipher_code},
            dataType:'json',
            success:function (data) {
            }
        });
    }
}