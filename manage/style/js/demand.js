$(function(){
	$('.checkbox.remote').on('click',function(){
		var checked= $(this).find('.checked-switch').is(':checked');
		if(checked==true){
			$(this).parent().parent().siblings().removeClass('hide');
		}else {
			$(this).parent().parent().siblings().addClass('hide');
		}
	});
	var Num=0;
	$('#button').on('click',function(){
		Num++;
		if(Num>4){
			Message.showMessage("一次最多上传四张");
			return false;}
		$(this).before('<div class="photo"><div class="img">点击上传</div><input type="file" name="Image[]"  accept="image/gif,image/jpeg,image/png,image/jpg"></div>');
		upload();
	});
});

function upload(){
	$('input[type="file"]').on("change",function() {
		var file = this.files[0];
		if(!/image\/\w+/.test(file.type)){
			Message.showMessage("请确保文件为图像类型");
			return false;
		}
		var size=this.files[0].size;
		if(size>1024*1024*5){
			Message.showMessage("图片不能大于5M");
			return false;
		}
		var aa= $(this).siblings('.img');
		srcs = new FileReader();
		srcs.readAsDataURL(file);
		srcs.onload = function(e) {
			aa.html('<img style="width:80px;height: 80px;" src="' + srcs.result+ '"/>');
		}
	});
}

var demand={
	add:function(){
		var name=$('input[name="name"]').val(),
				demandcat_id=$('input[name="demandcat_id"]').val(),
				shop_id=$('select[name="shop_id"]').find('option:selected').val(),
				budget=$('input[name="budget"]').val(),
				demand_start_time=$('input[name="demand_start_time"]').val(),
				demand_end_time=$('input[name="demand_end_time"]').val();
		if(!name){
			Message.showMessage("名称不能为空");
			return false;
		}
		if(!demandcat_id){
			Message.showMessage("选择需求分类");
			return false;
		}
		if(!shop_id){
			Message.showMessage("选择需求发布店铺");
			return false;
		}
		if(!budget){
			Message.showMessage("需求预算不能为空");
			return false;
		}
		if(budget<0){
			Message.showMessage("需求预算不能小于0");
			return false;
		}
		if(!demand_start_time){
			Message.showMessage("开始时间不能为空");
			return false;
		}
		if(!demand_end_time){
			Message.showMessage("结束时间不能为空");
			return false;
		}
		if(demand_start_time==demand_end_time){
			Message.showMessage("开始时间和结束时间不能为同一天");
			return false;
		}
		if(demand_end_time<demand_start_time) {
			Message.showMessage("结束时间不能小于开始时间");
			return false;
		}
		var options={
			beforeSubmit: function () {
				$('#showLoading').show();
				$("button[type='button']").text("添加中...").attr("disabled", "disabled");
			},
			success:function(o){
				$('#showLoading').hide();
				if(o.isok=='true'){
					Message.showNotify(""+ o.data+"", 1500);
					setTimeout("window.location.href='"+o.url+"'",1600);
				}else{
					Message.showNotify(''+o.data+'', 1500);
					setTimeout("window.location.href='"+o.url+"",1600);
				};
			},
		};
		$("#demandForm").ajaxSubmit(options);
		return false;//防止刷新提交
	},
	edit:function(){
		var name=$('input[name="name"]').val(),
				demandcat_id=$('input[name="demandcat_id"]').val(),
				shop_id=$('select[name="shop_id"]').find('option:selected').val(),
				budget=$('input[name="budget"]').val(),
				demand_start_time=$('input[name="demand_start_time"]').val(),
				demand_end_time=$('input[name="demand_end_time"]').val();
		if(!name){
			Message.showMessage("名称不能为空");
			return false;
		}
		if(!demandcat_id){
			Message.showMessage("选择需求分类");
			return false;
		}
		if(!shop_id){
			Message.showMessage("选择需求发布店铺");
			return false;
		}
		if(!budget){
			Message.showMessage("需求预算不能为空");
			return false;
		}
		if(budget<0){
			Message.showMessage("需求预算不能小于0");
			return false;
		}
		if(!demand_start_time){
			Message.showMessage("开始时间不能为空");
			return false;
		}
		if(!demand_end_time){
			Message.showMessage("结束时间不能为空");
			return false;
		}
		if(demand_start_time==demand_end_time){
			Message.showMessage("开始时间和结束时间不能为同一天");
			return false;
		}
		if(demand_end_time<demand_start_time) {
			Message.showMessage("结束时间不能小于开始时间");
			return false;
		}
		var options={
			beforeSubmit: function () {
				$('#showLoading').show();
				$("button[type='button']").text("编辑中...").attr("disabled", "disabled");
			},
			success:function(o){
				$('#showLoading').hide();
				if(o.isok=='true'){
					Message.showNotify(""+ o.data+"", 1500);
					setTimeout("window.location.href='"+o.url+"'",1600);
				}else{
					Message.showNotify(''+o.data+'', 1500);
					setTimeout("window.location.href='"+o.url+"",1600);
				};
			},
		};
		$("#demandForm").ajaxSubmit(options);
		return false;//防止刷新提交
	}
}






$.fn.demandajax = function(url){
	var PageSize=15,p=1;
	var scrollHandler = function () {
		var scrollT = $(document).scrollTop(); //滚动条滚动高度
		var pageH = $(document).height();  //滚动条高度 
		var winH= $(window).height(); //页面可视区域高度
		var aa = (pageH-winH-scrollT)/winH; 
		if(aa<=0.001){
			if(p>=1){
			  p++;
			}
			ajaxlist(p,url);
		}
	}
	$(window).scroll(scrollHandler);//执行滚动
	ajaxlist(1,url);//默认加载一页
	function ajaxlist(p,url){
		$('#showLoading').show();
		var act='list';

			$.ajax({
			type: "get",
			url:url,
			data:{PageSize:PageSize,p:p,act:act},
			dataType: "json",
			//cache:false,
				success: function (o) {
					$('#showLoading').hide();
					if(o.isok=='true'){
						var jsonListObj = o.data;
						insertListDiv(jsonListObj);
					}else{
						$('.div_null').html(o.data).removeClass('hide');
					}
					},
				beforeSend: function () {
				},
				error: function () {$('#showLoading').hide();
				}
			});
	}
	 //生成数据html,append到div中
    function insertListDiv(json) {
		var $mainDiv = $("#list");
		var html = '';
		for (var i = 0; i< json.length; i++) {
            var name = '';
            if (json[i].name.length > 20) {
                name = json[i].name.substring(0, 23) + "...";
            } else {
                name = '' + json[i].name + '';
            }
            html += '<div class="supplyitem" id="demand_' + json[i].id + '" >';
            // html += '<div class="goods_img"><img src="/' + json[i].demand_thumb + '" alt=""></div>';
            html+='<div class="goods_img"><span class="'+json[i].is_sure+'"></span><img src="/'+json[i].demand_thumb+'" alt=""></div>';
            html += '<div class="supply_c">';
            html += '<h2>' + name + '</h2>';
            html += '<div class="Price"><i></i>预算：<b>￥<em>' + json[i].budget + '</em></b></div>';
            html += '<div class="goods_sn"><i></i>类别：' + json[i].cat_name + '</div>';
            html += '</div>';
            if (json[i].is_sure == 4) {
                html += '<div class="eidt"><span data-href="index.php?a=edit&c=demand&id=' + json[i].id + '"><i></i>编辑</span><span onclick="fail(' + json[i].id + ')" style="text-align: center; line-height: 30px">审核失败</span></div>';
            }else {
            html += '<div class="eidt"><span data-href="index.php?a=edit&c=demand&id=' + json[i].id + '"><i></i>编辑</span><span onclick="del(' + json[i].id + ')"><i></i>删除</span></div>';
           }
			html+='</div>';
		}
		$mainDiv.append(html);
		links();
	}
}
//删除
function del(id){
	Message.showConfirm("您确定要删除此条数据吗？", "确定", "取消", function () {
		$('#showLoading').show();
		$.ajax({
		url: 'index.php?a=del&c=demand',
		type: 'POST',
		dataType: 'json',
		data: {id: id},
		})
		.done(function (o) {
			if (o.isok == 'true') {
				$('#showLoading').hide();
				Message.showNotify("" + o.data + "", 1000);
				$('#demand_' + id).remove();
			} else {
				Message.showNotify("" + o.data + "", 1000);
			}
		})
		.fail(function () {
		})
		.always(function () {
		$('#showLoading').hide();
		});
	});
}

function links(){
	$('[data-href]').on('click',function(e){
		var url=$(this).attr('data-href');
		loadurl(url);//加载跳转链接
		return false;
	});
}


function fail(id) {
    $.get('index.php?a=fail&c=demand',{id:id},function (re) {
        if(re.isok==0){
            Message.showMessage(''+re.remarks+'');
        }else {
            Message.showMessage(''+re.data+'');
        }
    })
}