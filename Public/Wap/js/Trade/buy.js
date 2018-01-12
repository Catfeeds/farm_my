/**
 * Created by 48930 on 2017/8/15.
 */
$(function () {

    setInterval(function () {
        $.ajax({
            url:'/Wap`TradeData`getXnbTede',
            type:'post',
            dataType:'json',
            data:{'market':$('input[name=market]').val()},
            success:function (data) {
                var jie="";
                    $.each(data,function (k,v) {
                        if(v.id != undefined){
                        jie+='<li>'+
                            '<a class="buya" onclick="asd(this)" value='+v.id+' type='+data.mark+'>'+
                            '<div class="district-title">'+
                            '<div class="district-title-left">'+
                            '<img src='+v.imgurl+'>'+
                            '<span>'+v.name+'('+v.brief+')'+'</span>'+
                            '</div>'+
                            '<div class="district-title-right">'+
                            '<img src="./Public/Wap/img/right1.png"/>'+
                            '</div>'+
                            '</div>'+
                            '<div class="district-cost">'+
                            '<span class="district-cost-price">'+
                            '<span  type="new_price" value='+getnewprice(v.new_price)+'>'+
                            ben(data.pd)+getnewprice(v.new_price)+
                            '</span>'+
                            '</span>'+
                            '<span class="district-grow ce34c48" type="up_dow" value="{$zd}">'+
                            linefu(v.oldprice,v.new_price)+'%'+
                            '</span>'+
                            '</div>'+
                            '<dl class="district-info">'+
                            '<dt>'+
                            '<p>最高价</p>'+
                            '<p>'+
                            ben(data.pd)+prices(v.max_price)+
                            '</p>'+
                            '</dt>'+
                            '<dt>'+
                            '<p>最低价</p>'+
                            '<p>'+
                            ben(data.pd)+prices(v.min_price)+
                            '</p>'+
                            '</dt>'+
                            '<dt>'+
                            '<p>成交量</p>'+
                            '<p type="allnumber" value='+prices(v.smum_number)+'>'+
                            prices(v.smum_number)+
                            '</p>'+
                            '</dt>'+
                            '<dt>'+
                            '<p>成交额</p>'+
                            '<p type="allmoney" value='+jiaoe(v.avg_price,v.smum_number)+'>'+
                            jiaoe(v.avg_price,v.smum_number)+
                            '</p>'+
                            '</dt>'+
                            '</dl>'+
                            '</a>'+
                            '</li>';
                        $('.us').html(jie);
                        }
                    })


            }
        })
    },5000)
});
//跳转
function asd(ss) {
    var ids=$(ss).attr("value");
    var idas=$(ss).attr("type");
    location.href='/Wap`Trade`trade`xnb`'+ids+'`marke`'+idas+''
}
//本位币
function ben(int) {
    var str="";
    if(int==1){
        str='￥'
    }
    return str;
}
//成交额
function jiaoe(int,num) {
    var str=int*num;
    var ssw="";
    if(str==""){
        ssw+='0.00'
    }else{
        str=Math.floor(str*100)/100
        ssw+=getnumber(str)
    }
    return ssw;
}
//数据处理
function prices(int) {

    int=parseFloat(int);

    var str="";
    if( isNaN(int)||int==null  ){

        var num = '0.00';
        num= parseFloat(num).toFixed(2);
        str=num;
        if(isNaN(getnumber(str))){
            str=num;
        }
    }else{
        int= Math.floor(int*100)/100;
        str+=(int)
    }

    return str;
}
//涨幅
function linefu(old,newp) {
    var zd=(parseFloat(newp)-parseFloat(old))/parseFloat(old)*100;
    if(zd/100==0 || isNaN(zd/100)){
        zd="0"
    }else {
        zd=Math.floor(zd*100)/100;
    }

    return zd;
}
//new_price 判断
function getnewprice(int) {
    var str="";
    if(int==""){
        // value="{:floatval($vo['new_price'])}"
        str+=0.00;
    }else{
        int=Math.floor(int*1000000)/1000000;
        str+=int;
    }
    return str;
}
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