/**
 * Created by 48930 on 2017/8/4.
 */
$(function () {
    rik()
})

/*kline*/
// var xnb=0;
function rik(asds) {
    var dom = document.getElementById("kxiantu");
    var myChart = echarts.init(dom);
    var sef= [];
    var ss=[];
    var seff=[];
    var ssa=[];
    var value=[];
    var day=[];
    var xnb=$('.xnb_name_out').attr("value");
    var mark=$('#market_record').attr("value");
    $.ajax({
        url:"/Home`Trade`echarts",
        type:"POST",
        dataType:'json',
        data:{xnb:xnb,mark:mark},
        success:function (data) {
            ss.push(data);
            for(var ssd in ss[0].buy_data){
                sef.push(ssd);
            }
            for(var sd in ss[0].sell_data){
                ssa.push(sd);
            }
            for(var jk=0;jk<sef.length;jk++){
                seff.push(ss[0].buy_data[jk].price)
            }
            var xAxisData = [];
            var value2=[];
            for(var j=0;j<seff.length;j++){
                xAxisData.push(seff[j]);
                value.push(ss[0].buy_data[j].num)
                value2.push(0);
            }

            for(var jh=0;jh<ssa.length;jh++){
                xAxisData.push(ss[0].sell_data[jh].price)
                value2.push(ss[0].sell_data[jh].num);
            }
            option = {
                // title: {
                //     text: '柱状图动画延迟'
                // },
                legend: {
                    data: ['累计买单', '累计卖单'],
                    align: 'left'
                },
                toolbox: {
                    // y: 'bottom',
                    // feature: {
                    //     magicType: {
                    //         type: ['stack', 'tiled']
                    //     },
                    //     dataView: {},
                    //     saveAsImage: {
                    //         pixelRatio: 2
                    //     }
                    // }
                },
                tooltip: {},
                xAxis: {
                    data: xAxisData,
                    silent: false,
                    splitLine: {
                        show: false
                    }
                },
                yAxis: {
                },
                series: [{
                    name: '累计买单',
                    type: 'bar',
                    data: value,
                    // animationDelay: function (idx) {
                    //     return idx * 10;
                    // }
                }, {
                    name: '累计卖单',
                    type: 'bar',
                    data: value2,
                    // animationDelay: function (idx) {
                    //     return idx * 10 + 100;
                    // }
                }],
                // animationEasing: 'elasticOut',
                // animationDelayUpdate: function (idx) {
                //     return idx * 5;
                // }
            };
//             var options = {
//                 scaleOverride: false, //是否用硬编码重写y轴网格线
//                 scaleSteps: 0, //y轴刻度的个数
//                 scaleStepWidth: 2, //y轴每个刻度的宽度
//                 scaleStartValue: null, //y轴的起始值
//                 pointDot: true, //是否显示点
//                 pointDotRadius: 0, //点的半径
// //            scaleShowGridLines: false,//是否网格线
//                 scaleLineColor : "rgba(0,0,0,.1)",
//                 pointDotStrokeWidth: 0, //点的线宽
//                 datasetStrokeWidth: 1, //数据线的线宽
//                 scaleLineWidth : 1,
// //            scaleLineWidth : 0.01,
//                 bezierCurve : false,//变成折线
//                 scaleFontFamily : "'Arial'",  // 字体
//                 scaleFontSize : 20,        // 文字大小
//                 scaleFontStyle : "normal",  // 文字样式
//                 scaleFontColor : "#666",    // 文字颜色
//                 scaleShowGridLines : true,   // 是否显示网格
// //            scaleFontSize : 0,
// //            scaleFontColor : "white",
//                 gridLineWidth: 0,
// //            scaleShowLabels:true,
//                 pointHitDetectionRadius:1,
//                 datasetStroke : true,        // 数据集行程
//                 scaleGridLineWidth : 2,      // 网格宽度
//
//                 // responsive: false,xLabelsSkip:0,
//             };
//             var LineChart = {
//                 labels: day,
//                 datasets: [{
//                     fillColor : "rgba(250, 66, 35, 0.6)",
// //          strokeColor: "black",
//                     data: value
//                 }
//                     ,{
//                         fillColor : "rgba(65, 245, 22, 0.4)",
// //              strokeColor: "black",
//                         data: []
//                     }]
//             };
            myChart.setOption(option);


            // new Chart(ctx).Line(LineChart, options);
        }
    });


}