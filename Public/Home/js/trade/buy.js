/**
 * Created by yvdedu.com on 2017/8/10.
 */
$(function () {
    setInterval(function () {
        $.ajax({
            url:'/Home`TradeData`getXnb',
            type:'post',
            data:{markethouse:$('li[active]').attr('active')},
            success:function (data) {
                $.each(data,function (k,v) {
                    var li= $('#RMB_area').find('li[name='+v.brief+']');
                    var top_up=(parseFloat(v.now_price)-parseFloat(v.close_price))/parseFloat(v.close_price)*100;

                    if(top_up==0 || isNaN(top_up) || top_up==Infinity){
                        top_up="0.00"
                    }else {
                        top_up=Math.floor(top_up*100)/100;
                    }

                    li.find('.newprice').text(parseFloat(v.currency_transactionrecords_price)).attr('value',parseFloat(v.currency_transactionrecords_price));
                    li.find('.allmoney').text(getnumber(parseFloat(v.numbers*v.avg_price))).attr('value',parseFloat(v.numbers*v.avg_price));
                    li.find('.allnumber').text(getnumber(parseFloat(v.numbers))).attr('value',parseFloat(v.numbers));
                    li.find('.top_up').text(parseFloat(top_up)).attr('value',parseFloat(top_up));
                })
            }
        })
    },5000)
})

function getnumber(int) {
    int= Math.round(int) > 0 ? Math.round(int) : '-';
    var length=int.toString().length;
    if (length>=5 && length<=8){
        return int=parseFloat((int/10000).toFixed(2))+"万";
    }
    if (length>=9){
        return  int=parseFloat((int/100000000).toFixed(2))+'亿';
    }
    return parseFloat(int,2);
}

//$zd=($vo['now_price']-$vo['close_price'])/$vo['close_price'];echo round($zd*100,2);