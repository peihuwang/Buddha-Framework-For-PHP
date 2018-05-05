
$.fn.ajaxshiolist = function(url) {
    var PageSize = 15, p = 1;
    var scrollHandler = function () {
        var scrollT = $(document).scrollTop(); //滚动条滚动高度
        var pageH = $(document).height();  //滚动条高度
        var winH = $(window).height(); //页面可视区域高度
        var aa = (pageH - winH - scrollT) / winH;
        if (aa <= 0.001) {
            if (p >= 1) {
                p++;
            }
            ajaxlist(p, url);
        }
    }
    $(window).scroll(scrollHandler);//执行滚动
    ajaxlist(1, url);//默认加载一页
    function ajaxlist(p, url) {
        $('#showLoading').show();
        var act = 'list';
        $.ajax({
            type: "get",
            url: url,
            data: {PageSize: PageSize, p: p, act: act},
            dataType: "json",
            //cache:false,
            success: function (o) {
                $('#showLoading').hide();
                if (o.isok == 'true') {
                    insertListDiv(o);
                } else {
                    $('.div_null').html(o.data).removeClass('hide');
                }
            },
            beforeSend: function () {
            },
            error: function () {
                $('#showLoading').hide();
            }
        });
    }
}

function insertListDiv(json){
    var html = template('demo', json);
    $('#list').append(html);
    $('[data-href]').on('click',function(e){
        var url=$(this).attr('data-href');
        loadurl(url);//加载跳转链接
        return false;
    });
}


