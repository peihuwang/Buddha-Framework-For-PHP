TouchSlide({ slideCell:"#leftTabBox" });
TouchSlide({ slideCell:"#leftTabhot" });

$(function () {
	$('#agenttel').find('span').on('click',function () {
		$('.tel').toggle(300)
    });

    if(getCookie("sName")!="" && getCookie("sName")!=undefined) {
        var position=getCookie("sName");
           position=JSON.parse(position);
        if(position.district){
            $('.nav-city').html(position.district+"<space></space><i class='text-icon icon-downarrow'></i>");
        }else {
            $('.nav-city').html(position.city+"<space></space><i class='text-icon icon-downarrow'></i>");
        }
        $.get("/ajax/?identify=mobile_home",{number:position.adcode,lat:position.lat,lng:position.lng},function(result){
            index(result);
        });
    }else {
        var geolocation = new qq.maps.Geolocation("HM5BZ-ICHKU-VDNVI-27RMJ-YZRCQ-P5BTP", "myapp");
        var options = {timeout: 9000};
        geolocation.getLocation(showPosition, showErr,options);
    };
});
function showPosition(position) {
    var str = JSON.stringify(position);
    if(position.district){
        $('.nav-city').html(position.district+"<space></space><i class='text-icon icon-downarrow'></i>");
    }else {
        $('.nav-city').html(position.city+"<space></space><i class='text-icon icon-downarrow'></i>");
    }
    setCookie('sName',str);
    $('#index_meun,.meun').find('a').attr('data-id',position.adcode);
    $('#index_meun,.meun').find('a').on('click',function(){
        var data=$(this).attr('data-id');
        var url=$(this).attr('href');
        if(url.indexOf("number")<0){
            $(this).attr('href',url+'&number='+data);
        }
    });
    $.get("/ajax/?identify=mobile_home",{number:position.adcode,lat:position.lat,lng:position.lng},function(result){
        index(result);
    });

};


function showErr() {
	Message.showConfirm("定位当前位置失败!是否重新定位", "确定", "取消", function () {
		window.location.reload();
	},function(){
		Message.showNotify("你取消了定位!", 500);
	});
};

function index(result){
	if(result.shopRec){
	$.each(result.shopRec ,function(i,n){
		$('#shop_Rec').append('<div class="nearby" data-href="index.php?a=index&c=shop&id='+ n.id+'"><div class="pic_img"><img src="/'+n.small+'"></div><div class="nearby_con"><div class="title">'+ n.name+'</div><p>'+ n.brief+'</p><div class="address"><span class="strong"><i></i>约'+ n.distance+'</span><span>'+ n.roadfullname+'</span></div> </div>');
	});
        $('#shop_Rec').append('<div class="bntlink" data-href="index.php?a=shop&c=list&type=is_rec">更多</div>');
		link();
	}else{
		$('#shop_Rec').append('<li>暂无信息</li>');
	}
	if(result.shopNws){
		$.each(result.shopNws ,function(i,n){
			$('#shop_nws').append('<div class="nearby" data-href="index.php?a=index&c=shop&id='+ n.id+'"><div class="pic_img"><img src="/'+n.small+'"></div><div class="nearby_con"><div class="title">'+ n.name+'</div><p>'+ n.brief+'</p><div class="address"><span class="strong"><i></i>约'+ n.distance+'</span><span>'+ n.roadfullname+'</span></div> </div>');
		});
        $('#shop_nws').append('<div class="bntlink" data-href="index.php?a=shop&c=list&type=is_nws">更多</div>');
		link();
	}else{
		$('#shop_nws').append('<li>暂无信息</li>');
	}
	if(result.shopPro){
		$.each(result.shopPro ,function(i,n){
			$('#shop_Pro').append('<div class="nearby" data-href="index.php?a=index&c=shop&id='+ n.id+'"><div class="pic_img"><img src="/'+n.small+'"></div><div class="nearby_con"><div class="title">'+ n.name+'</div><p>'+ n.brief+'</p><div class="address"><span class="strong"><i></i>约'+ n.distance+'</span><span>'+ n.roadfullname+'</span></div> </div>');
		});
        $('#shop_Pro').append('<div class="bntlink" data-href="index.php?a=shop&c=list&type=is_promotion">更多</div>');
		link();
	}else{
		$('#shop_Pro').append('<li>暂无信息</li>');
	}
	if(result.shopHot){
		$.each(result.shopHot ,function(i,n){
			$('#shop_Hot').append('<div class="nearby" data-href="index.php?a=index&c=shop&id='+ n.id+'"><div class="pic_img"><img src="/'+n.small+'"></div><div class="nearby_con"><div class="title">'+ n.name+'</div><p>'+ n.brief+'</p><div class="address"><span class="strong"><i></i>约'+ n.distance+'</span><span>'+ n.roadfullname+'</span></div> </div>');
		});
        $('#shop_Hot').append('<div class="bntlink" data-href="index.php?a=shop&c=list&type=is_hot">更多</div>');
		link();
	}else{
		$('#shop_Hot').append('<li>暂无信息</li>');
	}

	//最新供应
	if(result.goodsNws){
		$.each(result.goodsNws ,function(i,n){
			$('#goods_nws').append('<div class="sup_item" data-href="index.php?a=info&c=supply&id='+ n.id+'">'
					+'<div class="pic_img"><img src="/'+ n.goods_thumb+'"></div>'
					+'<div class="nearby_con">'
					+'<div class="title">'+ n.name+'</div>'
					+'<div class="price"><i></i>价格：<em>￥<i>'+ n.price+'</i></em></div>'
					+'<div class="address"><span class="strong"><i></i>'+ n.shop_name+'</span><span>'+ n.roadfullname+'</span></div>'
					+'</div>'
					+'</div>');
		});
        $('#goods_nws').append('<div class="bntlink" data-href="index.php?a=index&c=supply">更多</div>');
		link();
	}else{
		$('#goods_nws').append('<li>暂无信息</li>');
	};

	//最新招聘
	if(result.RegioNws){
		$.each(result.RegioNws ,function(i,n){
			var res='';
			    res+='<div class="sup_item" data-href="index.php?a=info&c=recruit&id='+ n.id+'">';
			    res+='<div class="nearby_con" style="margin: 0">';
			    res+='<div class="title">'+ n.name+'</div>';
				if(n.pay==0){
					res+='<div class="price"><i></i>薪酬：<em>面议</em></div>';
				}else{
					res+='<div class="price"><i></i>薪酬：<em>￥<i>'+ n.pay+'</i></em></div>';
				}
				res+='<div class="address"><span class="strong"><i></i>'+ n.shop_name+'</span><span>'+ n.roadfullname+'</span></div>';
			    res+='</div>';
			    res+='</div>';
			$('#Regio_Nws').append(res);
		});
        $('#Regio_Nws').append('<div class="bntlink" data-href="index.php?a=index&c=recruit">更多</div>');
		link();
	}else{
		$('#Regio_Nws').append('<li>暂无信息</li>');
	};
    //最新需求
	if(result.DemandNws){
		$.each(result.DemandNws ,function(i,n){
			$('#Demand_Nws').append('<div class="sup_item" data-href="index.php?a=info&c=demand&id='+ n.id+'">'
					+'<div class="pic_img"><img src="/'+ n.demand_thumb+'"></div>'
					+'<div class="nearby_con">'
					+'<div class="title">'+ n.name+'</div>'
					+'<div class="price"><i></i>预算：<em>￥<i>'+ n.budget+'</i></em></div>'
					+'<div class="address"><span class="strong"><i></i>'+ n.shop_name+'</span><span>'+ n.roadfullname+'</span></div>'
					+'</div>'
					+'</div>');
		});
        $('#Demand_Nws').append('<div class="bntlink" data-href="index.php?a=index&c=demand">更多</div>');
		link();
	}else{
		$('#Demand_Nws').append('<li>暂无信息</li>');
	};
	//最新租赁
	if(result.leaseNws){
		$.each(result.leaseNws ,function(i,n){
			$('#lease_Nws').append('<div class="sup_item" data-href="index.php?a=info&c=lease&id='+ n.id+'">'
					+'<div class="pic_img"><img src="/'+ n.lease_thumb+'"></div>'
					+'<div class="nearby_con">'
					+'<div class="title">'+ n.name+'</div>'
					+'<div class="price"><i></i>租金：<em>￥<i>'+ n.rent+'</i></em></div>'
					+'<div class="address"><span class="strong"><i></i>'+ n.shop_name+'</span><span>'+ n.roadfullname+'</span></div>'
					+'</div>'
					+'</div>');
		});
        $('#lease_Nws').append('<div class="bntlink" data-href="index.php?a=index&c=lease">更多</div>');
		link()
	}else{
		$('#lease_Nws').append('<li>暂无信息</li>');
	};

	$.each(result.guanggao,function(index,item){
		var html='';
		html+='<ul>';
		$.each(item.image,function(i,n){
			html+='<li><img src="'+ n.large+'" alt=""></li>';
		});
		html+='</ul>';
		$('#'+item.name).html(html).parent().show();
	});
	TouchSlide({
		slideCell :"#focus",
		titCell : ".hd ul", // 开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
		mainCell : ".bd ul",
		effect : "leftLoop",
		autoPlay : true, // 自动播放
		autoPage : true, // 自动分页
		delayTime: 200, // 毫秒；切换效果持续时间（执行一次效果用多少毫秒）
		interTime: 2500, // 毫秒；自动运行间隔（隔多少毫秒后执行下一个效果）
		//switchLoad : "_src" // 切换加载，真实图片路径为"_src"
	});
	TouchSlide({
		slideCell :"#focus1",
		titCell : ".hd ul", // 开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
		mainCell : ".bd ul",
		effect : "leftLoop",
		autoPlay : true, // 自动播放
		autoPage : true, // 自动分页
		delayTime: 200, // 毫秒；切换效果持续时间（执行一次效果用多少毫秒）
		interTime: 2500, // 毫秒；自动运行间隔（隔多少毫秒后执行下一个效果）
		//switchLoad : "_src" // 切换加载，真实图片路径为"_src"
	});
	TouchSlide({
		slideCell :"#focus2",
		titCell : ".hd ul", // 开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
		mainCell : ".bd ul",
		effect : "leftLoop",
		autoPlay : true, // 自动播放
		autoPage : true, // 自动分页
		delayTime: 200, // 毫秒；切换效果持续时间（执行一次效果用多少毫秒）
		interTime: 2500, // 毫秒；自动运行间隔（隔多少毫秒后执行下一个效果）
		//switchLoad : "_src" // 切换加载，真实图片路径为"_src"
	});

};


function link(){
	$('[data-href]').on('click',function(){
		var url=$(this).attr('data-href');
		loadurl(url);//加载跳转链接
		return false;
	});
};

function goPAGE(){
    if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
        // window.location.href="/";手机跳转地址
    }else {
        window.location.href="/pc";
    };
};
goPAGE();
