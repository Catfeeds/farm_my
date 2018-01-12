/**
 * Created by 48930 on 2017/7/21.
 */
$(function () {
  var xnb=  $('.xnb').get(0);
    $(xnb).trigger("click")
});
/*kline xiangyignshi*/
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
/*kline*/
var xnb=0;
var ss=0;
var myChart;
var  dom = document.getElementById("kxiantu");;
var mark=$('input[name=markets]').val();
var sef= [];
var jef=[];
var ss=1;
function riks() {
    var xnb=$('.cur');
    rik(xnb)
}
function rik(asds) {

     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)

    xnb=$(asds).attr("value");
    $('.timing').find('button').removeClass('activebtn');
    $('.riline').addClass('activebtn');
    $.ajax({
        url:"/Home`Index`newdol",
        type:"POST",
        dataType:'json',
        data:{brief:xnb},
        success:function (data) {
              $('.price').html('￥'+data.price)
        }
    });
    $.ajax({
        url:"/Home`Index`increase",
        type:"POST",
        dataType:'json',
        data:{brief:xnb},
        success:function (data) {
            var sdsad="";
            if(data==false){
                sdsad+='0.00'
            }else {
                sdsad+=data.toFixed(2)
            }
            $('.rose').html(sdsad+'%');
        }
    });
    $.ajax({
        url: "./Public/XnbKline/"+xnb+mark+".text",
        dataType: 'json',
        error: function(xhr) {
            $.ajax({
                url:"/Home`Index`echart",
                type:"POST",
                dataType:'json',
                data:{brief:xnb},
                success:function (data) {
                    ss=data;
                    if(ss.day.length==0){
                        option = {
                            title: {
                                text: xnb,
                                left: 0
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: ['日线']
                            },
                            grid: {
                                left: '10%',
                                right: '10%',
                                bottom: '15%'
                            },
                            xAxis: {
                                type: 'category',
                                data: [0],
                                scale: true,
                                boundaryGap : true,
                                axisLine: {onZero: false},
                                splitLine: {show: false},
                                splitNumber: 5,
                                min: 'dataMin',
                                max: 'dataMax'
                            },
                            yAxis: {
                                max:0,
                                scale: true,
                                interval:0,
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
                                    y: '90%',
                                    start: 50,
                                    end: 100
                                }
                            ],
                            series: [
                                {
                                    name: '日线',
                                    type: 'candlestick',
                                    data: [0,0,0,0],
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
                                            }
                                        ]
                                    }
                                }

                            ]
                        };
                        myChart.clear();
                        return false
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
                        title: {
                            text: xnb,
                            left: 0
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            }
                        },
                        legend: {
                            data: ['日线']
                        },
                        grid: {
                            left: '10%',
                            right: '10%',
                            bottom: '15%'
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
                            max:ss.sssmax,
                            scale: true,
                            interval:ss.jack,
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
                                y: '90%',
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
                                        }
                                    ]
                                }
                            }

                        ]
                    };
                    myChart.setOption(option);
                }
            });
        }, //如果你的url,txt有问题,将会提示
        success: function(data) {
            jef=data;
            // console.log(jef.extime );
            // console.log(Math.round(new Date().getTime()/1000));
            // console.log(jef.extime > Math.round(new Date().getTime()/1000));

            if(jef==undefined){
                $.ajax({
                    url:"/Home`Index`echart",
                    type:"POST",
                    dataType:'json',
                    data:{brief:xnb},
                    success:function (data) {
                        ss=data;
                        if(ss.day.length==0){
                            option = {
                                title: {
                                    text: xnb,
                                    left: 0
                                },
                                tooltip: {
                                    trigger: 'axis',
                                    axisPointer: {
                                        type: 'cross'
                                    }
                                },
                                legend: {
                                    data: ['日线']
                                },
                                grid: {
                                    left: '10%',
                                    right: '10%',
                                    bottom: '15%'
                                },
                                xAxis: {
                                    type: 'category',
                                    data: [0],
                                    scale: true,
                                    boundaryGap : true,
                                    axisLine: {onZero: false},
                                    splitLine: {show: false},
                                    splitNumber: 5,
                                    min: 'dataMin',
                                    max: 'dataMax'
                                },
                                yAxis: {
                                    max:0,
                                    scale: true,
                                    interval:0,
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
                                        y: '90%',
                                        start: 50,
                                        end: 100
                                    }
                                ],
                                series: [
                                    {
                                        name: '日线',
                                        type: 'candlestick',
                                        data: [0,0,0,0],
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
                                                }
                                            ]
                                        }
                                    }

                                ]
                            };
                            myChart.clear();
                            return false
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
                            title: {
                                text: xnb,
                                left: 0
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: ['日线']
                            },
                            grid: {
                                left: '10%',
                                right: '10%',
                                bottom: '15%'
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
                                max:ss.sssmax,
                                scale: true,
                                interval:ss.jack,
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
                                    y: '90%',
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
                                            }
                                        ]
                                    }
                                }

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
                    data:{brief:xnb},
                    success:function (data) {
                        ss=data;
                        if(ss.day.length==0){
                            option = {
                                title: {
                                    text: xnb,
                                    left: 0
                                },
                                tooltip: {
                                    trigger: 'axis',
                                    axisPointer: {
                                        type: 'cross'
                                    }
                                },
                                legend: {
                                    data: ['日线']
                                },
                                grid: {
                                    left: '10%',
                                    right: '10%',
                                    bottom: '15%'
                                },
                                xAxis: {
                                    type: 'category',
                                    data: [0],
                                    scale: true,
                                    boundaryGap : true,
                                    axisLine: {onZero: false},
                                    splitLine: {show: false},
                                    splitNumber: 5,
                                    min: 'dataMin',
                                    max: 'dataMax'
                                },
                                yAxis: {
                                    max:0,
                                    scale: true,
                                    interval:0,
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
                                        y: '90%',
                                        start: 50,
                                        end: 100
                                    }
                                ],
                                series: [
                                    {
                                        name: '日线',
                                        type: 'candlestick',
                                        data: [0,0,0,0],
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
                                                }
                                            ]
                                        }
                                    }

                                ]
                            };
                            myChart.clear();
                            return false
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
                            title: {
                                text: xnb,
                                left: 0
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: ['日线']
                            },
                            grid: {
                                left: '10%',
                                right: '10%',
                                bottom: '15%'
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
                                max:ss.sssmax,
                                scale: true,
                                interval:ss.jack,
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
                                    y: '90%',
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
                                            }
                                        ]
                                    }
                                }

                            ]
                        };
                        myChart.setOption(option);
                    }
                });
            }
            if(ss.day.length==0){
                option = {
                    title: {
                        text: xnb,
                        left: 0
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'cross'
                        }
                    },
                    legend: {
                        data: ['日线']
                    },
                    grid: {
                        left: '10%',
                        right: '10%',
                        bottom: '15%'
                    },
                    xAxis: {
                        type: 'category',
                        data: [0],
                        scale: true,
                        boundaryGap : true,
                        axisLine: {onZero: false},
                        splitLine: {show: false},
                        splitNumber: 5,
                        min: 'dataMin',
                        max: 'dataMax'
                    },
                    yAxis: {
                        max:0,
                        scale: true,
                        interval:0,
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
                            y: '90%',
                            start: 50,
                            end: 100
                        }
                    ],
                    series: [
                        {
                            name: '日线',
                            type: 'candlestick',
                            data: [0,0,0,0],
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
                                    }
                                ]
                            }
                        }

                    ]
                };
                myChart.clear();
                return false
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
                title: {
                    text: xnb,
                    left: 0
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                legend: {
                    data: ['日线']
                },
                grid: {
                    left: '10%',
                    right: '10%',
                    bottom: '15%'
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
                    max:ss.sssmax,
                    scale: true,
                    interval:ss.jack,
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
                        y: '90%',
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
                                }
                            ]
                        }
                    }

                ]
            };

            myChart.setOption(option,true);
        }
    });
}
function fivemin(dass) {
     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    var xian=$(dass).val();
    var title=$(dass).text();
    $.ajax({
        url:"/Home`Index`echartfmin",
        type:"POST",
        dataType:'json',
        data:{xian:xian,brief:xnb},
        success:function (data) {
            ss=data;
            if(ss.sssmax==0){
                ss.sssmax=null
            }
            if(ss.jack==0){
                ss.jack=null
            }
            option = {
                title: {
                    text: xnb,
                    left: 0
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                legend: {
                    data: [title]
                },
                grid: {
                    left: '10%',
                    right: '10%',
                    bottom: '15%'
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
                        y: '90%',
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
                                        symbolSize: 100,
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
function thertyemin(dass) {
     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    var xian=$(dass).val();
    var title=$(dass).text();
    $.ajax({
        url:"/Home`Index`echarttmin",
        type:"POST",
        dataType:'json',
        data:{xian:xian,brief:xnb},
        success:function (data) {
            ss=data;
            if(ss.sssmax==0){
                ss.sssmax=null
            }
            if(ss.jack==0){
                ss.jack=null
            }
            option = {
                title: {
                    text: xnb,
                    left: 0
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                legend: {
                    data: [title]
                },
                grid: {
                    left: '10%',
                    right: '10%',
                    bottom: '15%'
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
                        y: '90%',
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
                data:{xian:xian,brief:xnb},
                success:function (data) {
                    ss=data;
                    if(ss.sssmax==0){
                        ss.sssmax=null
                    }
                    if(ss.jack==0){
                        ss.jack=null
                    }
                    option = {
                        title: {
                            text: xnb,
                            left: 0
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            }
                        },
                        legend: {
                            data: [title]
                        },
                        grid: {
                            left: '10%',
                            right: '10%',
                            bottom: '15%'
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
                            max:ss.sssmax,
                            scale: true,
                            interval:ss.jack,
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
                                y: '90%',
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
                                        }
                                    ]
                                }
                            }

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
                    data:{xian:xian,brief:xnb},
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {
                            title: {
                                text: xnb,
                                left: 0
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: [title]
                            },
                            grid: {
                                left: '10%',
                                right: '10%',
                                bottom: '15%'
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
                                max:ss.sssmax,
                                scale: true,
                                interval:ss.jack,
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
                                    y: '90%',
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
                                            }
                                        ]
                                    }
                                }

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
                    data:{xian:xian,brief:xnb},
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {
                            title: {
                                text: xnb,
                                left: 0
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: [title]
                            },
                            grid: {
                                left: '10%',
                                right: '10%',
                                bottom: '15%'
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
                                max:ss.sssmax,
                                scale: true,
                                interval:ss.jack,
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
                                    y: '90%',
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
                                            }
                                        ]
                                    }
                                }

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
                title: {
                    text: xnb,
                    left: 0
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                legend: {
                    data: [title]
                },
                grid: {
                    left: '10%',
                    right: '10%',
                    bottom: '15%'
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
                    max:ss.sssmax,
                    scale: true,
                    interval:ss.jack,
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
                        y: '90%',
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
                                }
                            ]
                        }
                    }

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
function eighero(dass) {
     myChart = echarts.init(dom);
    var app = {};
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
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
                data:{xian:xian,brief:xnb},
                success:function (data) {
                    ss=data;
                    if(ss.sssmax==0){
                        ss.sssmax=null
                    }
                    if(ss.jack==0){
                        ss.jack=null
                    }
                    option = {
                        title: {
                            text: xnb,
                            left: 0
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            }
                        },
                        legend: {
                            data: [title]
                        },
                        grid: {
                            left: '10%',
                            right: '10%',
                            bottom: '15%'
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
                            max:ss.sssmax,
                            scale: true,
                            interval:ss.jack,
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
                                y: '90%',
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
                                        }
                                    ]
                                }
                            }

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
                    data:{xian:xian,brief:xnb},
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {
                            title: {
                                text: xnb,
                                left: 0
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: [title]
                            },
                            grid: {
                                left: '10%',
                                right: '10%',
                                bottom: '15%'
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
                                max:ss.sssmax,
                                scale: true,
                                interval:ss.jack,
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
                                    y: '90%',
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
                                            }
                                        ]
                                    }
                                }

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
                    data:{xian:xian,brief:xnb},
                    success:function (data) {
                        ss=data;
                        if(ss.sssmax==0){
                            ss.sssmax=null
                        }
                        if(ss.jack==0){
                            ss.jack=null
                        }
                        option = {
                            title: {
                                text: xnb,
                                left: 0
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: [title]
                            },
                            grid: {
                                left: '10%',
                                right: '10%',
                                bottom: '15%'
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
                                max:ss.sssmax,
                                scale: true,
                                interval:ss.jack,
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
                                    y: '90%',
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
                                            }
                                        ]
                                    }
                                }

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
                title: {
                    text: xnb,
                    left: 0
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                legend: {
                    data: [title]
                },
                grid: {
                    left: '10%',
                    right: '10%',
                    bottom: '15%'
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
                    max:ss.sssmax,
                    scale: true,
                    interval:ss.jack,
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
                        y: '90%',
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
                                }
                            ]
                        }
                    }

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
function xround(x, num){
    Math.round(x * Math.pow(10, num)) / Math.pow(10, num) ;
}