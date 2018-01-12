/**
 * Created by DENG on 2017/7/13.
 */


$(function () {
    $('#banks').change(function () {//银行卡选择
        // var url = "?s=/Home`Property`rmbwithdrawal";
        var url = "/Home`Property`rmbwithdrawal";
        var value=$("#banks option:selected").val();
        $.ajax({
            url: url,
            type: "POST",
            data: {value:value},
            success: function (data) {
                $("#bankname").val(data);
            }

        })
    })

    // $('#quick_out').click(function () {
    //     if($('#quick_out').is(":checked")==true) {
    //         $.ajax({
    //             url:"index.php/Home/Property/takecase",
    //             type:'post',
    //             data:{choice:'2'},
    //             dataType:'json'
    //         })
    //     }
    // });

    // $('#normal_out').click(function () {
    //     if($('#normal_out').is(":checked")==true) {
    //         $.ajax({
    //             url:"index.php/Home/Property/takecase",
    //             type:'post',
    //             data:{choice:'1'},
    //             dataType:'json'
    //         })
    //     }
    // });

})



var yanjzetime=0;

function yanzhen() {//验证码

    var cipher_code=$('#input_identify_code').val();//验证码比对
    var yanzheng_num=$('#yanzheng_num').val();//验证码
    if(yanjzetime==0){
    $.ajax({
        // url:"index.php/Home/Photoma/sms",
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
                        $('#input_identify_code').html(yanjzetime+"秒")
                        $('#input_identify_code').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#input_identify_code').html("免费获取验证码");
                        $('#input_identify_code').css({"pointer-events": "auto" }); //移除click
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


//
//     $('#normal_out').prop('checked',function () {
//         $.ajax({
//             url:"index.php/Home/Property/rmbtakeout",
//             type:'post',
//             data:{choice:'2'},
//             dataType:'json'
//         })
//     })

