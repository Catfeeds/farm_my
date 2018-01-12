/**
 * Created by asus on 2017/6/21.
 */

function from() {
    alert(document.myform.action);//返回值：http://localhost:8080/XXX/ssss
  alert(document.myform.attributes["action"].value);//返回值：ssss    与上一行有区别
//   以下三行，随便哪一行都行
     document.getElementById('myform').action='new_url';
     document.myform.action='new_url';
     document.myform.attributes["action"].value = 'new_url';
}