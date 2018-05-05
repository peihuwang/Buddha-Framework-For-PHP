var recruit={
	add:function(){
		var recruit_name = $('input[name="recruit_name"]').val(),
				recruit_id = $('input[name="recruit_id"]').val(),
				pay = $('input[name="pay"]').val(),
				shop_id = $('select[name="shop_id"]').find('option:selected').val();

				if (!recruit_name) {
					Message.showMessage("招聘名称不能为空");
					return false;
				};
				if (!recruit_id) {
					Message.showMessage("招聘分类不能为空");
					return false;
				};
				if (!shop_id) {
					Message.showMessage("选择招聘发布店铺");
					return false;
				};
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
		$("#recruitForm").ajaxSubmit(options);
		return false;//防止刷新提交
	},
	edit:function(){
	var recruit_name = $('input[name="recruit_name"]').val(),
			recruit_id = $('input[name="recruit_id"]').val(),
			pay = $('input[name="pay"]').val(),
			shop_id = $('select[name="shop_id"]').find('option:selected').val();

	if (!recruit_name) {
		Message.showMessage("招聘名称不能为空");
		return false;
	};
	if (!recruit_id) {
		Message.showMessage("招聘分类不能为空");
		return false;
	};
	if (!shop_id) {
		Message.showMessage("选择招聘发布店铺");
		return false;
	};
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
	$("#recruitForm").ajaxSubmit(options);
	return false;//防止刷新提交
},
}






$.fn.recruitajax = function(url){
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
		var html = '',pay='';
		for (var i = 0; i< json.length; i++) {
			var name='';
			if(json[i].recruit_name.length>20){
				name=json[i].recruit_name.substring(0,23)+"...";
			}else{
				name= ''+json[i].recruit_name+'';
			}
			if(json[i].pay==0){
				pay='<b>面议</b>';
			}else{
				if((json[i].pay==0) || (json[i].pay=='') ||  (json[i].pay == '面议')){
                    pay='<b><em>面议</em></b>';
				}else{
                    pay='<b><em>'+json[i].pay+'</em></b>元/月';
				}

			}
			html+='<div class="supplyitem" id="recruit_'+json[i].id+'" >';
			html+='<div class="supply_c" style="margin-left:5px">';
            // html+='<h2>'+json[i].name+'</h2>';
			html+='<h2>'+json[i].recruit_name;
            html+='&nbsp;&nbsp;<b style="color: #0000CC;">状态：</b>';
            if(json[i].is_sure == "not" ){
                html+='未审核';
            }else if(json[i].is_sure == "no" ){
                html+='未通过';
            }else if (json[i].is_sure == "yes") {
                html+='已通过';
            }
            html+='</h2>';
			html+='<div class="Price"><i></i>薪酬：'+pay+'</div>';
			html+='<div class="goods_sn"><i></i>类别：'+json[i].cat_name+'</div>';
			html+='</div>';
            if (json[i].is_sure == 4) {
                html += '<div class="eidt"><span data-href="index.php?a=edit&c=recruit&id=' + json[i].id + '"><i></i>编辑</span><span onclick="fail(' + json[i].id + ')" style="text-align: center; line-height: 30px">审核失败</span></div>';
            }else {
                html += '<div class="eidt"><span data-href="index.php?a=edit&c=recruit&id=' + json[i].id + '"><i></i>编辑</span><span onclick="del(' + json[i].id + ')"><i></i>删除</span></div>';
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
		url: 'index.php?a=del&c=recruit',
		type: 'POST',
		dataType: 'json',
		data: {id: id},
		})
		.done(function (o) {
			if (o.isok == 'true') {
				$('#showLoading').hide();
				Message.showNotify("" + o.data + "", 1000);
				$('#recruit_' + id).remove();
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
    $.get('index.php?a=fail&c=recruit',{id:id},function (re) {
        if(re.isok==0){
            Message.showMessage(''+re.remarks+'');
        }else {
            Message.showMessage(''+re.data+'');
        }
    })
}