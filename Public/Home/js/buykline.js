var xnb=$('.xnb');
var ss=0;
var ssw=0;
$(function () {
    // ss=$(xnb).attr("value")
    // rrek(ss);
    for_Interva(xnb,100);

})
function for_Interva(lst,timer){
    var index=0;
    var Intertimer=setInterval(function(){
        //操作集合中第lst[index]个;
        //集合小标+1;
        ss=$(xnb[index]).attr("value")
        ssw=$(xnb[index-1]).attr("value")
        rrek(ss);
        index+=1;
        //判断index是否达到集合最后一个
        if(index>=lst.length){
            //如果达到了，就清除定时器，停止循环
            clearInterval(Intertimer);
        }
    },timer);
}
var sads=0;
var value=[];
var ctx;
var options;
var LineChart;
function rrek(asdas) {
    var mark=$('input[name=market]').val();
    var jef=[];
    // ctx = document.getElementById(asdas).getContext("2d");
    // options = {
    //     scaleOverride: false, //是否用硬编码重写y轴网格线
    //     scaleSteps: 0, //y轴刻度的个数
    //     scaleStepWidth: 0, //y轴每个刻度的宽度
    //     scaleStartValue: 0, //y轴的起始值
    //     pointDot: false, //是否显示点
    //     pointDotRadius: 0, //点的半径
    //     scaleShowGridLines: false,//是否网格线
    //     pointDotStrokeWidth: 0, //点的线宽
    //     datasetStrokeWidth: 1, //数据线的线宽
    //     scaleLineWidth : 0.01,
    //     bezierCurve : true,//变成折线
    //     scaleFontSize : 0,
    //     scaleFontColor : "white",
    //     gridLineWidth: 0,
    //     scaleShowLabels:true,
    //     pointHitDetectionRadius:0,
    //
    //
    //     // responsive: false,xLabelsSkip:0,
    // };
    // LineChart = {
    //     labels: ["2017-08-25 09:09:54","2017-08-25 09:09:45","2017-08-25 09:09:34","2017-08-25 09:09:12","2017-08-25 09:08:59","2017-08-25 09:08:54","2017-08-25 09:08:47","2017-08-25 09:08:40","2017-08-25 09:08:12","2017-08-25 09:08:02","2017-08-25 09:07:12","2017-08-25 09:06:45","2017-08-25 09:06:41","2017-08-25 09:06:17","2017-08-25 09:06:08","2017-08-25 09:05:31","2017-08-25 09:05:26","2017-08-25 09:04:54","2017-08-25 09:04:53","2017-08-25 09:04:35","2017-08-25 09:04:21","2017-08-25 09:03:54","2017-08-25 09:03:31","2017-08-25 09:03:22","2017-08-25 09:03:07","2017-08-25 09:02:55","2017-08-25 09:02:40","2017-08-25 09:02:36","2017-08-25 09:02:24","2017-08-25 09:02:16"],
    //     datasets: [{
    //         fillColor : "rgba(220,220,220,0)",
    //         strokeColor: "red",
    //         data: [112.47659,112.55005,112.07603,112.79585,112.55392,112.90841,112.21007,112.6438,112.30481,112.22587,112.04661,112.24671,112.04809,112.88616,112.87937,112.36358,112.35456,112.80597,112.13589,112.17284,112.58801,112.77225,112.69865,112.92168,112.00668,112.78806,112.3812,112.53162,112.10079,112.04526]
    //     }]
    // };
    // new Chart(ctx).Line(LineChart, options);
$.ajax({
        url: "/Public/Tradeline/"+asdas+mark+".text",
        type:"POST",
        dataType: 'json',
        error: function(xhr){
            $.ajax({
                url:"/Home`Trade`echart",
                // url:"index.php?s=/Home/Test/cece",
                type:"POST",
                dataType:'json',
                data:{brief:asdas,mark:mark},
                success:function (data) {
                    sads=data;

                    ctx = document.getElementById(asdas).getContext("2d");
                    options = {
                        scaleOverride: false, //是否用硬编码重写y轴网格线
                        scaleSteps: 0, //y轴刻度的个数
                        scaleStepWidth: 0, //y轴每个刻度的宽度
                        scaleStartValue: 0, //y轴的起始值
                        pointDot: false, //是否显示点
                        pointDotRadius: 0, //点的半径
                        scaleShowGridLines: false,//是否网格线
                        pointDotStrokeWidth: 0, //点的线宽
                        datasetStrokeWidth: 1, //数据线的线宽
                        scaleLineWidth : 0.01,
                        bezierCurve : true,//变成折线
                        scaleFontSize : 0,
                        scaleFontColor : "white",
                        gridLineWidth: 0,
                        scaleShowLabels:true,
                        pointHitDetectionRadius:0,


                        // responsive: false,xLabelsSkip:0,
                    };
                    LineChart = {
                        labels: sads.day,
                        datasets: [{
                            fillColor : "rgba(220,220,220,0)",
                            strokeColor: "red",
                            data: sads.value
                        }]
                    };
                    new Chart(ctx).Line(LineChart, options);
                }
            });

        }, //如果你的url,txt有问题,将会提示
        success: function(data) {
            // alert(data);
            jef.push(data);
            if(jef[0]==undefined){
                $.ajax({
                    url:"/Home`Trade`echart",
                    type:"POST",
                    dataType:'json',
                    data:{brief:asdas,mark:mark},
                    success:function (data) {
                        sads=data;
                        ctx = document.getElementById(asdas).getContext("2d");
                        options = {
                            scaleOverride: false, //是否用硬编码重写y轴网格线
                            scaleSteps: 0, //y轴刻度的个数
                            scaleStepWidth: 0, //y轴每个刻度的宽度
                            scaleStartValue: 0, //y轴的起始值
                            pointDot: false, //是否显示点
                            pointDotRadius: 0, //点的半径
                            scaleShowGridLines: false,//是否网格线
                            pointDotStrokeWidth: 0, //点的线宽
                            datasetStrokeWidth: 1, //数据线的线宽
                            scaleLineWidth : 0.01,
                            bezierCurve : true,//变成折线
                            scaleFontSize : 0,
                            scaleFontColor : "white",
                            gridLineWidth: 0,
                            scaleShowLabels:true,
                            pointHitDetectionRadius:0,


                            // responsive: false,xLabelsSkip:0,
                        };
                        LineChart = {
                            labels: sads.day,
                            datasets: [{
                                fillColor : "rgba(220,220,220,0)",
                                strokeColor: "red",
                                data: sads.value
                            }]
                        };
                        new Chart(ctx).Line(LineChart, options);
                    }
                });

            }else if(jef[0].extime!=undefined && jef[0].extime > Math.round(new Date().getTime()/1000)){
                sads=jef[0]
            }else if(jef==0) {
                $.ajax({
                    url:"/Home`Trade`echart",
                    // url:"index.php?s=/Home/Test/cece",
                    type:"POST",
                    dataType:'json',
                    data:{brief:asdas,mark:mark},
                    success:function (data) {
                        sads=data;
                        ctx = document.getElementById(asdas).getContext("2d");
                        options = {
                            scaleOverride: false, //是否用硬编码重写y轴网格线
                            scaleSteps: 0, //y轴刻度的个数
                            scaleStepWidth: 0, //y轴每个刻度的宽度
                            scaleStartValue: 0, //y轴的起始值
                            pointDot: false, //是否显示点
                            pointDotRadius: 0, //点的半径
                            scaleShowGridLines: false,//是否网格线
                            pointDotStrokeWidth: 0, //点的线宽
                            datasetStrokeWidth: 1, //数据线的线宽
                            scaleLineWidth : 0.01,
                            bezierCurve : true,//变成折线
                            scaleFontSize : 0,
                            scaleFontColor : "white",
                            gridLineWidth: 0,
                            scaleShowLabels:true,
                            pointHitDetectionRadius:0,


                            // responsive: false,xLabelsSkip:0,
                        };
                        LineChart = {
                            labels: sads.day,
                            datasets: [{
                                fillColor : "rgba(220,220,220,0)",
                                strokeColor: "red",
                                data: sads.value
                            }]
                        };
                        new Chart(ctx).Line(LineChart, options);
                    }
                });

            }else if(jef[0].extime!=undefined && jef[0].extime < Math.round(new Date().getTime()/1000)) {
                $.ajax({
                    url:"/Home`Trade`echart",
                    // url:"index.php?s=/Home/Test/cece",
                    type:"POST",
                    dataType:'json',
                    data:{brief:asdas,mark:mark},
                    success:function (data) {
                        sads=data;
                        ctx = document.getElementById(asdas).getContext("2d");
                        options = {
                            scaleOverride: false, //是否用硬编码重写y轴网格线
                            scaleSteps: 0, //y轴刻度的个数
                            scaleStepWidth: 0, //y轴每个刻度的宽度
                            scaleStartValue: 0, //y轴的起始值
                            pointDot: false, //是否显示点
                            pointDotRadius: 0, //点的半径
                            scaleShowGridLines: false,//是否网格线
                            pointDotStrokeWidth: 0, //点的线宽
                            datasetStrokeWidth: 1, //数据线的线宽
                            scaleLineWidth : 0.01,
                            bezierCurve : true,//变成折线
                            scaleFontSize : 0,
                            scaleFontColor : "white",
                            gridLineWidth: 0,
                            scaleShowLabels:true,
                            pointHitDetectionRadius:0,
                        };
                        LineChart = {
                            labels: sads.day,
                            datasets: [{
                                fillColor : "rgba(220,220,220,0)",
                                strokeColor: "red",
                                data: sads.value
                            }]
                        };
                        new Chart(ctx).Line(LineChart, options);
                    }
                });

            }
            ctx = document.getElementById(asdas).getContext("2d");
            options = {
                scaleOverride: false, //是否用硬编码重写y轴网格线
                scaleSteps: 0, //y轴刻度的个数
                scaleStepWidth: 0, //y轴每个刻度的宽度
                scaleStartValue: 0, //y轴的起始值
                pointDot: false, //是否显示点
                pointDotRadius: 0, //点的半径
                scaleShowGridLines: false,//是否网格线
                pointDotStrokeWidth: 0, //点的线宽
                datasetStrokeWidth: 1, //数据线的线宽
                scaleLineWidth : 0.01,
                bezierCurve : true,//变成折线
                scaleFontSize : 0,
                scaleFontColor : "white",
                gridLineWidth: 0,
                scaleShowLabels:true,
                pointHitDetectionRadius:0,


                // responsive: false,xLabelsSkip:0,
            };
            LineChart = {
                labels: sads.day,
                datasets: [{
                    fillColor : "rgba(220,220,220,0)",
                    strokeColor: "red",
                    data: sads.value
                }]
            };
            new Chart(ctx).Line(LineChart, options);

        }
    });



}
function removeRepeat1(arr){
    var a1=((new Date).getTime())
    for(var i=0;i<arr.length;i++)
        for(var j=i+1;j<arr.length;j++)
            if(arr[i]===arr[j]){arr.splice(j,1);j--;}
    // console.info((new Date).getTime()-a1) //记录去除数组重复元素所花费时间
    return arr.sort(function(a,b){return a-b});
}
