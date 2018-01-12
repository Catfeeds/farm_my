var yanjzetime=0;
var oldpwd=0;
var newpwd=0;
var olddeal=0;
var newdeal=0;

var arr = new Array();
arr[0] = "东城,西城,崇文,宣武,朝阳,丰台,石景山,海淀,门头沟,房山,通州,顺义,昌平,大兴,平谷,怀柔,密云,延庆";
arr[1] = "黄浦,卢湾,徐汇,长宁,静安,普陀,闸北,虹口,杨浦,闵行,宝山,嘉定,浦东,金山,松江,青浦,南汇,奉贤,崇明";
arr[2] = "和平,东丽,河东,西青,河西,津南,南开,北辰,河北,武清,红挢,塘沽,汉沽,大港,宁河,静海,宝坻,蓟县";
arr[3] = "万州,涪陵,渝中,大渡口,江北,沙坪坝,九龙坡,南岸,北碚,万盛,双挢,渝北,巴南,黔江,长寿,綦江,潼南,铜梁,大足,荣昌,壁山,梁平,城口,丰都,垫江,武隆,忠县,开县,云阳,奉节,巫山,巫溪,石柱,秀山,酉阳,彭水,江津,合川,永川,南川";
arr[4] = "石家庄,邯郸,邢台,保定,张家口,承德,廊坊,唐山,秦皇岛,沧州,衡水";
arr[5] = "太原,大同,阳泉,长治,晋城,朔州,吕梁,忻州,晋中,临汾,运城";
arr[6] = "呼和浩特,包头,乌海,赤峰,呼伦贝尔盟,阿拉善盟,哲里木盟,兴安盟,乌兰察布盟,锡林郭勒盟,巴彦淖尔盟,伊克昭盟";
arr[7] = "沈阳,大连,鞍山,抚顺,本溪,丹东,锦州,营口,阜新,辽阳,盘锦,铁岭,朝阳,葫芦岛";
arr[8] = "长春,吉林,四平,辽源,通化,白山,松原,白城,延边";
arr[9] = "哈尔滨,齐齐哈尔,牡丹江,佳木斯,大庆,绥化,鹤岗,鸡西,黑河,双鸭山,伊春,七台河,大兴安岭";
arr[10] = "南京,镇江,苏州,南通,扬州,盐城,徐州,连云港,常州,无锡,宿迁,泰州,淮安";
arr[11] = "杭州,宁波,温州,嘉兴,湖州,绍兴,金华,衢州,舟山,台州,丽水";
arr[12] = "合肥,芜湖,蚌埠,马鞍山,淮北,铜陵,安庆,黄山,滁州,宿州,池州,淮南,巢湖,阜阳,六安,宣城,亳州";
arr[13] = "福州,厦门,莆田,三明,泉州,漳州,南平,龙岩,宁德";
arr[14] = "南昌市,景德镇,九江,鹰潭,萍乡,新馀,赣州,吉安,宜春,抚州,上饶";
arr[15] = "济南,青岛,淄博,枣庄,东营,烟台,潍坊,济宁,泰安,威海,日照,莱芜,临沂,德州,聊城,滨州,菏泽";
arr[16] = "郑州,开封,洛阳,平顶山,安阳,鹤壁,新乡,焦作,濮阳,许昌,漯河,三门峡,南阳,商丘,信阳,周口,驻马店,济源";
arr[17] = "武汉,宜昌,荆州,襄樊,黄石,荆门,黄冈,十堰,恩施,潜江,天门,仙桃,随州,咸宁,孝感,鄂州";
arr[18] = "长沙,常德,株洲,湘潭,衡阳,岳阳,邵阳,益阳,娄底,怀化,郴州,永州,湘西,张家界";
arr[19] = "广州,深圳,珠海,汕头,东莞,中山,佛山,韶关,江门,湛江,茂名,肇庆,惠州,梅州,汕尾,河源,阳江,清远,潮州,揭阳,云浮";
arr[20] = "南宁,柳州,桂林,梧州,北海,防城港,钦州,贵港,玉林,南宁地区,柳州地区,贺州,百色,河池";
arr[21] = "海口,三亚";
arr[22] = "成都,绵阳,德阳,自贡,攀枝花,广元,内江,乐山,南充,宜宾,广安,达川,雅安,眉山,甘孜,凉山,泸州";
arr[23] = "贵阳,六盘水,遵义,安顺,铜仁,黔西南,毕节,黔东南,黔南";
arr[24] = "昆明,大理,曲靖,玉溪,昭通,楚雄,红河,文山,思茅,西双版纳,保山,德宏,丽江,怒江,迪庆,临沧";
arr[25] = "拉萨,日喀则,山南,林芝,昌都,阿里,那曲";
arr[26] = "西安,宝鸡,咸阳,铜川,渭南,延安,榆林,汉中,安康,商洛";
arr[27] = "兰州,嘉峪关,金昌,白银,天水,酒泉,张掖,武威,定西,陇南,平凉,庆阳,临夏,甘南";
arr[28] = "银川,石嘴山,吴忠,固原";
arr[29] = "西宁,海东,海南,海北,黄南,玉树,果洛,海西";
arr[30] = "乌鲁木齐,石河子,克拉玛依,伊犁,巴音郭勒,昌吉,克孜勒苏柯尔克孜,博 尔塔拉,吐鲁番,哈密,喀什,和田,阿克苏";
arr[31] = "香港";
arr[32] = "澳门";
arr[33] = "台北,高雄,台中,台南,屏东,南投,云林,新竹,彰化,苗栗,嘉义,花莲,桃园,宜兰,基隆,台东,金门,马祖,澎湖";


$("#province").change(function () {
    getCity();
});

init();

//银行
function tijiaobankphone() {
    if ($('#deposit_name').val()==""){
        ShowHintBox("请填写完整信息",false);
        return false;
    }
    if($('#bank_number').val()=="" || $('#bank_number_new').val()=="" ){
        ShowHintBox("银行卡号不可为空",false);
        return false;
    }
    if($('#bank_trade_pw').val()==""){
        ShowHintBox("交易密码不可为空",false);
        return false;
    }
    if($('#bank_number').val()!=$('#bank_number_new').val()){
        ShowHintBox("两次卡号不一致",false)
        return false
    }
    var pattern = /^([1-9]{1})(\d{14}|\d{18})$/;
    if(!pattern.test($('#bank_number').val())){
        ShowHintBox("银行卡格式不正确",false);
        return false
    }
    if(bankre==1 && olddeal==1){
        var form=$('#ajaxbank').parents().parent('.ajaxbank_form');
        if (form.get(0)==undefined){
            form=$('#ajaxbank').parent();
        }
        $.ajax({
            url:form.attr('action'),
            type:form.attr('method'),
            data:form.serialize(),
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    ShowHintBox(data.info,false);
                    return false
                }
                ShowHintBox(data.info,true);
                olddeal=0;
                bankre=0;
                setTimeout(function () {
                    self.location=document.referrer;
                }, 1500);
            }
        });
        return false
    }



}
//银行卡号
function Regbankss(ss) {
    var pattern = /^([1-9]{1})(\d{15,20})$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            set_poput_yanzhen("银行卡号格式正确",true);
        }else{
            set_poput_yanzhen("银行卡号格式不正确",false);
            return false;
        }

    }else{
        set_poput_yanzhen("银行卡号不可为空",false);
        return false;
    }
}
//新的银行卡号
function Regbanknew(ss,mm) {
    var bankcard=$('#bank_number').val();
    var pattern = /^([1-9]{1})(\d{14}|\d{18})$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            // bankre=1;
            if($(ss).val()==$(mm).val()){
                set_poput_yanzhen("银行卡号格式正确",true);
                bankre=1;
            } else {
                set_poput_yanzhen("两次卡号不一致",false);
                return false;
            }

        }else{
            set_poput_yanzhen("银行卡号格式不正确",false);
            return false;
        }

    }else{
        set_poput_yanzhen("银行卡号不可为空",false);
        return false;
    }
}
//交易密码
function Reg(ss) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            //set_poput_yanzhen("交易密码可用",true);
            $(ss).parent().find("span.red_tips").hide();
            $(ss).parent().find("span.green_tips").show();
            olddeal=1;
        }else{
            //set_poput_yanzhen("密码格式不正确",false);
            $(ss).parent().find("span.red_tips").show();
            $(ss).parent().find("span.green_tips").hide();
            return false;
        }
    }
}


function init() {
    var city = document.getElementById("city");
    var cityArr = arr[0].split(",");
    for (var i = 0; i < cityArr.length; i++) {
        city[i] = new Option(cityArr[i], cityArr[i]);
    }
}

function getCity() {
    var pro = document.getElementById("province");
    var city = document.getElementById("city");
    var index = pro.selectedIndex;
    var cityArr = arr[index].split(",");
    city.length = 0;
    //将城市数组中的值填充到城市下拉框中
    for (var i = 0; i < cityArr.length; i++) {
        city[i] = new Option(cityArr[i], cityArr[i]);
    }
}
//验证消息
function set_poput_yanzhen(title,code){
    if(code){
        $(".notice").css({'color':'#45bf4a','display':'block'});
    }else{
        $(".notice").css({'color':'red','display':'block'});
    }
    $(".notice").html(title);
    $(".notice").show(500);
    setTimeout(function () {
        $(".notice").hide(500);
    }, 3000);
}
//手机绑定
function phoneyanzheng() {
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#get_identify_code').val();
    var yanzheng_num=$('#yanzheng_num').val();
    if(yanjzetime==0){
        $.post("index.php/Wap/Photoma/sms",{phone_num:phone_num,cipher_code:cipher_code,yanzheng_num:yanzheng_num},function (data) {
            if(data.status==true){
                ShowHintBox(data.info,false);
                yanjzetime=59;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#get_identify_code').html(yanjzetime+"秒")
                        $('#get_identify_code').css({"pointer-events": "none" }); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#get_identify_code').html("免费获取验证码");
                        $('#get_identify_code').css({"pointer-events": "auto" }); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }else {
                ShowHintBox(data.info,false);
            }
        })

    }
}
function addWap() {
    var phone_num=$('#phone_num').val();
    var cipher_code=$('#cipher_code').val();
    $.ajax({
        url:"index.php/Wap/Safety/reg",
        type:"POST",
        dataType:'json',
        data:{phone_num:phone_num,cipher_code:cipher_code},
        async: false,
        success:function (data) {
            if(data.status==true){
                ShowHintBox(data.info,true);
            }else {
                ShowHintBox(data.info,false);
                return false;
            }

        }
    });
}
//手机短信
function phonejiechu() {
    var phone_num=$('#get_identify_code_unbind').attr("value");
    var cipher_code=$('#get_identify_code').val();
    var yanzheng_num=$('#yanzheng_num_unbind').val();
    if(yanjzetime==0){
        $.post("index.php/Wap/Photoma/sms",{phone_num:phone_num,yanzheng_num:yanzheng_num},function (data) {
            if(data.status==true){
                set_poput_yanzhen(data.info,false);
                yanjzetime=59;
                var ss= setInterval(function () {
                    if(yanjzetime>0){
                        yanjzetime= yanjzetime-1;
                        $('#get_identify_code_unbind').html(yanjzetime+"秒");
                        $('#get_identify_code_unbind').removeAttr("onclick"); //移除click
                    }else if(yanjzetime<=0){
                        clearInterval(ss);
                        $('#get_identify_code_unbind').html("发送验证码");
                        $('#get_identify_code_unbind').attr("onclick","phonejiechu()"); //移除click
                        yanjzetime=0;
                    }
                },1000)
            }else {
                set_poput_yanzhen(data.info,false);
            }
        })
    }
}

//解绑手机
function jiechuWap() {
    var phone_num=$('#get_identify_code_unbind').attr("value");
    var cipher_code=$('#cipher_code_unbind').val();
    $.ajax({
        url:"index.php/Wap/Safety/jiechu",
        type:"POST",
        dataType:'json',
        data:{phone_num:phone_num,cipher_code:cipher_code},
        async: false,
        success:function (data) {
            if(data.status==true){
                ShowHintBox(data.info,true);
            }else {
                ShowHintBox(data.info,false);
                return false;
            }

        }
    });
}

//修改登录密码
function comparison() {
    var newpwdobj=$('#new_enter_pw').val();
    var newpwdobj2=$('#re_new_enter_pw').val();
    if (newpwdobj==newpwdobj2){
        newpwd=1;
        if(newpwd==1 && oldpwd==1){
            var form=$('#enter_pw_btn').parents().parent('.ajaxa_form');
            if (form.get(0)==undefined){
                form=$('#enter_pw_btn').parent();
            }
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:form.serialize(),
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        ShowHintBox(data.info,false);
                        return false
                    }
                    ShowHintBox(data.info,true);

                    oldpwd=0;
                    newpwd=0;
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                }
            });
            return false
        }
    }else{
        set_poput_yanzhen("两次密码不一致",false);
    }
}

//修改交易密码
function tijiaodeal() {
    var newdealobj=$('#new_trade_pw').val();
    var newdealobj2=$('#re_new_trade_pw').val();
    if (newdealobj==newdealobj2){
        newdeal=1;
        if(newdeal==1 && olddeal==1){
            var form=$('#trade_pw_btn').parents().parent('.ajax_form');
            if (form.get(0)==undefined){
                form=$('#trade_pw_btn').parent();
            }
            $.ajax({
                url:form.attr('action'),
                type:form.attr('method'),
                data:form.serialize(),
                dataType:'json',
                success:function (data) {
                    if (data.status!=true){   //请求失败
                        ShowHintBox(data.info,false);
                        return false
                    }
                    ShowHintBox(data.info,true);
                    olddeal=0;
                    newdeal=0;
                    setTimeout(function () {
                        window.history.back();
                    }, 1500);
                }
            });
            return false
        }
    }else{
        set_poput_yanzhen("两次密码不一致",false);
    }
}

//密码格式
function RegeMatch(ss){
    var pattern = /^[a-zA-Z]\w{5,17}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            set_poput_yanzhen("密码可用",true);
            oldpwd=1;
        }else{
            set_poput_yanzhen("密码格式不正确",false);
            return false;
        }
    }
}
//交易密码格式
function newdealReg(ss,mm) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            set_poput_yanzhen("交易密码可用",true);
            // $(ss).parent().find("span.red_tips").hide();
            // $(ss).parent().find("span.green_tips").show();
            if($(ss).val()==$(mm).val()){
                set_poput_yanzhen("交易密码可用",true);
            }else {
                if($(mm).val() != "" && $(mm).val() != null){
                    set_poput_yanzhen("交易密码不一致",false);
                }
            }
            olddeal=1;
        }else{
            set_poput_yanzhen("交易密码格式不正确，6位数字",false);
            if($(mm).val() != "" && $(mm).val() != null){
                set_poput_yanzhen("交易密码不一致",false);
            }

            return false;
        }
    }
}

//新的交易密码格式验证
function renewdealReg(ss,mm) {
    var pattern = /^\d{6}$/;
    if($(ss).val() != "" && $(ss).val() != null){
        if(pattern.test($(ss).val())){
            /*set_poput_yanzhen("交易密码可用",true);*/
            if($(ss).val()==$(mm).val()){
                $(ss).parent().find("span.red_tips").hide();
                $(ss).parent().find("span.green_tips").show();
            }else{
                $(ss).parent().find("span.green_tips").hide();
                $(ss).parent().find("span.red_tips").show().children("span").text("密码不一致");
            }

            newdeal=1;
        }else{
            /*    set_poput_yanzhen("密码格式不正确",false);*/
            $(ss).parent().find("span.red_tips").show().children("span").text("交易密码必须是6位数字");
            $(ss).parent().find("span.green_tips").hide();

            return false;
        }
    }
}
$(function () {
    $('.ajaxa_form').find('button[type=submit]').click(function () {
        var form=$(this).parents().parent('.ajaxa_form');
        if (form.get(0)==undefined){
            form=$(this).parent();
        }
        $.ajax({
            url:form.attr('action'),
            type:form.attr('method'),
            data:form.serialize(),
            dataType:'json',
            success:function (data) {
                if (data.status!=true){   //请求失败
                    ShowHintBox(data.info,false);
                    return false
                }
                ShowHintBox(data.info,true);
            }
        });
        return false
    })
})