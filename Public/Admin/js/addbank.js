$(function () {
 
    $('.files').click(function () {
        $('[type=file]').trigger('click');
    })

    $('input[type=file]').change(function () {
       
        var  fr= new FileReader()
        fr.onload=function () {
            $('.tb').attr("src",fr.result)
        }
        fr.readAsDataURL(this.files[0])
    })
    
    $('.submit-btn').click(function () {
        var formData= new FormData($('form').get(0));
        $.ajax({
            url:"",
            type:"post",
            data:formData,
            processData:false,
            contentType:false,
            dataType:"json",
            success:function (data) {
                if (data.status!=true){  //失败
                    set_poput_code(data.info,false);
                    return false;
                }
                    set_poput_code(data.info,true)
            }
        })
    })
})
