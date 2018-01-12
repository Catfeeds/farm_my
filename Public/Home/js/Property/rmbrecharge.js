/**
 * Created by DENG on 2017/7/18.
 */

//充值方式

$(function () {
    $("#ToRecharge").click(function(){
        var value=$('.dianji').val();
        var cny_money=$('#cny_money').val();
        var shoukuan=$('#shoukuan').text();
        var shoukuanbank=$('#shoukuanbank').text();
        var payee = $('#payee').text();
        var fukuan=$('#fukuan').text();
        // console.log(shoukua)
        var exp = /^([1-9][\d]{0,7}|0)(\.[\d]{1,2})?$/;
        if (cny_money < 200) {
            set_poput_yanzhen('充值金额不能小于200',false);
            return false;
        }
        if(exp.test(cny_money)){
            $.ajax({
                // url:"index.php?s=/Home/Property/bomb",
                url:"/Home`Property`bomb",
                data:{case:value,cny_money:cny_money,shoukuan:shoukuan,fukuan:fukuan,payee:payee,shoukuanbank:shoukuanbank},
                type:"post",
                success:function (data) {
                    if(data. rechargetype==1){
                        $('#PrepaidBox').show();
                        $('#alipay_cny').html(data.money);
                        $("#order_alipay").html(data.order);
                        $("#order_alipays").html(data.order);
                    }
                    if(data. rechargetype==2){
                        $('#wechat_show').show();
                        $('#wechat_cny').html(data.money);
                        $("#order_wechat").html(data.order);
                        $("#order_wechats").html(data.order);
                    }
                    if(data. rechargetype==3){
                        $('#bank_recharge').show();
                        $('#money_cny').html(data.money);
                        $('#order_cny').html(data.order);
                    }
                }
            })
        }else{
            set_poput_yanzhen('请输入正确金额',false);
        }

    });

    $('.look').click(function () {
        var string=$(this).attr('value');
        var type=$('#type').attr('value');
        // console.log(type);
        $.ajax({
            url:"/Home`Property`xnboutlook",
            data:{id:string,type:type},
            type:"post",
            success:function (data) {
                if(data. rechargetype==1){
                    $('#PrepaidBox').show();
                    $('#alipay_cny').html(data.money);
                    $("#order_alipay").html(data.order);
                    $("#order_alipays").html(data.order);
                }
                if(data. rechargetype==2){
                    $('#wechat_show').show();
                    $('#wechat_cny').html(data.money);
                    $("#order_wechat").html(data.order);
                    $("#order_wechats").html(data.order);
                }
                if(data. rechargetype==3){
                    $('#bank_recharge').show();
                    $('#money_cny').html(data.money);
                    $('#order_cny').html(data.order);
                }
            }
        })
    })
})

