//新增，控制增减评审详情
$(function(){
    $('.add').live('click',function(){
        var tr = $(this).parent().parent().clone();
        //console.log($(this).parent().parent().find(':hidden').val());
         tr.find('input[type="hidden"]').attr('value','');
         tr.find('input[type="text"]').attr('value','');
         tr.find('textarea').attr('value','');
         tr.find('select').attr('value','0');
        //追加新的tr元素
        $(this).parent().parent().after(tr);
    });
    $('.del').live('click',function(){
        //事件处理程序
        $(this).parent().parent().remove();
    });
});