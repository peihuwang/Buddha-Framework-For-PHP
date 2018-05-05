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



var lease={
	add:function(){
		var lease_name=$('input[name="lease_name"]').val(),
				leasecat_id=$('input[name="leasecat_id"]').val(),
				rent=$('input[name="rent"]').val(),
				lease_start_time=$('input[name="lease_start_time"]').val(),
				lease_end_time=$('input[name="lease_end_time"]').val();
		if(!lease_name){
			Message.showMessage("名称不能为空");
			return false;
		}
		if(!leasecat_id){
			Message.showMessage("选择租赁分类");
			return false;
		}
		if(!rent){
			Message.showMessage("租金不能为空");
			return false;
		}
		if(!lease_start_time){
			Message.showMessage("开始时间不能为空");
			return false;
		}
		if(!lease_end_time){
			Message.showMessage("结束时间不能为空");
			return false;
		}
		if(lease_start_time==lease_end_time){
			Message.showMessage("开始时间和结束时间不能为同一天");
			return false;
		}
		if(lease_end_time<lease_start_time) {
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
		$("#leaseForm").ajaxSubmit(options);
		return false;//防止刷新提交
	},

	edit:function(){
		var lease_name=$('input[name="lease_name"]').val(),
				leasecat_id=$('input[name="leasecat_id"]').val(),
				rent=$('input[name="rent"]').val(),
				lease_start_time=$('input[name="lease_start_time"]').val(),
				lease_end_time=$('input[name="lease_end_time"]').val();
				if(!lease_name){
					Message.showMessage("名称不能为空");
					return false;
				}
				if(!leasecat_id){
					Message.showMessage("选择租赁分类");
					return false;
				}
				if(!rent){
					Message.showMessage("租金不能为空");
					return false;
				}
				if(!lease_start_time){
					Message.showMessage("开始时间不能为空");
					return false;
				}
				if(!lease_end_time){
					Message.showMessage("结束时间不能为空");
					return false;
				}
				if(lease_start_time==lease_end_time){
					Message.showMessage("开始时间和结束时间不能为同一天");
					return false;
				}
				if(lease_end_time<lease_start_time) {
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
		$("#leaseForm").ajaxSubmit(options);
		return false;//防止刷新提交
	},
}








$.fn.leaseajax = function(url){
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
			var name='';
			if(json[i].lease_name.length>20){
				name=json[i].lease_name.substring(0,23)+"...";
			}else{
				name= ''+json[i].lease_name+'';
			}
			html+='<div class="supplyitem" id="lease_'+json[i].id+'" >';
			html+='<div class="goods_img"><img src="/'+json[i].lease_thumb+'" alt=""></div>';
			html+='<div class="supply_c">';
			html+='<h2>'+name+'</h2>';
			html+='<div class="Price"><i></i>租金：<b>￥<em>'+json[i].rent+'</em></b></div>';
			html+='<div class="goods_sn"><i></i>类别：'+json[i].cat_name+'</div>';
			html+='</div>';
			html+='<div class="eidt"><span data-href="index.php?a=edit&c=lease&id='+json[i].id+'"><i></i>编辑</span><span onclick="del('+json[i].id+')"><i></i>删除</span></div>';
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
		url: 'index.php?a=del&c=lease',
		type: 'POST',
		dataType: 'json',
		data: {id: id},
		})
		.done(function (o) {
			if (o.isok == 'true') {
				$('#showLoading').hide();
				Message.showNotify("" + o.data + "", 1000);
				$('#lease_' + id).remove();
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