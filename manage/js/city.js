var jsonarr,nextlevel='areas1',optionsel='0';
$(function(){
    jsonarr = {'father':optionsel};
    citya_min(jsonarr,nextlevel);
    $('.areas').change(function(){
        var optionsel = $(this).find('option:selected').val();
         var  nextlevel=$(this).parent().next().find('.areas').attr('id');
        if(!optionsel){
            $(this).parent().nextAll().addClass('hide');
        }else {
            $(this).parent().next().removeClass('hide');
        }
        $('#father').val(optionsel);
        jsonarr = {'father':optionsel};
        citya_min(jsonarr,nextlevel)
    })
})

function addArea(){
    var modes=$('#areaMode').find('tr').clone(true);
    $('#createareas').append(modes)
}
function citya_min(jsonarr,nextlevel) {
    var url = 'index.php?a=ajax&c=region&t='+Math.random();
    var jsonstr = JSON.stringify(jsonarr);
    $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            data:{json:jsonstr},
        })
        .done(function(json) {
            var html='',srt='';
            var createareas=$('#createareas');
            var option=$('#'+nextlevel);
            if(json.isok=='true'){
                var json=json.data;
                srt+='<option value="">请选择</option>';
                for (var i = 0; i < json.length; i++) {
                    html+='<tr id="'+json[i].id+'">';
                    html+='<td><input type="text" name="areas['+json[i].id+'][id]" value="'+json[i].id+'" class="form-control" readonly="readonly"/></td>';
                    if(json[i].name){
                        html+='<td><input type="text" name="areas['+json[i].id+'][name]" value="'+json[i].name+'" class="form-control"></td>';
                    }else{
                        html+='<td><input type="text" name="areas['+json[i].id+'][name]" value="'+json[i].fullname+'" class="form-control"></td>';
                    }

                    html+='<td><input type="text" name="areas['+json[i].id+'][fullname]" value="'+json[i].fullname+'" class="form-control"></td>';
                    html+='<td><input type="text" name="areas['+json[i].id+'][pinyin]" value="'+json[i].pinyin+'" class="form-control"></td>';
                    html+='<td><input type="text" name="areas['+json[i].id+'][lat]" value="'+json[i].lat+'" class="form-control"></td>';
                    html+='<td><input type="text" name="areas['+json[i].id+'][lng]" value="'+json[i].lng+'" class="form-control"></td>';
                    if(json[i].level>3){
                        //href="index.php?a=del&c=region&id='+json[i].id+'&father='+json[i].father+'"
                    html+='<td><a  onclick="delnav('+json[i].id+','+json[i].father+')">删除</a></td>';
                    }
                    html+='</tr>';
                    srt+='<option value="'+json[i].id+'">'+json[i].name+'</option>';
                }
                createareas.html(html);
                option.html(srt);
                areas();
            }
        })
        .fail(function() {
        })
        .always(function() {});
}
function areas() {
    $('.areas').each(function() {
        var selectlength = $(this).find('option').length;
        if (selectlength == 0) {
            $(this).addClass('hide');
        }else {
            $(this).removeClass('hide');
        }
    })
}
function delnav(id,father){
    var url = 'index.php?a=ajaxdel&c=region';
    $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            data:{id:id,father:father},
        })
        .done(function(json) {
            if(json.isok=='true'){
                alert(json.msg);
                $('#'+id+'').hide();
            }else{
                alert(json.msg);
            }
        })
        .fail(function() {
        })
        .always(function() {});



   // alert(id);
   // return confirm("确认要删除此会员吗？");
}


