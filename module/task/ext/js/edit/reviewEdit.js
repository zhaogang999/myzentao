//新增，控制增减评审详情
$(function(){
    //bugxiugai
    $('#status').on('change',function () {
        if($('#status').val() == 'done' && $('#type').val() == 'review')
        {
            $('.create').show();
        }else{
            $('.create').hide();
        }
    });
    
    $('#type').on('change',function () {
        if($('#status').val() == 'done' && $('#type').val() == 'review')
        {
            $('.create').show();
        }else{
            $('.create').hide();
        }
    });

    $('.add').live('click',function(){
        var tr = $(this).parent().parent().clone();
         tr.find('input[type="hidden"]').attr('value','');
         tr.find('input[type="text"]').attr('value','');
         tr.find('textarea').attr('value','');
         tr.find('select').attr('value','0');
        //追加新的tr元素
        $(this).parent().parent().after(tr);
    });
    $('.del').live('click',function(){
        //事件处理程
        if($(this).parent().parent().parent().children().length == 1){
            var tr = $(this).parent().parent().clone();
            tr.find('input[type="hidden"]').attr('value','');
            tr.find('input[type="text"]').attr('value','');
            tr.find('textarea').attr('value','');
            tr.find('select').attr('value','0');
            //追加新的tr元素
            $(this).parent().parent().after(tr);
        }
        if($(this).parent().parent().find(':hidden').val() == '')
        {
            $(this).parent().parent().remove();
        }
        if($(this).parent().parent().find(':hidden').val() != '')
        {
            if(confirm('点击确定将会永久删除该条记录'))
            {
                var _this = $(this);
                var objectID = $(this).parent().parent().find('input[type="hidden"]').val();
                $url = createLink('task', 'deleteReview', 'reviewDetailID=' + objectID);
                $.get($url, function(data) {
                    //window.location.reload();
                    _this.parent().parent().remove();
                },'json');
            }
        }
    });
    $('.delAudit').live('click',function(){
        //事件处理程序
        if($(this).parent().parent().parent().children().length == 1){
            var tr = $(this).parent().parent().clone();
            tr.find('input[type="hidden"]').attr('value','');
            tr.find('input[type="text"]').attr('value','');
            tr.find('textarea').attr('value','');
            tr.find('select').attr('value','0');
            //追加新的tr元素
            $(this).parent().parent().after(tr);
        }
        if($(this).parent().parent().find(':hidden').val() == '')
        {
            if(($('.text-center').length) == 2){
                var tr = $(this).parent().parent().clone();
                tr.find('input[type="text"]').attr('value','');
                tr.find('select').attr('value','');
                //追加新的tr元素
                $(this).parent().parent().after(tr);
            }
            $(this).parent().parent().remove();
        }
        if($(this).parent().parent().find(':hidden').val() != '')
        {
            if(confirm('点击确定将永久删除该条记录'))
            {
                var _this = $(this);
                var objectID = $(this).parent().parent().find('input[type="hidden"]').val();
                var url = createLink('task', 'deleteAudit', 'auditID=' + objectID);
                $.get(url, function(data) {
                    //window.location.reload();
                    _this.parent().parent().remove();
                },'json');
            }
        }
    });
});