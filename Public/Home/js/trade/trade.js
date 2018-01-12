$(function () {

   // 数据请求 卖13，买13数据请求
    setInterval(function () {
        $.ajax({
            url:'/Home`Joggle`getTrdeWater',
            type:'post',
            dataType:'json',
            data:{'id':$('input[name=xnb]').val()},
            success:function (data) {
                var i=1;
                var p;
                if (data.sell_data!=null){
                     p=data.sell_data.length;
                }
                var buy_tr;
                var sell_tr;
                var TrdeWater_tr;

                if (data.buy_data!=null){
                    $.each(data.buy_data,function (k,v) {   //买13
                        buy_tr+='<tr>' +
                            '<td>买' +(i++)+'</td>'+
                            '<td>'+parseFloat(v.price)+'</td>' +
                            '<td>'+parseFloat(v.num)+'</td>' +
                            '<td>￥'+parseFloat((v.price*v.num).toFixed(6))+'</td>' +
                            '</tr>';
                    })
                    $('.buy_num_table tfoot').html(buy_tr);
                }

                if (data.sell_data!=null){
                    $.each(data.sell_data,function (k,v) {
                        var a=v.price*v.num

                        sell_tr+='<tr>' +
                            '<td>卖'+(p--)+'</td>'+
                            '<td>'+parseFloat(v.price)+'</td>' +
                            '<td>'+parseFloat(v.num)+'</td>' +
                            '<td>￥'+parseFloat(a.toFixed(6))+'</td>' +
                            '</tr>';
                    })

                    $('.buy_num_table tbody').html(sell_tr);
                }

                if (data.TrdeWater!=null){
                    $.each(data.TrdeWater,function (k,v) {
                        var type;
                        if(v.type==1){
                            type='买';
                        }else{
                            type='卖';
                        }
                        TrdeWater_tr+='<tr>' +
                            '<td>' +(formatDate(v.time))+'</td>'+
                            '<td>'+type+'</td>' +
                            '<td>'+parseFloat(v.price)+'</td>' +
                            '<td>'+parseFloat(v.number)+'</td>' +
                            '<td>'+parseFloat((v.price*v.number).toFixed(6))+'</td>' +
                            '</tr>';
                    })
                    $('.water').html(TrdeWater_tr);
                }
            }
        })
    },3000)

    //时间转换格式,php时间转js时间*1000
    function   formatDate(i)   {
        var   now=new Date()
        now.setTime(i*1000);
        var   year=now.getFullYear();
        var   month=now.getMonth()+1;
        var   date=now.getDate();
        var   hour=now.getHours();
        var   minute=now.getMinutes();
        var   second=now.getSeconds();
        return   year+"-"+month+"-"+date+"   "+hour+":"+minute+":"+second;
    }

    //买卖数据提交验证！
    $('[name=price],[name=number]').blur(function () {   //正则匹配
        if( $(this).val().match(/^[1-9]\d*$|^[1-9]\d*\.\d*[1-9]{1,6}$|0\.\d*[1-9]{1,6}$/) != $(this).val()) {
            $(this).val("");
        }
    })

    $('.ajax_form_trde').find('button[type=submit]').click(function () {
        //检测非法修改正则匹配
        var i=true;

        $('[name=price],[name=number]').each(function () {
            if( $(this).val().match(/^[1-9]\d*$|^[1-9]\d*\.\d*[1-9]{1,6}$|0\.\d*[1-9]{1,6}$/) != $(this).val()) {
                $(this).val("");
                return i=false;
            }
        })

        var form=$(this).parents().parent('.ajax_form_trde');
        if (form.get(0)==undefined){
            form=$(this).parent();
        }
        if (form.find('[name=price]').val()=="" || form.find('[name=number]').val()==""){
            set_poput_code("非法数据！11",false);
            return i=false;
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
    });

})
