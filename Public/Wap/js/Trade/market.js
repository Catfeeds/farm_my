/**
 * Created by 48930 on 2017/8/16.
 */
$(function () {

    // 数据请求 卖13，买13数据请求
    setInterval(function () {
        $.ajax({
            url:'/Wap`TradeData`returnXnbEntrust',
            type:'post',
            dataType:'json',
            data:{'xnb':$('input[name=xnb]').val(),'market':$('input[name=market]').val()},
            success:function (data) {
                var buy_tr="";
                var sell_tr="";
                var TrdeWater_tr="";
                if (data.buy_data!=null){
                    $.each(data.buy_data,function (k,v) {   //买13
                        buy_tr+='<li>' +
                            '<span>'+parseFloat(v.num)+'</span>'+
                            '<span>'+parseFloat(v.price)+'</span>'+
                            '</li>';

                    });

                    $('.pan').find('.buypan').html(buy_tr);

                }

                if (data.sell_data!=null){
                    $.each(data.sell_data,function (k,v) {
                        sell_tr+='<li>' +
                            '<span>'+parseFloat(v.price)+'</span>' +
                            '<span>'+parseFloat(v.num)+'</span>' +
                            '</li>';
                    });
                    $('.pan').find('.sellpan').html(sell_tr);
                }

                if (data.TrdeWater!=null){
                    $.each(data.TrdeWater,function (k,v) {
                        TrdeWater_tr+='<li>' +
                            '<span>'+formattype(v.type)+'</span>' +
                            '<span class="matime">' +
                             '<p>'   +
                            (formatDate(v.time))+
                            '</p>'+
                            '<p>'   +
                            (formatDates(v.time))+
                            '</p>'+
                            '</span>'+

                            '<span>'+parseFloat(v.price)+'</span>' +
                            '<span>'+parseFloat(v.number)+'</span>' +
                            '</li>';
                    })
                    $('.market2').find('ul').html(TrdeWater_tr);
                }
            }
        })
    },3000)
    function   formattype(i)   {
        var type=""
        if(i==1){
            type+='<span class="cbuy">买</span>';
        }else{
            type+='	<span class="csell">卖</span>';
        }
        return type
    }
    //时间转换格式,php时间转js时间*1000
    function   formatDate(i)   {
        var   now=new Date()
        now.setTime(i*1000);
        var   year=now.getFullYear();
        var   month=now.getMonth()+1;
        var   date=now.getDate();
        return   year+"-"+month+"-"+date;
    }
    function   formatDates(i)   {
        var   now=new Date()
        now.setTime(i*1000);
        var   hour=now.getHours();
        var   minute=now.getMinutes();
        var   second=now.getSeconds();
        return     hour+":"+minute+":"+second;
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
