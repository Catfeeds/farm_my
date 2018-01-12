$(function () {
    //传送id 的方法！
    $('.status').click(function () {
        var id = new Array();
        $('input.ids').each(function () {
            if ($(this).is(':checked')){
                id.push($(this).val())
            }
        })
        $.ajax({
            url:$(this).attr('url'),
            type:'post',
            data:{id:id,type:$(this).attr('types')},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_code(data.info,false);
                    return false
                }
                set_poput_code(data.info,true);
            }
        })
    })


    $('.ajax-a').click(function () {
        $.ajax({
            url:$(this).attr('href'),
            type:'post',
            data:{id:$(this).attr('ids'),type:$(this).attr('types')},
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_code(data.info,false);
                    return false
                }
                set_poput_code(data.info,true);
            }
        })
        return false;
    })

})
