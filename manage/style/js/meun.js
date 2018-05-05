var
	sf_layer=$('.sf_layer'),
	layer_bg=$('.layer_bg'),
	shopcat=$('#shopcat'),
	address=$('#address'),
	list=$('.mod_list');
var a='',c='',v='',shopcat='',regionstr='';
var re=/[>]/g;
$(function(){
	$('#shopcat,#address').on('click',function(){
		var title=$(this).siblings('span').text();
		var mod_list=$('.mod_list').html();
		 a=$(this).data('a');
		 c=$(this).data('c');
		if(mod_list){list.empty();}
		$('.tit').html(title.substring(1,title.length-1))
		layer_bg.addClass('show');
		sf_layer.addClass('show');
		meunlist();

		shopcat='';
		regionstr='';
	})
	$('.btn_gray').on('click',function(){
		$('.layer_bg').removeClass('show');
		$('.sf_layer').removeClass('show');
	})
	layer_bg.on('click',function(){
		$('.layer_bg').removeClass('show');
		$('.sf_layer').removeClass('show');
	})
	//店铺选择
$('#shop').on('click',function(){
	var title=$(this).siblings('span').text();
	var mod_list=$('.mod_list').html();
	a=$(this).data('a');
	c=$(this).data('c');
	if(mod_list){list.empty();}
	$('.tit').html(title.substring(0,title.length-1));
	layer_bg.addClass('show');
	sf_layer.addClass('show');
	$('.btn_primary').removeClass('hide');
	shoplist();

});
//异地发布区域选择
	$('#remote').on('click',function(){
		var title=$(this).siblings('span').text();
		var mod_list=$('.mod_list').html();
		a=$(this).data('a');
		c=$(this).data('c');
		if(mod_list){list.empty();}
		$('.tit').html(title.substring(0,title.length-1));
		layer_bg.addClass('show');
		sf_layer.addClass('show');
		remotelist();
		shopcat='';
		regionstr='';
	});
});
function meunlist(v){
	var url = 'index.php?a='+a+'&c='+c+'&t='+Math.random();
	$.ajax({
		url:url,
		type:'GET',
		dataType:'json',
		data:{fid:v},
		})
		.done(function(o) {
			if(o.isok=='true'){
				shopListObj= o.data;
				regionListObj= o.datas;
				if(shopListObj){
					shopcatListDiv(shopListObj)
				}else{
					regionListDiv(regionListObj)
				}
			}
		})
		.fail(function() {
		})
		.always(function() {
		});
}

function shopcatListDiv(json){
	var html='';
	var mydiv=$('.mod_list');
	html+='<ul>';
	for(var i=0;i<json.length;i++){
		var onclick='',child='';
		if(json[i].child_count>0 ){
		    child='class="child"';
			onclick='onclick=meunlist('+json[i].id+')';
		}else{
			onclick='data-links='+json[i].child_count+'';
		}
		html+='<li '+onclick+' '+child+' data-value="'+json[i].id+'">'+json[i].cat_name+'</li>';
	}
	html+='</div>';
	mydiv.html(html);
	Choice();
}

function regionListDiv(json){
	var html='';
	var mydiv=$('.mod_list');
	html+='<ul>';
	for(var i=0;i<json.length;i++){
		var onclick='',child='';
		if(json[i].immchildnum>0){
			child='class="child"';
			onclick='onclick=meunlist('+json[i].id+')';
		}else{
			onclick='data-links='+json[i].immchildnum+'';
		}
		html+='<li '+onclick+' '+child+' data-value="'+json[i].id+'">'+json[i].name+'</li>';
	}
	html+='</div>';
	mydiv.html(html);
	region();
}

function remotelist(v){
	var url = 'index.php?a='+a+'&c='+c+'&t='+Math.random();

		$.ajax({
			url:url,
			type:'GET',
			dataType:'json',
			data:{'fid':v},
		})
		.done(function(o) {
			if(o.isok=='true'){
				RegionListObj= o.data;
				RegionListDiv(RegionListObj)
			}
		})
		.fail(function() {
		})
		.always(function() {
		});
}

function RegionListDiv(json){
	var html='';
	var mydiv=$('.mod_list');
	html+='<ul>';
	for(var i=0;i<json.length;i++){
		var onclick='',child='';
		if(json[i].level<=3){
			if(json[i].immchildnum>0 && json[i].level<=2){
				child='class="child"';
				onclick='onclick=remotelist('+json[i].id+')';
			}
		}
		html+='<li '+onclick+' '+child+' data-value="'+json[i].id+'">'+json[i].name+'</li>';
	}
	html+='</div>';
	mydiv.html(html);
	RegionChoice();
}

function RegionChoice(){
	$('.mod_list li').on('click',function(){
		var txt=$(this).text();
		var val=$(this).data('value');
		if(!shopcat){
			shopcat+=txt;
			regionstr+=val;
		}else {
			shopcat+='>'+txt;
			regionstr+=','+val;
		}
		$('#remote').html(shopcat);
		$('#regionstr').val(regionstr);
		if(re.test(shopcat)) {
			var n = shopcat.match(re).length;
			if (n==2) {
				layer_bg.removeClass('show');
				sf_layer.removeClass('show');
			}
		}
	})
}

function Choice(){
	$('.mod_list li').on('click',function(){
		var txt=$(this).text();
		var val=$(this).data('value');
		var links=$(this).data('links');
		if(!shopcat){
			shopcat+=txt;
		}else {
			shopcat+='>'+txt;
		}
		$('#shopcat_id').val(val);
		$('#shopcat').html(shopcat);
		if(links==0){
			layer_bg.removeClass('show');
			sf_layer.removeClass('show');

		}
	})
}
function region(){
	$('.mod_list li').on('click',function() {
		var txt = $(this).text();
		var val=$(this).data('value');
		var links=$(this).data('links');
		if (!shopcat) {
			regionstr+=val;
			shopcat += txt;
		} else {
			regionstr+=','+val;
			shopcat += '>' + txt;
		}
		if(re.test(shopcat)){
			var n=shopcat.match(re).length;
			if(n<=2){
				$('#address').html(shopcat);
				$('#town').empty();
				$('#road').val('');
			}else if(n<=3){
            $('#town').val(txt);
			}else{
				$('#road').val(txt);
			};
		}
		$('#regionstr').val(regionstr);
		if(links==0){
			layer_bg.removeClass('show');
			sf_layer.removeClass('show');

		}
	})
}

function shoplist(v){
	var url = 'index.php?a='+a+'&c='+c+'&t='+Math.random();
	$.ajax({
		url:url,
		type:'GET',
		dataType:'json',
		data:{'fid':v},
	})
	.done(function(o) {
		if(o.isok=='true'){
			shopListObj= o.data;
			shopListDiv(shopListObj)
		};
	})
	.fail(function() {
	})
	.always(function() {
	});
};

function shopListDiv(json){
	var html='';
	var mydiv=$('.mod_list');
	html+='<ul>';
	for(var i=0;i<json.length;i++){
		var curr='';
		$('.shoplin').each(function(){
			var shopid=$(this).val();
			if(shopid==''+json[i].id+''){
				curr='class="curr"';
			};
		});
		html+='<li  data-value="'+json[i].id+'" '+curr+'>'+json[i].name+'</li>';
	};
	html+='</div>';
	mydiv.html(html);
	$('.mod_list li').on('click',function() {
		var val = $(this).data('value');
		var hasClass=$(this).hasClass('curr');
		if(hasClass){
			$('input[value="'+val+'"]').remove();
			$(this).removeClass('curr');
		}else{
			$(this).addClass('curr');
			$('.submitbtn').before('<input type="hidden" value="'+val+'" class="shoplin">');
		};

	});

$('.btn_primary').on('click',function(){
	var shoptxt='',shopval='';
	$('.mod_list li').each(function(){
		var hasClass=$(this).hasClass('curr');
		if(hasClass){
			var txt = $(this).text();
			var val1 = $(this).data('value');
			if(!shoptxt){
				shoptxt+=txt;
				shopval+=val1
			}else {
				shoptxt+='>'+txt;
				shopval+=','+val1;
			};
		};
		$('#shop').html(shoptxt);
		$('#shop_id').val(shopval);
	})
	$('.layer_bg').removeClass('show');
	$('.sf_layer').removeClass('show');
});

};

$(function(){
	$('#contenteditable').blur(function(){
		var html=$(this).html();
		$('#brief').val(html);
	});
});


