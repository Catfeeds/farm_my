/**
 * Created by 杨尚云 on 2017/6/24.
 */
$(document).ready(function () {
    $(".listTurn_click").click= function () {
        $(".listTurn_click").removeClass("listActive");
        $(this).addClass("listActive");

    }
});