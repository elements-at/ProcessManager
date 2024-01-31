var timer = null;
$(function () {
    if ($('#autorefresh').is(':checked')) {
        startRefresh();
    }

    $('#autorefresh').on('change',function () {
        alert('in cahnge');
        if (!this.checked) {
            clearTimeout(timer);
        } else {
            startRefresh();
        }
    });
});


function startRefresh() {
    timer = setTimeout(startRefresh, 1000);
    $.get(location.href + '&ajax=1', function (data) {
        $('#content').html(data.html);
        if(!data.monitoringItem.pid){
            $('#autorefresh').attr('checked', false);
            clearTimeout(timer);
        }
        $(window).scrollTop($(document).height());
    });
}