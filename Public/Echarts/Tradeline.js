/**
 * Created by 48930 on 2017/7/31.
 */
$(function () {
    rik()
});
var ss=0;
var worldMapforeach = document.getElementById('kxiantu');

//用于使chart自适应高度和宽度,通过窗体高宽计算容器高宽
var resizeWorldMapforeach = function () {
    worldMapforeach.style.width = worldMapforeach.innerWidth+'px';
    worldMapforeach.style.height = worldMapforeach.innerHeight+'px';
};
//设置容器高宽
resizeWorldMapforeach();
// 基于准备好的dom，初始化echarts实例
var myChart1 = echarts.init(worldMapforeach);
/*    // 使用刚指定的配置项和数据显示图表。
 myChart.setOption(option);*/

//用于使chart自适应高度和宽度
window.onresize = function () {
    //重置容器高宽
    resizeWorldMapforeach();
    myChart1.resize();
};
/*Kline*/
var xnb=0;
var mark=0;
var myChart;
var dom = document.getElementById("kxiantu");
var ss=1;
var sef= [];
var jef=[];
function rik(asds) {

     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)


    xnb=$('.xnb_name').attr("value");
    mark=$('#trade_line_out').attr("value");
    var jef=[];
    $.ajax({
        url: "/Public/XnbKline/"+xnb+mark+".text",
        dataType: 'json',
        // async: false,
        error: function(xhr) {
            $.ajax({
                url:"/Home`Index`echart",
                type:"POST",
                dataType:'json',
                data:{brief:xnb,mark:mark},
                // async: false,
                success:function (data) {
                    ss=data;
                    if(ss.sssmax==0){
                        ss.sssmax=null
                    }
                    if(ss.jack==0){
                        ss.jack=null
                    }
                    $('.high').html(ss.maxnum);
                    $('.low').html(ss.minnum);
                    option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            }
                        },

                        grid: {
                            top:'10%',
                            left: '10%',
                            right: '10%',
                            bottom: '30%'
                        },
                        xAxis: {
                            type: 'category',
                            data: ss.day,
                            scale: true,
                            boundaryGap : true,
                            axisLine: {onZero: false},
                            splitLine: {show: false},
                            splitNumber: 5,
                            min: 'dataMin',
                            max: 'dataMax'
                        },
                        yAxis: {
                            interval:ss.jack,
                            max:ss.sssmax,
                            scale: true,
                            splitArea: {
                                show: true
                            }
                        },
                        dataZoom: [
                            {
                                type: 'inside',
                                start: 100,
                                end: 0
                            },
                            {
                                show: true,
                                type: 'slider',
                                y: '76%',
                                start: 50,
                                end: 100
                            }
                        ],
                        series: [
                            {
                                name: '日线',
                                type: 'candlestick',
                                data: ss.value,
                                markPoint: {
                                    label: {
                                        normal: {
                                            formatter: function (param) {
                                                return param != null ? Math.round(param.value) : '';
                                            }
                                        }
                                    },
                                    data: [
                                        {
                                            name: 'XX标点',
                                            coord: ['2013/5/31', 2300],
                                            value: 2300,
                                            itemStyle: {
                                                normal: {color: 'rgb(41,60,85)'}
                                            }
                                        },
                                        {
                                            name: 'highest value',
                                            type: 'max',
                                            valueDim: '最高'
                                        },
                                        {
                                            name: 'lowest value',
                                            type: 'min',
                                            valueDim: '最低'
                                        },
                                      
                                    ],
                                    tooltip: {
                                        formatter: function (param) {
                                            return param.name + '<br>' + (param.data.coord || '');
                                        }
                                    }
                                },
                                markLine: {
                                    symbol: ['none', 'none'],
                                    data: [
                                        [
                                            {
                                                name: 'from lowest to highest',
                                                type: 'min',
                                                valueDim: '最低',
                                                symbol: 'circle',
                                                symbolSize: 10,
                                                label: {
                                                    normal: {show: false},
                                                    emphasis: {show: false}
                                                }
                                            },
                                            {
                                                type: 'max',
                                                valueDim: '最高',
                                                symbol: 'circle',
                                                symbolSize: 10,
                                                label: {
                                                    normal: {show: false},
                                                    emphasis: {show: false}
                                                }
                                            }
                                        ],
                                        {
                                            name: 'min line on close',
                                            type: 'min',
                                            valueDim: '闭盘'
                                        },
                                        {
                                            name: 'max line on close',
                                            type: 'max',
                                            valueDim: '闭盘'
                                        }
                                    ]
                                }
                            }
                            ,


                        ]
                    };
                    myChart.setOption(option);
                }
            });

        }, //如果你的url,txt有问题,将会提示
        success: function(data) {
            // alert(data);
            jef=data;
            if(jef==undefined){
                $.ajax({
                    url:"/Home`Index`echart",
                    type:"POST",
                    dataType:'json',
                    data:{brief:xnb,mark:mark},
                    // async: false,
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        $('.high').html(ss.maxnum);
                        $('.low').html(ss.minnum);
                        option = {

                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },

                            grid: {
                                top:'10%',
                                left: '10%',
                                right: '10%',
                                bottom: '30%'
                            },
                            xAxis: {
                                type: 'category',
                                data: ss.day,
                                scale: true,
                                boundaryGap : true,
                                axisLine: {onZero: false},
                                splitLine: {show: false},
                                splitNumber: 5,
                                min: 'dataMin',
                                max: 'dataMax'
                            },
                            yAxis: {
                                interval:ss.jack,
                                max:ss.sssmax,
                                scale: true,
                                splitArea: {
                                    show: true
                                }
                            },
                            dataZoom: [
                                {
                                    type: 'inside',
                                    start: 100,
                                    end: 0
                                },
                                {
                                    show: true,
                                    type: 'slider',
                                    y: '76%',
                                    start: 50,
                                    end: 100
                                }
                            ],
                            series: [
                                {
                                    name: '日线',
                                    type: 'candlestick',
                                    data: ss.value,
                                    markPoint: {
                                        label: {
                                            normal: {
                                                formatter: function (param) {
                                                    return param != null ? Math.round(param.value) : '';
                                                }
                                            }
                                        },
                                        data: [
                                            {
                                                name: 'XX标点',
                                                coord: ['2013/5/31', 2300],
                                                value: 2300,
                                                itemStyle: {
                                                    normal: {color: 'rgb(41,60,85)'}
                                                }
                                            },
                                            {
                                                name: 'highest value',
                                                type: 'max',
                                                valueDim: '最高'
                                            },
                                            {
                                                name: 'lowest value',
                                                type: 'min',
                                                valueDim: '最低'
                                            },
                                          
                                        ],
                                        tooltip: {
                                            formatter: function (param) {
                                                return param.name + '<br>' + (param.data.coord || '');
                                            }
                                        }
                                    },
                                    markLine: {
                                        symbol: ['none', 'none'],
                                        data: [
                                            [
                                                {
                                                    name: 'from lowest to highest',
                                                    type: 'min',
                                                    valueDim: '最低',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                },
                                                {
                                                    type: 'max',
                                                    valueDim: '最高',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                }
                                            ],
                                            {
                                                name: 'min line on close',
                                                type: 'min',
                                                valueDim: '闭盘'
                                            },
                                            {
                                                name: 'max line on close',
                                                type: 'max',
                                                valueDim: '闭盘'
                                            }
                                        ]
                                    }
                                }
                                ,


                            ]
                        };
                        myChart.setOption(option);
                    }
                });
            }else if(jef.extime!=undefined && jef.extime > Math.round(new Date().getTime()/1000)){
                ss=jef
            }else if(jef.extime!=undefined && jef.extime < Math.round(new Date().getTime()/1000)){
                $.ajax({
                    url:"/Home`Index`echart",
                    type:"POST",
                    dataType:'json',
                    data:{brief:xnb,mark:mark},
                    // async: false,
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        $('.high').html(ss.maxnum);
                        $('.low').html(ss.minnum);
                        option = {
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },

                            grid: {
                                top:'10%',
                                left: '10%',
                                right: '10%',
                                bottom: '30%'
                            },
                            xAxis: {
                                type: 'category',
                                data: ss.day,
                                scale: true,
                                boundaryGap : true,
                                axisLine: {onZero: false},
                                splitLine: {show: false},
                                splitNumber: 5,
                                min: 'dataMin',
                                max: 'dataMax'
                            },
                            yAxis: {
                                interval:ss.jack,
                                max:ss.sssmax,
                                scale: true,
                                splitArea: {
                                    show: true
                                }
                            },
                            dataZoom: [
                                {
                                    type: 'inside',
                                    start: 100,
                                    end: 0
                                },
                                {
                                    show: true,
                                    type: 'slider',
                                    y: '76%',
                                    start: 50,
                                    end: 100
                                }
                            ],
                            series: [
                                {
                                    name: '日线',
                                    type: 'candlestick',
                                    data: ss.value,
                                    markPoint: {
                                        label: {
                                            normal: {
                                                formatter: function (param) {
                                                    return param != null ? Math.round(param.value) : '';
                                                }
                                            }
                                        },
                                        data: [
                                            {
                                                name: 'XX标点',
                                                coord: ['2013/5/31', 2300],
                                                value: 2300,
                                                itemStyle: {
                                                    normal: {color: 'rgb(41,60,85)'}
                                                }
                                            },
                                            {
                                                name: 'highest value',
                                                type: 'max',
                                                valueDim: '最高'
                                            },
                                            {
                                                name: 'lowest value',
                                                type: 'min',
                                                valueDim: '最低'
                                            },

                                        ],
                                        tooltip: {
                                            formatter: function (param) {
                                                return param.name + '<br>' + (param.data.coord || '');
                                            }
                                        }
                                    },
                                    markLine: {
                                        symbol: ['none', 'none'],
                                        data: [
                                            [
                                                {
                                                    name: 'from lowest to highest',
                                                    type: 'min',
                                                    valueDim: '最低',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                },
                                                {
                                                    type: 'max',
                                                    valueDim: '最高',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                }
                                            ],
                                            {
                                                name: 'min line on close',
                                                type: 'min',
                                                valueDim: '闭盘'
                                            },
                                            {
                                                name: 'max line on close',
                                                type: 'max',
                                                valueDim: '闭盘'
                                            }
                                        ]
                                    }
                                }
                                ,


                            ]
                        };
                        myChart.setOption(option);
                    }
                });
            }
            if(ss.sssmax==0){
                ss.sssmax=null
            }
            if(ss.jack==0){
                ss.jack=null
            }
            $('.high').html(ss.maxnum);
            $('.low').html(ss.minnum);
            option = {

                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },

                grid: {
                    top:'10%',
                    left: '10%',
                    right: '10%',
                    bottom: '30%'
                },
                xAxis: {
                    type: 'category',
                    data: ss.day,
                    scale: true,
                    boundaryGap : true,
                    axisLine: {onZero: false},
                    splitLine: {show: false},
                    splitNumber: 5,
                    min: 'dataMin',
                    max: 'dataMax'
                },
                yAxis: {
                    interval:ss.jack,
                    max:ss.sssmax,
                    scale: true,
                    splitArea: {
                        show: true
                    }
                },
                dataZoom: [
                    {
                        type: 'inside',
                        start: 100,
                        end: 0
                    },
                    {
                        show: true,
                        type: 'slider',
                        y: '76%',
                        start: 50,
                        end: 100
                    }
                ],
                series: [
                    {
                        name: '日线',
                        type: 'candlestick',
                        data: ss.value,
                        markPoint: {
                            label: {
                                normal: {
                                    formatter: function (param) {
                                        return param != null ? Math.round(param.value) : '';
                                    }
                                }
                            },
                            data: [
                                {
                                    name: 'XX标点',
                                    coord: ['2013/5/31', 2300],
                                    value: 2300,
                                    itemStyle: {
                                        normal: {color: 'rgb(41,60,85)'}
                                    }
                                },
                                {
                                    name: 'highest value',
                                    type: 'max',
                                    valueDim: '最高'
                                },
                                {
                                    name: 'lowest value',
                                    type: 'min',
                                    valueDim: '最低'
                                },
                               
                            ],
                            tooltip: {
                                formatter: function (param) {
                                    return param.name + '<br>' + (param.data.coord || '');
                                }
                            }
                        },
                        markLine: {
                            symbol: ['none', 'none'],
                            data: [
                                [
                                    {
                                        name: 'from lowest to highest',
                                        type: 'min',
                                        valueDim: '最低',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    },
                                    {
                                        type: 'max',
                                        valueDim: '最高',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    }
                                ],
                                {
                                    name: 'min line on close',
                                    type: 'min',
                                    valueDim: '闭盘'
                                },
                                {
                                    name: 'max line on close',
                                    type: 'max',
                                    valueDim: '闭盘'
                                }
                            ]
                        }
                    }
                    ,


                ]
            };
            myChart.setOption(option);
        }
    });



    //    if (option && typeof option === "object") {
    //        var startTime = +new Date();
    //        myChart.setOption(option, true);
    //        var endTime = +new Date();
    //        var updateTime = endTime - startTime;
    //        console.log("Time used:", updateTime);
    //    }
}
function fivemin(dass) {

     myChart = echarts.init(dom);

    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    mark=$('#trade_line_out').attr("value");
    var xian=$(dass).val();
    var title=$(dass).text();
    $.ajax({
        url:"/Home`Index`echartfmin",
        type:"POST",
        dataType:'json',
        data:{xian:xian,brief:xnb,mark:mark},
        success:function (data) {
            ss=data;
            if(ss.sssmax==0){
                ss.sssmax=null
            }
            if(ss.jack==0){
                ss.jack=null
            }
            option = {

                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },

                grid: {
                    top:'10%',
                    left: '10%',
                    right: '10%',
                    bottom: '30%'
                },
                xAxis: {
                    type: 'category',
                    data: ss.day,
                    scale: true,
                    boundaryGap : true,
                    axisLine: {onZero: false},
                    splitLine: {show: false},
                    splitNumber: 5,
                    min: 'dataMin',
                    max: 'dataMax'
                },
                yAxis: {
                    interval:ss.jack,
                    max:ss.sssmax,
                    scale: true,
                    splitArea: {
                        show: true
                    }
                },
                dataZoom: [
                    {
                        type: 'inside',
                        start: 100,
                        end: 0
                    },
                    {
                        show: true,
                        type: 'slider',
                        y: '76%',
                        start: 50,
                        end: 100
                    }
                ],
                series: [
                    {
                        name: title,
                        type: 'candlestick',
                        data: ss.value,
                        markPoint: {
                            label: {
                                normal: {
                                    formatter: function (param) {
                                        return param != null ? Math.round(param.value) : '';
                                    }
                                }
                            },
                            data: [
                                {
                                    name: 'XX标点',
                                    coord: ['2013/5/31', 2300],
                                    value: 2300,
                                    itemStyle: {
                                        normal: {color: 'rgb(41,60,85)'}
                                    }
                                },
                                {
                                    name: 'highest value',
                                    type: 'max',
                                    valueDim: '最高'
                                },
                                {
                                    name: 'lowest value',
                                    type: 'min',
                                    valueDim: '最低'
                                },
                              
                            ],
                            tooltip: {
                                formatter: function (param) {
                                    return param.name + '<br>' + (param.data.coord || '');
                                }
                            }
                        },
                        markLine: {
                            symbol: ['none', 'none'],
                            data: [
                                [
                                    {
                                        name: 'from lowest to highest',
                                        type: 'min',
                                        valueDim: '最低',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    },
                                    {
                                        type: 'max',
                                        valueDim: '最高',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    }
                                ],
                                {
                                    name: 'min line on close',
                                    type: 'min',
                                    valueDim: '闭盘'
                                },
                                {
                                    name: 'max line on close',
                                    type: 'max',
                                    valueDim: '闭盘'
                                }
                            ]
                        }
                    }
                    ,


                ]
            };
            myChart.setOption(option);
        }
    })
    //    if (option && typeof option === "object") {
    //        var startTime = +new Date();
    //        myChart.setOption(option, true);
    //        var endTime = +new Date();
    //        var updateTime = endTime - startTime;
    //        console.log("Time used:", updateTime);
    //    }

}
function thertyemin(dass) {
     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    mark=$('#trade_line_out').attr("value");
    var xian=$(dass).val();
    var title=$(dass).text();
    $.ajax({
        url:"/Home`Index`echarttmin",
        type:"POST",
        dataType:'json',
        data:{xian:xian,brief:xnb,mark:mark},
        success:function (data) {
            ss=data;
            if(ss.sssmax==0){
                ss.sssmax=null
            }
            if(ss.jack==0){
                ss.jack=null
            }
            option = {

                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                grid: {
                    top:'10%',
                    left: '10%',
                    right: '10%',
                    bottom: '30%'
                },
                xAxis: {
                    type: 'category',
                    data: ss.day,
                    scale: true,
                    boundaryGap : true,
                    axisLine: {onZero: false},
                    splitLine: {show: false},
                    splitNumber: 5,
                    min: 'dataMin',
                    max: 'dataMax'
                },
                yAxis: {
                    interval:ss.jack,
                    max:ss.sssmax,
                    scale: true,
                    splitArea: {
                        show: true
                    }
                },
                dataZoom: [
                    {
                        type: 'inside',
                        start: 100,
                        end: 0
                    },
                    {
                        show: true,
                        type: 'slider',
                        y: '76%',
                        start: 50,
                        end: 100
                    }
                ],
                series: [
                    {
                        name: title,
                        type: 'candlestick',
                        data: ss.value,
                        markPoint: {
                            label: {
                                normal: {
                                    formatter: function (param) {
                                        return param != null ? Math.round(param.value) : '';
                                    }
                                }
                            },
                            data: [
                                {
                                    name: 'XX标点',
                                    coord: ['2013/5/31', 2300],
                                    value: 2300,
                                    itemStyle: {
                                        normal: {color: 'rgb(41,60,85)'}
                                    }
                                },
                                {
                                    name: 'highest value',
                                    type: 'max',
                                    valueDim: '最高'
                                },
                                {
                                    name: 'lowest value',
                                    type: 'min',
                                    valueDim: '最低'
                                },
                               
                            ],
                            tooltip: {
                                formatter: function (param) {
                                    return param.name + '<br>' + (param.data.coord || '');
                                }
                            }
                        },
                        markLine: {
                            symbol: ['none', 'none'],
                            data: [
                                [
                                    {
                                        name: 'from lowest to highest',
                                        type: 'min',
                                        valueDim: '最低',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    },
                                    {
                                        type: 'max',
                                        valueDim: '最高',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    }
                                ],
                                {
                                    name: 'min line on close',
                                    type: 'min',
                                    valueDim: '闭盘'
                                },
                                {
                                    name: 'max line on close',
                                    type: 'max',
                                    valueDim: '闭盘'
                                }
                            ]
                        }
                    }
                    ,


                ]
            };
            myChart.setOption(option);
        }
    })
    //    if (option && typeof option === "object") {
    //        var startTime = +new Date();
    //        myChart.setOption(option, true);
    //        var endTime = +new Date();
    //        var updateTime = endTime - startTime;
    //        console.log("Time used:", updateTime);
    //    }

}
function onehero(dass) {
     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    mark=$('#trade_line_out').attr("value");
    var xian=$(dass).val();
    var title=$(dass).text();
    $.ajax({
        url: "/Public/XnbOne/"+xnb+mark+".text",
        dataType: 'json',
        error: function(xhr) {
            $.ajax({
                url:"/Home`Index`onehero",
                type:"POST",
                dataType:'json',
                data:{xian:xian,brief:xnb,mark:mark},
                success:function (data) {
                    ss=data;
                    if(ss.sssmax==0){
                        ss.sssmax=null
                    }
                    if(ss.jack==0){
                        ss.jack=null
                    }
                    option = {

                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            }
                        },

                        grid: {
                            top:'10%',
                            left: '10%',
                            right: '10%',
                            bottom: '30%'
                        },
                        xAxis: {
                            type: 'category',
                            data: ss.day,
                            scale: true,
                            boundaryGap : true,
                            axisLine: {onZero: false},
                            splitLine: {show: false},
                            splitNumber: 5,
                            min: 'dataMin',
                            max: 'dataMax'
                        },
                        yAxis: {
                            interval:ss.jack,
                            max:ss.sssmax,
                            scale: true,
                            splitArea: {
                                show: true
                            }
                        },
                        dataZoom: [
                            {
                                type: 'inside',
                                start: 100,
                                end: 0
                            },
                            {
                                show: true,
                                type: 'slider',
                                y: '76%',
                                start: 50,
                                end: 100
                            }
                        ],
                        series: [
                            {
                                name: title,
                                type: 'candlestick',
                                data: ss.value,
                                markPoint: {
                                    label: {
                                        normal: {
                                            formatter: function (param) {
                                                return param != null ? Math.round(param.value) : '';
                                            }
                                        }
                                    },
                                    data: [
                                        {
                                            name: 'XX标点',
                                            coord: ['2013/5/31', 2300],
                                            value: 2300,
                                            itemStyle: {
                                                normal: {color: 'rgb(41,60,85)'}
                                            }
                                        },
                                        {
                                            name: 'highest value',
                                            type: 'max',
                                            valueDim: '最高'
                                        },
                                        {
                                            name: 'lowest value',
                                            type: 'min',
                                            valueDim: '最低'
                                        },
                                       
                                    ],
                                    tooltip: {
                                        formatter: function (param) {
                                            return param.name + '<br>' + (param.data.coord || '');
                                        }
                                    }
                                },
                                markLine: {
                                    symbol: ['none', 'none'],
                                    data: [
                                        [
                                            {
                                                name: 'from lowest to highest',
                                                type: 'min',
                                                valueDim: '最低',
                                                symbol: 'circle',
                                                symbolSize: 10,
                                                label: {
                                                    normal: {show: false},
                                                    emphasis: {show: false}
                                                }
                                            },
                                            {
                                                type: 'max',
                                                valueDim: '最高',
                                                symbol: 'circle',
                                                symbolSize: 10,
                                                label: {
                                                    normal: {show: false},
                                                    emphasis: {show: false}
                                                }
                                            }
                                        ],
                                        {
                                            name: 'min line on close',
                                            type: 'min',
                                            valueDim: '闭盘'
                                        },
                                        {
                                            name: 'max line on close',
                                            type: 'max',
                                            valueDim: '闭盘'
                                        }
                                    ]
                                }
                            }
                            ,


                        ]
                    };
                    myChart.setOption(option);
                }
            })

        }, //如果你的url,txt有问题,将会提示
        success: function(data) {
            // alert(data);
            jef=data;
            if(jef==undefined){
                $.ajax({
                    url:"/Home`Index`onehero",
                    type:"POST",
                    dataType:'json',
                    data:{xian:xian,brief:xnb,mark:mark},
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {

                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },

                            grid: {
                                top:'10%',
                                left: '10%',
                                right: '10%',
                                bottom: '30%'
                            },
                            xAxis: {
                                type: 'category',
                                data: ss.day,
                                scale: true,
                                boundaryGap : true,
                                axisLine: {onZero: false},
                                splitLine: {show: false},
                                splitNumber: 5,
                                min: 'dataMin',
                                max: 'dataMax'
                            },
                            yAxis: {
                                interval:ss.jack,
                                max:ss.sssmax,
                                scale: true,
                                splitArea: {
                                    show: true
                                }
                            },
                            dataZoom: [
                                {
                                    type: 'inside',
                                    start: 100,
                                    end: 0
                                },
                                {
                                    show: true,
                                    type: 'slider',
                                    y: '76%',
                                    start: 50,
                                    end: 100
                                }
                            ],
                            series: [
                                {
                                    name: title,
                                    type: 'candlestick',
                                    data: ss.value,
                                    markPoint: {
                                        label: {
                                            normal: {
                                                formatter: function (param) {
                                                    return param != null ? Math.round(param.value) : '';
                                                }
                                            }
                                        },
                                        data: [
                                            {
                                                name: 'XX标点',
                                                coord: ['2013/5/31', 2300],
                                                value: 2300,
                                                itemStyle: {
                                                    normal: {color: 'rgb(41,60,85)'}
                                                }
                                            },
                                            {
                                                name: 'highest value',
                                                type: 'max',
                                                valueDim: '最高'
                                            },
                                            {
                                                name: 'lowest value',
                                                type: 'min',
                                                valueDim: '最低'
                                            },
                                           
                                        ],
                                        tooltip: {
                                            formatter: function (param) {
                                                return param.name + '<br>' + (param.data.coord || '');
                                            }
                                        }
                                    },
                                    markLine: {
                                        symbol: ['none', 'none'],
                                        data: [
                                            [
                                                {
                                                    name: 'from lowest to highest',
                                                    type: 'min',
                                                    valueDim: '最低',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                },
                                                {
                                                    type: 'max',
                                                    valueDim: '最高',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                }
                                            ],
                                            {
                                                name: 'min line on close',
                                                type: 'min',
                                                valueDim: '闭盘'
                                            },
                                            {
                                                name: 'max line on close',
                                                type: 'max',
                                                valueDim: '闭盘'
                                            }
                                        ]
                                    }
                                }
                                ,


                            ]
                        };
                        myChart.setOption(option);
                    }
                })
            }else if(jef.extime!=undefined && jef.extime > Math.round(new Date().getTime()/1000)){
                ss=jef
            }else if(jef.extime!=undefined && jef.extime < Math.round(new Date().getTime()/1000)){
                $.ajax({
                    url:"/Home`Index`onehero",
                    type:"POST",
                    dataType:'json',
                    data:{xian:xian,brief:xnb,mark:mark},
                    success:function (data) {
                        ss=data;
                        myChart.clear();
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {

                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },

                            grid: {
                                top:'10%',
                                left: '10%',
                                right: '10%',
                                bottom: '30%'
                            },
                            xAxis: {
                                type: 'category',
                                data: ss.day,
                                scale: true,
                                boundaryGap : true,
                                axisLine: {onZero: false},
                                splitLine: {show: false},
                                splitNumber: 5,
                                min: 'dataMin',
                                max: 'dataMax'
                            },
                            yAxis: {
                                interval:ss.jack,
                                max:ss.sssmax,
                                scale: true,
                                splitArea: {
                                    show: true
                                }
                            },
                            dataZoom: [
                                {
                                    type: 'inside',
                                    start: 100,
                                    end: 0
                                },
                                {
                                    show: true,
                                    type: 'slider',
                                    y: '76%',
                                    start: 50,
                                    end: 100
                                }
                            ],
                            series: [
                                {
                                    name: title,
                                    type: 'candlestick',
                                    data: ss.value,
                                    markPoint: {
                                        label: {
                                            normal: {
                                                formatter: function (param) {
                                                    return param != null ? Math.round(param.value) : '';
                                                }
                                            }
                                        },
                                        data: [
                                            {
                                                name: 'XX标点',
                                                coord: ['2013/5/31', 2300],
                                                value: 2300,
                                                itemStyle: {
                                                    normal: {color: 'rgb(41,60,85)'}
                                                }
                                            },
                                            {
                                                name: 'highest value',
                                                type: 'max',
                                                valueDim: '最高'
                                            },
                                            {
                                                name: 'lowest value',
                                                type: 'min',
                                                valueDim: '最低'
                                            },

                                        ],
                                        tooltip: {
                                            formatter: function (param) {
                                                return param.name + '<br>' + (param.data.coord || '');
                                            }
                                        }
                                    },
                                    markLine: {
                                        symbol: ['none', 'none'],
                                        data: [
                                            [
                                                {
                                                    name: 'from lowest to highest',
                                                    type: 'min',
                                                    valueDim: '最低',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                },
                                                {
                                                    type: 'max',
                                                    valueDim: '最高',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                }
                                            ],
                                            {
                                                name: 'min line on close',
                                                type: 'min',
                                                valueDim: '闭盘'
                                            },
                                            {
                                                name: 'max line on close',
                                                type: 'max',
                                                valueDim: '闭盘'
                                            }
                                        ]
                                    }
                                }
                                ,


                            ]
                        };
                        myChart.setOption(option);
                    }
                })
            }
            if(ss.sssmax==0){
                ss.sssmax=null
            }
            if(ss.sssmin==0){
                ss.sssmin=null
            }
            if(ss.jack==0){
                ss.jack=null
            }
            option = {

                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },

                grid: {
                    top:'10%',
                    left: '10%',
                    right: '10%',
                    bottom: '30%'
                },
                xAxis: {
                    type: 'category',
                    data: ss.day,
                    scale: true,
                    boundaryGap : true,
                    axisLine: {onZero: false},
                    splitLine: {show: false},
                    splitNumber: 5,
                    min: 'dataMin',
                    max: 'dataMax'
                },
                yAxis: {
                    interval:ss.jack,
                    max:ss.sssmax,
                    min:ss.sssmin,
                    scale: true,
                    splitArea: {
                        show: true
                    }
                },
                dataZoom: [
                    {
                        type: 'inside',
                        start: 100,
                        end: 0
                    },
                    {
                        show: true,
                        type: 'slider',
                        y: '76%',
                        start: 50,
                        end: 100
                    }
                ],
                series: [
                    {
                        name: title,
                        type: 'candlestick',
                        data: ss.value,
                        markPoint: {
                            label: {
                                normal: {
                                    formatter: function (param) {
                                        return param != null ? Math.round(param.value) : '';
                                    }
                                }
                            },
                            data: [
                                {
                                    name: 'XX标点',
                                    coord: ['2013/5/31', 2300],
                                    value: 2300,
                                    itemStyle: {
                                        normal: {color: 'rgb(41,60,85)'}
                                    }
                                },
                                {
                                    name: 'highest value',
                                    type: 'max',
                                    valueDim: '最高'
                                },
                                {
                                    name: 'lowest value',
                                    type: 'min',
                                    valueDim: '最低'
                                },

                            ],
                            tooltip: {
                                formatter: function (param) {
                                    return param.name + '<br>' + (param.data.coord || '');
                                }
                            }
                        },
                        markLine: {
                            symbol: ['none', 'none'],
                            data: [
                                [
                                    {
                                        name: 'from lowest to highest',
                                        type: 'min',
                                        valueDim: '最低',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    },
                                    {
                                        type: 'max',
                                        valueDim: '最高',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    }
                                ],
                                {
                                    name: 'min line on close',
                                    type: 'min',
                                    valueDim: '闭盘'
                                },
                                {
                                    name: 'max line on close',
                                    type: 'max',
                                    valueDim: '闭盘'
                                }
                            ]
                        }
                    }
                    ,


                ]
            };

            myChart.setOption(option);
        }
    });


}
function eighero(dass) {
     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    mark=$('#trade_line_out').attr("value");
    var xian=$(dass).val();
    var title=$(dass).text();
    $.ajax({
        url: "/Public/XnbEight/"+xnb+mark+".text",
        dataType: 'json',
        error: function(xhr) {
            $.ajax({
                url:"/Home`Index`eighero",
                type:"POST",
                dataType:'json',
                data:{xian:xian,brief:xnb,mark:mark},
                success:function (data) {
                    ss=data;
                    if(ss.sssmax==0){
                        ss.sssmax=null
                    }
                    if(ss.jack==0){
                        ss.jack=null
                    }
                    option = {

                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            }
                        },
                        grid: {
                            top:'10%',
                            left: '10%',
                            right: '10%',
                            bottom: '30%'
                        },
                        xAxis: {
                            type: 'category',
                            data: ss.day,
                            scale: true,
                            boundaryGap : true,
                            axisLine: {onZero: false},
                            splitLine: {show: false},
                            splitNumber: 5,
                            min: 'dataMin',
                            max: 'dataMax'
                        },
                        yAxis: {
                            interval:ss.jack,
                            max:ss.sssmax,
                            scale: true,
                            splitArea: {
                                show: true
                            }
                        },
                        dataZoom: [
                            {
                                type: 'inside',
                                start: 100,
                                end: 0
                            },
                            {
                                show: true,
                                type: 'slider',
                                y: '76%',
                                start: 50,
                                end: 100
                            }
                        ],
                        series: [
                            {
                                name: title,
                                type: 'candlestick',
                                data: ss.value,
                                markPoint: {
                                    label: {
                                        normal: {
                                            formatter: function (param) {
                                                return param != null ? Math.round(param.value) : '';
                                            }
                                        }
                                    },
                                    data: [
                                        {
                                            name: 'XX标点',
                                            coord: ['2013/5/31', 2300],
                                            value: 2300,
                                            itemStyle: {
                                                normal: {color: 'rgb(41,60,85)'}
                                            }
                                        },
                                        {
                                            name: 'highest value',
                                            type: 'max',
                                            valueDim: '最高'
                                        },
                                        {
                                            name: 'lowest value',
                                            type: 'min',
                                            valueDim: '最低'
                                        },
                                        {
                                            name: 'average value on close',
                                            type: 'average',
                                            valueDim: '闭盘'
                                        }
                                    ],
                                    tooltip: {
                                        formatter: function (param) {
                                            return param.name + '<br>' + (param.data.coord || '');
                                        }
                                    }
                                },
                                markLine: {
                                    symbol: ['none', 'none'],
                                    data: [
                                        [
                                            {
                                                name: 'from lowest to highest',
                                                type: 'min',
                                                valueDim: '最低',
                                                symbol: 'circle',
                                                symbolSize: 10,
                                                label: {
                                                    normal: {show: false},
                                                    emphasis: {show: false}
                                                }
                                            },
                                            {
                                                type: 'max',
                                                valueDim: '最高',
                                                symbol: 'circle',
                                                symbolSize: 10,
                                                label: {
                                                    normal: {show: false},
                                                    emphasis: {show: false}
                                                }
                                            }
                                        ],
                                        {
                                            name: 'min line on close',
                                            type: 'min',
                                            valueDim: '闭盘'
                                        },
                                        {
                                            name: 'max line on close',
                                            type: 'max',
                                            valueDim: '闭盘'
                                        }
                                    ]
                                }
                            }
                            ,


                        ]
                    };
                    myChart.setOption(option);
                }
            })

        }, //如果你的url,txt有问题,将会提示
        success: function(data) {
            // alert(data);
            jef=data;
            if(jef==undefined){
                $.ajax({
                    url:"/Home`Index`eighero",
                    type:"POST",
                    dataType:'json',
                    data:{xian:xian,brief:xnb,mark:mark},
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {

                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            grid: {
                                top:'10%',
                                left: '10%',
                                right: '10%',
                                bottom: '30%'
                            },
                            xAxis: {
                                type: 'category',
                                data: ss.day,
                                scale: true,
                                boundaryGap : true,
                                axisLine: {onZero: false},
                                splitLine: {show: false},
                                splitNumber: 5,
                                min: 'dataMin',
                                max: 'dataMax'
                            },
                            yAxis: {
                                interval:ss.jack,
                                max:ss.sssmax,
                                scale: true,
                                splitArea: {
                                    show: true
                                }
                            },
                            dataZoom: [
                                {
                                    type: 'inside',
                                    start: 100,
                                    end: 0
                                },
                                {
                                    show: true,
                                    type: 'slider',
                                    y: '76%',
                                    start: 50,
                                    end: 100
                                }
                            ],
                            series: [
                                {
                                    name: title,
                                    type: 'candlestick',
                                    data: ss.value,
                                    markPoint: {
                                        label: {
                                            normal: {
                                                formatter: function (param) {
                                                    return param != null ? Math.round(param.value) : '';
                                                }
                                            }
                                        },
                                        data: [
                                            {
                                                name: 'XX标点',
                                                coord: ['2013/5/31', 2300],
                                                value: 2300,
                                                itemStyle: {
                                                    normal: {color: 'rgb(41,60,85)'}
                                                }
                                            },
                                            {
                                                name: 'highest value',
                                                type: 'max',
                                                valueDim: '最高'
                                            },
                                            {
                                                name: 'lowest value',
                                                type: 'min',
                                                valueDim: '最低'
                                            },
                                            {
                                                name: 'average value on close',
                                                type: 'average',
                                                valueDim: '闭盘'
                                            }
                                        ],
                                        tooltip: {
                                            formatter: function (param) {
                                                return param.name + '<br>' + (param.data.coord || '');
                                            }
                                        }
                                    },
                                    markLine: {
                                        symbol: ['none', 'none'],
                                        data: [
                                            [
                                                {
                                                    name: 'from lowest to highest',
                                                    type: 'min',
                                                    valueDim: '最低',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                },
                                                {
                                                    type: 'max',
                                                    valueDim: '最高',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                }
                                            ],
                                            {
                                                name: 'min line on close',
                                                type: 'min',
                                                valueDim: '闭盘'
                                            },
                                            {
                                                name: 'max line on close',
                                                type: 'max',
                                                valueDim: '闭盘'
                                            }
                                        ]
                                    }
                                }
                                ,


                            ]
                        };
                        myChart.setOption(option);
                    }
                })
            }else if(jef.extime!=undefined && jef.extime > Math.round(new Date().getTime()/1000)){
                ss=jef
            }else if(jef.extime!=undefined && jef.extime < Math.round(new Date().getTime()/1000)){
                $.ajax({
                    url:"/Home`Index`eighero",
                    type:"POST",
                    dataType:'json',
                    data:{xian:xian,brief:xnb,mark:mark},
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {

                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            grid: {
                                top:'10%',
                                left: '10%',
                                right: '10%',
                                bottom: '30%'
                            },
                            xAxis: {
                                type: 'category',
                                data: ss.day,
                                scale: true,
                                boundaryGap : true,
                                axisLine: {onZero: false},
                                splitLine: {show: false},
                                splitNumber: 5,
                                min: 'dataMin',
                                max: 'dataMax'
                            },
                            yAxis: {
                                interval:ss.jack,
                                max:ss.sssmax,
                                scale: true,
                                splitArea: {
                                    show: true
                                }
                            },
                            dataZoom: [
                                {
                                    type: 'inside',
                                    start: 100,
                                    end: 0
                                },
                                {
                                    show: true,
                                    type: 'slider',
                                    y: '76%',
                                    start: 50,
                                    end: 100
                                }
                            ],
                            series: [
                                {
                                    name: title,
                                    type: 'candlestick',
                                    data: ss.value,
                                    markPoint: {
                                        label: {
                                            normal: {
                                                formatter: function (param) {
                                                    return param != null ? Math.round(param.value) : '';
                                                }
                                            }
                                        },
                                        data: [
                                            {
                                                name: 'XX标点',
                                                coord: ['2013/5/31', 2300],
                                                value: 2300,
                                                itemStyle: {
                                                    normal: {color: 'rgb(41,60,85)'}
                                                }
                                            },
                                            {
                                                name: 'highest value',
                                                type: 'max',
                                                valueDim: '最高'
                                            },
                                            {
                                                name: 'lowest value',
                                                type: 'min',
                                                valueDim: '最低'
                                            },
                                            {
                                                name: 'average value on close',
                                                type: 'average',
                                                valueDim: '闭盘'
                                            }
                                        ],
                                        tooltip: {
                                            formatter: function (param) {
                                                return param.name + '<br>' + (param.data.coord || '');
                                            }
                                        }
                                    },
                                    markLine: {
                                        symbol: ['none', 'none'],
                                        data: [
                                            [
                                                {
                                                    name: 'from lowest to highest',
                                                    type: 'min',
                                                    valueDim: '最低',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                },
                                                {
                                                    type: 'max',
                                                    valueDim: '最高',
                                                    symbol: 'circle',
                                                    symbolSize: 10,
                                                    label: {
                                                        normal: {show: false},
                                                        emphasis: {show: false}
                                                    }
                                                }
                                            ],
                                            {
                                                name: 'min line on close',
                                                type: 'min',
                                                valueDim: '闭盘'
                                            },
                                            {
                                                name: 'max line on close',
                                                type: 'max',
                                                valueDim: '闭盘'
                                            }
                                        ]
                                    }
                                }
                                ,


                            ]
                        };
                        myChart.setOption(option);
                    }
                })
            }
            if(ss.sssmax==0){
                ss.sssmax=null
            }
            if(ss.jack==0){
                ss.jack=null
            }
            option = {

                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },

                grid: {
                    top:'10%',
                    left: '10%',
                    right: '10%',
                    bottom: '30%'
                },
                xAxis: {
                    type: 'category',
                    data: ss.day,
                    scale: true,
                    boundaryGap : true,
                    axisLine: {onZero: false},
                    splitLine: {show: false},
                    splitNumber: 5,
                    min: 'dataMin',
                    max: 'dataMax'
                },
                yAxis: {
                    interval:ss.jack,
                    max:ss.sssmax,
                    scale: true,
                    splitArea: {
                        show: true
                    }
                },
                dataZoom: [
                    {
                        type: 'inside',
                        start: 100,
                        end: 0
                    },
                    {
                        show: true,
                        type: 'slider',
                        y: '76%',
                        start: 50,
                        end: 100
                    }
                ],
                series: [
                    {
                        name: title,
                        type: 'candlestick',
                        data: ss.value,
                        markPoint: {
                            label: {
                                normal: {
                                    formatter: function (param) {
                                        return param != null ? Math.round(param.value) : '';
                                    }
                                }
                            },
                            data: [
                                {
                                    name: 'XX标点',
                                    coord: ['2013/5/31', 2300],
                                    value: 2300,
                                    itemStyle: {
                                        normal: {color: 'rgb(41,60,85)'}
                                    }
                                },
                                {
                                    name: 'highest value',
                                    type: 'max',
                                    valueDim: '最高'
                                },
                                {
                                    name: 'lowest value',
                                    type: 'min',
                                    valueDim: '最低'
                                },

                            ],
                            tooltip: {
                                formatter: function (param) {
                                    return param.name + '<br>' + (param.data.coord || '');
                                }
                            }
                        },
                        markLine: {
                            symbol: ['none', 'none'],
                            data: [
                                [
                                    {
                                        name: 'from lowest to highest',
                                        type: 'min',
                                        valueDim: '最低',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    },
                                    {
                                        type: 'max',
                                        valueDim: '最高',
                                        symbol: 'circle',
                                        symbolSize: 10,
                                        label: {
                                            normal: {show: false},
                                            emphasis: {show: false}
                                        }
                                    }
                                ],
                                {
                                    name: 'min line on close',
                                    type: 'min',
                                    valueDim: '闭盘'
                                },
                                {
                                    name: 'max line on close',
                                    type: 'max',
                                    valueDim: '闭盘'
                                }
                            ]
                        }
                    }
                    ,


                ]
            };
            myChart.setOption(option);
        }
    });
    

}