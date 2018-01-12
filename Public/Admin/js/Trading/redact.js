/**
 * Created by DENG on 2017/7/14.
 */
$(function () {
    //本位币选择值
    $('#standardmoney').change(function () {
        var url = "/Admin`Trading`redact`id`34.html";
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