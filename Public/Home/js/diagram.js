var nowdays = new Date();
	    var year = nowdays.getFullYear();
	    var month = nowdays.getMonth();
	    var day = nowdays.getDate();

	    var myDate = new Date(year, month, 0);
	    var lastDay = myDate.getDate();//上个月的最后一天
		var arr=[];
		
		
		for (var i = 0; i <=30; i++) {
			if(day-i<=0){
				if(month==0){			    
			        month=12;
			        year=year-1;
			    }else{
			    	year = nowdays.getFullYear();
			    }
				str=year+'/'+month+'/'+(lastDay+day-i)
			}else{
				str=year+'/'+(month+1)+'/'+(day-i)
			}
			arr.push([str,parseInt( Math.random()*100+50)])
		};
var ss=[];
var ssa=[];
var sef= [];
var sef2= [];
var sef3= [];
var dayx=[];
var value=[];
var dayx2=[];
var value2=[];
var dayx3=[];
var value3=[];
var value4=[];
$(function () {
	$.ajax({
		url:"/Admin`Interlinkage`echarts",
		type:"POST",
		dataType:'json',
		async: false,
		success:function (data) {
			ss.push(data);
		}
	});
	for(var ssd in ss[0]){
		sef.push(ssd);
	}

	for(var j=0;j<sef.length;j++){
		//day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
		dayx.push(sef[j]);
		value.push(ss[0][sef[j]])
	}
	var sas=[];
	for(var i=0;i<value.length;i++){
		var sanu=value[i]
		sas.push(sanu.length);
//                console.log(sanu.length);
	}
//             console.log(sas);
// console.log(sum);

	var  myChart1 = echarts.init(document.getElementById('box1'));
	var option1={
		title: {
			text: '用户注册表(最近30天)',
		},
		tooltip: {
			trigger: 'axis'
		},
		xAxis:  {
			name:'日期',
			type: 'category',
			boundaryGap: false,

			data: dayx
		},
		yAxis: {
			type: 'value',
			axisLabel: {
				formatter: '{value} 人'
			}
		},
		series: {
			name:'人数',
			type:'line',
			data:sas
		},
	};


	myChart1.setOption(option1);

	$.ajax({
		url:"/Admin`Interlinkage`echart",
		type:"POST",
		dataType:'json',
		async: false,
		success:function (data) {
			ssa.push(data);
		}
	});

	for(var ssd2 in ssa[0]){
		sef2.push(ssd2);
	}
	for(var s=0;s<sef2.length;s++){
		//day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
		dayx2.push(sef2[s]);
		value2.push(ssa[0][sef2[s]])
		for(var ssd3 in ssa[0][sef2[s]]){
			sef3.push(ssd3);
		}
	}
	for(var o=0;o<sef3.length;o++){
		//day  循环赋值以作为X坐标  value 循环赋值以作为Y坐标
		dayx3.push(sef3[o]);
	}

	dayx3=removeRepeat1(dayx3);
	dayx3=dayx3.sort();
	for(var q=0;q<dayx3.length;q++){
			if((ssa[0].chong[dayx3[q]]==undefined) ){
				ssa[0].chong[dayx3[q]]=0;


		}
			else if(ssa[0].ti[dayx3[q]]==undefined){
			ssa[0].ti[dayx3[q]]==0;
		}
		value3.push(ssa[0].chong[dayx3[q]]);
		value4.push(ssa[0].ti[dayx3[q]]);
	}
	var myChart2 = echarts.init(document.getElementById('box2'));
	// 指定图表的配置项和数据
	option2 = {
		title: {
			text: '系统 充值/提现 统计图(30天)',
			//subtext: '纯属虚构'
		},
		tooltip: {
			trigger: 'axis'
		},
		legend: {
			data:['充值','提现'],
			// right:'center',
			bottom:10,
		},

		xAxis:  {
			//name:'日期',
			name:'日期',
			type: 'category',
			boundaryGap: false,
			data: dayx3
			// axisLabel:{
			// 	interval:0,//横轴信息全部显示
			// 	//rotate: 60,//60度角倾斜显示
            //
			// },
		},
		yAxis: {
			// axisLine:{
			// 	show:false
			// },
			type: 'value',
			axisLabel: {
				formatter: '{value} 元'
			}
		},
		series: [
			{
				name:'充值',
				type:'line',
				data:value3,
				smooth: true

			},
			{
				name:'提现',
				type:'line',
				data:value4,
				smooth: true

			}
		]
	};
	myChart2.setOption(option2);
})
function removeRepeat1(arr){
	var a1=((new Date).getTime())
	for(var i=0;i<arr.length;i++)
		for(var j=i+1;j<arr.length;j++)
			if(arr[i]===arr[j]){arr.splice(j,1);j--;}
	// console.info((new Date).getTime()-a1) //记录去除数组重复元素所花费时间
	return arr.sort(function(a,b){return a-b});
}


myChart1.setOption(option1);
window.addEventListener("resize",function(){
      	 myChart1.resize();
 });
 
myChart2.setOption(option2);
window.addEventListener("resize",function(){
      	 myChart2.resize();
 });
        // 使用刚指定的配置项和数据显示图表。

