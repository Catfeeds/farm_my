$(function () {
    var dates = $("#date_start,#date_end");

    dates.datepicker({
        dateFormat: 'yy-mm-dd',
        //dayNames : ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
        //dayNamesShort : ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
        dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
        monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
        monthNamesShort: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
        altField: '#abc',
        altFormat: 'dd/mm/yy',
        appendText: '日历',
        showWeek: false,
        //weekHeader : '周',
        firstDay: 1,
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        onSelect: function (selectedDate) {
            var option = this.id == "date_start" ? "minDate" : "maxDate";
            dates.not(this).datepicker("option", option, selectedDate);
        }
    });

});
/**
 * Created by Administrator on 2017/7/13 0013.
 */
