function slide(navigation_id, pad_out, pad_in, time, multiplier){

    // 创建目标路径
    var list_elements_all=navigation_id + " li";
    var link_elements_all = list_elements_all + " a";

/*    // 启动定时器用于滑动动画
    var timer = 0;

    // 创建幻灯片动画的所有列表元素
    $(link_elements_all).each(function(i){
        $(this).css("margin-left","-210px");
        // 更新计时器
        timer = (timer*multiplier + time);
        $(this).stop(true,true).animate({ marginLeft: "0" }, timer);
        $(this).stop(true,true).animate({ marginLeft: "15px" }, timer);
        $(this).stop(true,true).animate({ marginLeft: "0" }, timer);
    });*/

    // 创建的所有链接元素的悬停滑动效果
/*    $(list_elements_all).each(function(i){
        $(this).click(function(){
            if($(this).hasClass("similar")){
                $(this).parent().children(".selected").removeClass("selected").addClass("similar").children("a").hover(function () {
                    $(this).stop(true,true).animate({marginLeft: pad_out }, 180);
                },function () {
                    $(this).stop(true,true).animate({marginLeft: pad_in }, 180);
                });
                $(this).removeClass("similar").addClass("selected");
            }
        })
    });*/

    $(link_elements_all).each(function(i){
        $(this).hover(function(){
            $(this).stop(true,true).animate({marginLeft: pad_out }, 180);
        },function(){
            $(this).stop(true,true).animate({marginLeft: pad_in }, 180);

        });
    });
}