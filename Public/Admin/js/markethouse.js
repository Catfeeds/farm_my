/**
 * Created by 48930 on 2017/8/4.
 */

$(function () {
    rik()
})

/*kline*/
function rik(asds) {
    var ctx = document.getElementById('kxiantu').getContext("2d");
    var sef= [];
    var ss=[];
    var xnb=$('#market_select').val();
    $.ajax({
        url:"/Admin`Interlinkage`markhouse",
        type:"POST",
        dataType:'json',
        data:{xnb:xnb},
        async: false,
        success:function (data) {
            ss.push(data);
        }
    });

    for(var ssd in ss[0]){
        sef.push(ssd);
    }
    console.log(sef)
    var value=[];
    var value2=[];
    for(var i=0;i<sef.sort().length;i++){
        if(ss[0][sef[i]].buy==undefined){
            ss[0][sef[i]].buy=0
        }
        if(ss[0][sef[i]].sell==undefined){
            ss[0][sef[i]].sell=0
        }
        value.push(ss[0][sef[i]].buy)
        value2.push(ss[0][sef[i]].sell)
    }

    var options = {
        scaleOverride: false, //是否用硬编码重写y轴网格线
        scaleSteps: 0, //y轴刻度的个数
        scaleStepWidth: 5, //y轴每个刻度的宽度
        scaleStartValue: null, //y轴的起始值
        pointDot: true, //是否显示点
        pointDotRadius: 0, //点的半径
//            scaleShowGridLines: false,//是否网格线
        scaleLineColor : "rgba(0,0,0,.1)",
        pointDotStrokeWidth: 0, //点的线宽
        datasetStrokeWidth: 1, //数据线的线宽
        scaleLineWidth : 1,
//            scaleLineWidth : 0.01,
        bezierCurve : false,//变成折线
        datasetFill: true,
        scaleFontFamily : "'Arial'",  // 字体
        scaleFontSize : 20,        // 文字大小
        scaleFontStyle : "normal",  // 文字样式
        scaleFontColor : "#666",    // 文字颜色
        scaleShowGridLines : true,   // 是否显示网格
//            scaleFontSize : 0,
//            scaleFontColor : "white",
        gridLineWidth: 0,
//            scaleShowLabels:true,
        pointHitDetectionRadius:1,
        datasetStroke : false,        // 数据集行程
        scaleGridLineWidth : 2,      // 网格宽度

        // responsive: false,xLabelsSkip:0,
    };
    var LineChart = {
        labels: sef,
        datasets: [{
            fillColor :  "rgba(241,158,194,0.5)",
//          strokeColor: "red",
            data: value
        }
            ,{
                fillColor : "rgba(0,191,255,0.5)",
//              strokeColor: "black",
                data: value2
            }]
    };

    new Chart(ctx).Line(LineChart, options);
}