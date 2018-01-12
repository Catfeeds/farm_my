$('#evaluate').click(function () {
    $.ajax({
        url:"{:U('Session/ifsession')}",
        type:"POST",
        success:function(data) {
            if(data!=true){
                lock();
                return false
            } else {
                return true;
            }
        }
    })
})
//评论评分都不能为空
$('#evaluate').submit(function () {
    var text = $('#xnb_score').val();
    if (text == "") {
        alert("评论不能为空");
        return false;
    } else {
        return true;
    }
})
//点赞
$(".great").click(function () {
    var commentid = $(this).attr("attr");
    $.ajax({
        url:"{:U('Trade/like')}",
        type:"POST",
        data:"commentid=" + commentid + "&status=0",
        success:function (data) {
            if (isNaN(data)) {
                alert(data);
            } else {
                $("#great" + commentid).text(data);
            }
        }
    });
});
//平庸
$(".soso").click(function () {
    var commentid = $(this).attr("attr");
    $.ajax({
        url:"{:U('Trade/like')}",
        type:"POST",
        data:"commentid=" + commentid + "&status=1",
        success:function (data) {
            if (isNaN(data)) {
                alert(data);
            } else {
                $("#soso" + commentid).text(data);
            }
        }
    });
})
//bad
$(".bad").click(function () {
    var commentid = $(this).attr("attr");
    $.ajax({
        url:"{:U('Trade/like')}",
        type:"POST",
        data:"commentid=" + commentid + "&status=2",
        success:function (data) {
            if (isNaN(data)) {
                alert(data);
            } else {
                $("#bad" + commentid).text(data);
            }
        }
    });
})