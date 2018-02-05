
$("button.add").click(function () {

    var i=true;

    $('[type=number]').each(function () {
            if ($(this).val()>1000 || $(this).val()<=0 || $(this).val()==""){
                set_poput_code('非法操作asdas！',false);
                return  i=false;
            }
    })
    if (!i){
        return false;
    }

    var input_data = new Object();
    $('input').each(function () {
        var k=$(this).attr('name');
        input_data[k]=$(this).val();
    })
    input_data=JSON.stringify(input_data)

    $.ajax({
        url:"",
        type:'post',
        dataType: "json",
        data:{data:input_data},
        success:function (data) {
            if (data.status!=true){   //请求失败
                set_poput_code(data.info,false);
                return false
            }
            set_poput_code(data.info,true);
        },
        error:function () {
            set_poput_code('系统错误，请联系我们！up',false);
            return false
        }
    })


})