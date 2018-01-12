/**
 * Created by DENG on 2017/7/14.
 */
$(function () {
    $('#standardmoney').change(function () {
        var url = "/Admin`Trading`add.html";
        var value=$("#standardmoney option:selected").val();
        $.ajax({
            url: url,
            type: "POST",
            data: {value:value},
            success: function (data) {

            }

        })
    })
    
})