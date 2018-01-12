
$(function () {
    /*change password*/
    $("#change_pw_title").on("click", "span", function () {
        leftShow(this);
        var this_id = $(this).attr("id");
        switch (this_id) {
            case "change_enter":
                leftShow("#enter_form");
                leftShow("#notice_for_enter");
                break;
            case "change_trade":
                leftShow("#trade_form");
                leftShow("#notice_for_trade");
                break;
        }
    });

    /*safe center change trade password*/
    var url=window.location.href,index =url.indexOf("pane");
    if(index != -1){
        $("#safe_list").children("li.selected").removeClass("selected").addClass("similar")
        $($("#safe_list").children("li")[3]).removeClass("similar").addClass("selected");
      $("#change_trade").trigger("click")
    }
});

/**
 * Created by lhj on 2017/7/8 0008.
 */
