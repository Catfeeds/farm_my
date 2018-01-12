/**
 * Created by 48930 on 2017/7/7.
 */
function lock() {
    var $screen=$("#lock_screen");
    $(".showlock").click(function () {
        $("#enter_form_lock").show();
        var x = $(document).width();
        var y = $(document).height();
        $screen.css({"width": x, "height": y, "display": "block"});
    });
    $(window).resize(function () {
        if ($screen.css("display") == "block") {
            var a = $(document).width();
            var b = $(document).height();
            $screen.css({"width": a, "height": b});
        }
    });

    $screen.click(function () {
        $("#enter_form_lock").hide();
        $(this).css({"width": "0", "height": "0", "display": "none"});
    });
    $("#enter_form_close").click(function () {
        $screen.trigger("click")
        $('#enter_form_lock').css("display","none");
    });
};