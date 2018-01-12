$(function () {

    $("#pay_bind_title").on("click", "span", function () {
        leftShow(this);
        var this_id = $(this).attr("id");
        switch (this_id) {
            case "alipay_bind":
                leftShow("#alipay_form_out");
                leftShow("#notice_alipay");
                break;
            case "wechat_bind":
                leftShow("#wechat_form_out");
                leftShow("#notice_wechat");
                break;
        }
    });
});

/**
 * Created by lhj on 2017/7/8 0008.
 */
