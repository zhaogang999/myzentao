//新增，控制增减评审详情
$(function(){
    $('#qa').on('change',function () {
        if($(this).val() == 'QA')
        {
            $('#qaAudit').show();
        }else{
            $('#qaAudit').hide();
        }
    });
    $('.add').live('click',function(){
        var tr = $(this).parent().parent().clone();
         tr.find('input[type="text"]').attr('value','');
         tr.find('select').attr('value','');
         tr.find('textarea').attr('value','');
        //return alert(tr.find('input[id="auditID1"]'));
        var num = $('.text-center').length;
        tr.find('input[name="auditID[]"]').attr('id','auditID'+num);
        tr.find('textarea[name="noDec[]"]').attr('id','noDec'+num);
        tr.find('select[name="noType[]"]').attr('id','noType'+num);
        tr.find('select[name="serious[]"]').attr('id','serious'+num);
        tr.find('textarea[name="cause[]"]').attr('id','cause'+num);
        tr.find('textarea[name="measures[]"]').attr('id','measures'+num);
        //追加新的tr元素
        $(this).parent().parent().after(tr);
    });
    $('.del').live('click',function(){
        if(($('.text-center').length) == 2){
            var tr = $(this).parent().parent().clone();
            tr.find('input[type="text"]').attr('value','');
            tr.find('select').attr('value','');
            tr.find('textarea').attr('value','');
            //追加新的tr元素
            $(this).parent().parent().after(tr);
        }
        //事件处理程序
        $(this).parent().parent().remove();
    });
    $('#submit').on('click',function () {
        if($('#qa').val() == 'QA'){
            var i = 1;
            var num = $('.text-center').length;
             //return alert(num);
            for(i=1;i<num;i++){
                if($('#auditID'+i).val() == ''){
                    alert('编号不能为空');
                }
                 if($('#noDec'+i).val() == ''){
                    alert('不符合描述不能为空');
                }
                 if($('#noType'+i).val() == ''){
                    alert('不符合类型不能为空');
                }
                 if($('#serious'+i).val() == ''){
                    alert('严重度不能为空');
                }
                 if($('#cause'+i).val() == ''){
                    alert('原因分析不能为空');
                }
                if($('#measures'+i).val() == ''){
                    alert('纠正预防措施不能为空');
                }
            }
        }
    })
});