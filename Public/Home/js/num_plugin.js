
/**
 *  数字滚动插件 v1.0
 */
;(function($) {
    $.fn.numberAnimate = function(setting) {
        var defaults = {
            speed : 1000,//动画速度
            num : "", //初始化值
            iniAnimate : true, //是否要初始化动画效果
            symbol : '',//默认的分割符号，千，万，千万
            dot : 0 //保留几位小数点
        };
        //如果setting为空，就取default的值
        var setting = $.extend(defaults, setting);

        //如果对象有多个，提示出错
        if($(this).length > 1){
            alert("just only one obj!");
            return;
        }

        //如果未设置初始化值。提示出错
        if(setting.num == ""){
            alert("must set a num!");
            return;
        }
        var nHtml = '<div class="mt-number-animate-dom" data-num="{{num}}">\
            <span class="mt-number-animate-span">0</span>\
            <span class="mt-number-animate-span">1</span>\
            <span class="mt-number-animate-span">2</span>\
            <span class="mt-number-animate-span">3</span>\
            <span class="mt-number-animate-span">4</span>\
            <span class="mt-number-animate-span">5</span>\
            <span class="mt-number-animate-span">6</span>\
            <span class="mt-number-animate-span">7</span>\
            <span class="mt-number-animate-span">8</span>\
            <span class="mt-number-animate-span">9</span>\
            <span class="mt-number-animate-span">.</span>\
          </div>';

        //数字处理
        var numToArr = function(num){
            num = parseFloat(num).toFixed(setting.dot);
            if(typeof(num) == 'number'){
                var arrStr = num.toString().split("");
            }else{
                var arrStr = num.split("");
            }
            //console.log(arrStr);
            return arrStr;
        };

        //设置DOM symbol:分割符号
        var setNumDom = function(arrStr){
            var shtml = '<div class="mt-number-animate">';
            for(var i=0,len=arrStr.length; i<len; i++){
                if(i != 0 && (len-i)%3 == 0 && setting.symbol != "" && arrStr[i]!="."){
                    shtml += '<div class="mt-number-animate-dot">'+setting.symbol+'</div>'+nHtml.replace("{{num}}",arrStr[i]);
                }else{
                    shtml += nHtml.replace("{{num}}",arrStr[i]);
                }
            }
            shtml += '</div>';
            return shtml;
        };

        //执行动画
        var runAnimate = function($parent){
            $parent.find(".mt-number-animate-dom").each(function() {
                var num = $(this).attr("data-num");
                num = (num=="."?10:num);
                var spanHei = $(this).height()/11; //11为元素个数
                var thisTop = -num*spanHei+"px";
                if(thisTop != $(this).css("top")){
                    if(setting.iniAnimate){
                        //HTML5不支持
                        if(!window.applicationCache){
                            $(this).animate({
                                top : thisTop
                            }, setting.speed);
                        }else{
                            $(this).css({
                                'transform':'translateY('+thisTop+')',
                                '-ms-transform':'translateY('+thisTop+')',   /* IE 9 */
                                '-moz-transform':'translateY('+thisTop+')',  /* Firefox */
                                '-webkit-transform':'translateY('+thisTop+')', /* Safari 和 Chrome */
                                '-o-transform':'translateY('+thisTop+')',
                                '-ms-transition':setting.speed/1000+'s',
                                '-moz-transition':setting.speed/1000+'s',
                                '-webkit-transition':setting.speed/1000+'s',
                                '-o-transition':setting.speed/1000+'s',
                                'transition':setting.speed/1000+'s'
                            });
                        }
                    }else{
                        setting.iniAnimate = true;
                        $(this).css({
                            top : thisTop
                        });
                    }
                }
            });
        };

        //初始化
        var init = function($parent){
            //初始化
            $parent.html(setNumDom(numToArr(setting.num)));
            runAnimate($parent);
        };

        //重置参数
        this.resetData = function(num){
            var newArr = numToArr(num);
            var $dom = $(this).find(".mt-number-animate-dom");
            if($dom.length < newArr.length){
                $(this).html(setNumDom(numToArr(num)));
            }else{
                $dom.each(function(index, el) {
                    $(this).attr("data-num",newArr[index]);
                });
            }
            runAnimate($(this));
        };
        //init
        init($(this));
        return this;
    }
})(jQuery);

$(function(){
    var numRun4 = $(".numberRun4").numberAnimate({num:'52353488', speed:2000});
/*    var nums4 = 52353434;
    setInterval(function(){
        nums4+= 123454;
        numRun4.resetData(nums4);
    },3500);*/


    /*for apply hover*/
    $("#out_border").on({
        "mouseenter": function () {
            $(this).children("span").addClass("selected")
        },
        "mouseleave": function () {
            $(this).children("span").removeClass("selected")
        }
    } );
    $("#bd_info").on({
        "mouseenter": function () {
            $("#bd_content").show();
        },
        "mouseleave": function () {
            $("#bd_content").hide()
        }
    });

    /*part1 manager img*/

    setTimeout(function () {
        $("#managers_img").addClass("first_load");
    },500);

    /*scroll animate*/
    var t1=$(".part2_title").offset().top;
    var t2=$("#part3_show").offset().top;
    /*var t3=$("#a3").offset().top;*/
    $(window).scroll(function () {
        var top=$(window).scrollTop();
        if(top>t1/2&&top<1400){
            $(".part2_title").addClass("show_out");
        }else if(top>1400&&top<2300){
            $(".part3_img").addClass("show_out");
        }else if(top>2300){
            $(".part4_show").addClass("show_out");
            $(".part4 .btn").addClass("show_out");
        }
    });

    /*apply form screen lock*/
    var $screen=$("#lock_screen");

    $(".for_apply_form").click(function () {
        $("#apply_form").show();
        var x = $(document).width();
        var y = $(document).height();
        $screen.css({"width": x, "height": y, "display": "block"});
    });

    $(window).resize(function () {
        if ($screen.css("display") == "block") {
            var a = $(window).width();
            var b = $(window).height();
            $screen.css({"width": a, "height": b});
        }
    });

    $screen.click(function () {
        $("#apply_form").hide();
        $(this).css({"width": "0", "height": "0", "display": "none"});
    });
    $("#form_close").click(function () {
        $screen.trigger("click")
    })
});
/**
 * Created by lhj on 2017/7/22 0022.
 */
