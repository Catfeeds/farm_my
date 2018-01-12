/**
 * Created by 48930 on 2017/7/14.
 */

function back() {
    $.ajax({
        url:"/Admin`Userqian`href",
        type:"post",
        data:"",
        dataType:'json',
        success:function (data) {
            console.log(data)
            if (data.status==true){
                location.href=data.href;
            }else{
                location.href=document.referrer;
            }
           

        }
    });
}