/*
 * artDialog basic
 * Date: 2011-07-30 14:29
 * http://code.google.com/p/artdialog/
 * (c) 2009-2010 TangBin, http://www.planeArt.cn
 *
 * This is licensed under the GNU LGPL, version 2.1 or later.
 * For details, see: http://creativecommons.org/licenses/LGPL/2.1/
 */
 
;(function (window, undefined) {
if (window.jQuery) return jQuery;

var $ = window.art = function (selector, content) {
    	return new $.fn.init(selector, content);
	},
	quickExpr = /^(?:[^<]*(<[\w\W]+>)[^>]*$|#([\w\-]+)$)/,
	rclass = /[\n\t]/g;

if (window.$ === undefined) window.$ = $;
$.fn = $.prototype = {
	constructor: $,

    /**
	 * 判断样式类是否存在
	 * @param	{String}	名称
	 * @return	{Boolean}
	 */
    hasClass: function (name) {		
		var className = ' ' + name + ' ';
		if ((' ' + this[0].className + ' ').replace(rclass, ' ').indexOf(className) > -1) return true;

		return false;
    },

    /**
	 * 添加样式类
	 * @param	{String}	名称
	 */
    addClass: function (name) {
        if (!this.hasClass(name)) this[0].className += ' ' + name;

        return this;
    },

    /**
	 * 移除样式类
	 * @param	{String}	名称
	 */
    removeClass: function (name) {
        var elem = this[0];

        if (!name) {
            elem.className = '';
        } else
		if (this.hasClass(name)) {
            elem.className = elem.className.replace(name, ' ');
        };

        return this;
    },

    /**
	 * 读写样式<br />
     * css(name) 访问第一个匹配元素的样式属性<br />
     * css(properties) 把一个"名/值对"对象设置为所有匹配元素的样式属性<br />
     * css(name, value) 在所有匹配的元素中，设置一个样式属性的值<br />
	 */
    css: function (name, value) {
        var i, elem = this[0], obj = arguments[0];

        if (typeof name === 'string') {
            if (value === undefined) {
                return $.css(elem, name);
            } else {
                elem.style[name] = value;
            };
        } else {
            for (i in obj) {
				elem.style[i] = obj[i];
			};
        };

        return this;
    },
	
	/** 显示元素 */
	show: function () {
		return this.css('display', 'block');
	},
	
	/** 隐藏元素 */
	hide: function () {
		return this.css('display', 'none');
	},

    /**
	 * 获取相对文档的坐标
	 * @return	{Object}	返回left、top的数值
	 */
    offset: function () {
        var elem = this[0],
            box = elem.getBoundingClientRect(),
            doc = elem.ownerDocument,
            body = doc.body,
            docElem = doc.documentElement,
            clientTop = docElem.clientTop || body.clientTop || 0,
            clientLeft = docElem.clientLeft || body.clientLeft || 0,
            top = box.top + (self.pageYOffset || docElem.scrollTop) - clientTop,
            left = box.left + (self.pageXOffset || docElem.scrollLeft) - clientLeft;

        return {
            left: left,
            top: top
        };
    },
	
	/**
	 * 读写HTML - (不支持文本框)
	 * @param		{String}	内容
	 */
	html: function (content) {
		if (content === undefined) return this[0].innerHTML;
		this[0].innerHTML = content;
		
		return this;
	}
};

$.fn.init = function (selector, content) {
	var match, elem;
	content = content || document;
	
	if (!selector) return this;
	
	if (selector.nodeType) {
		this[0] = selector;
		return this;
	};
		
	if (typeof selector === 'string') {
		match = quickExpr.exec(selector);

		if (match && match[2]) {
			elem = content.getElementById(match[2]);
			if (elem && elem.parentNode) this[0] = elem;
			return this;
		};
	};
	
	this[0] = selector;
	return this;
};
$.fn.init.prototype = $.fn;

/** 空函数 */
$.noop = function () {};

/** 检测window */
$.isWindow = function (obj) {
	return obj && typeof obj === 'object' && 'setInterval' in obj;
};

/** 数组判定 */
$.isArray = function (obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
};

/**
 * 搜索子元素
 * 注意：只支持nodeName或.className的形式，并且只返回第一个元素
 * @param	{String}
 */
$.fn.find = function (expr) {
	var value, elem = this[0],
		className = expr.split('.')[1];

	if (className) {
		if (document.getElementsByClassName) {
			value = elem.getElementsByClassName(className);
		} else {
			value = getElementsByClassName(className, elem);
		};
	} else {
		value = elem.getElementsByTagName(expr);
	};
	
	return $(value[0]);
};
function getElementsByClassName (className, node, tag) {
	node = node || document;
	tag = tag || '*';
	var i = 0,
		j = 0,
		classElements = [],
		els = node.getElementsByTagName(tag),
		elsLen = els.length,
		pattern = new RegExp("(^|\\s)" + className + "(\\s|$)");
		
	for (; i < elsLen; i ++) {
		if (pattern.test(els[i].className)) {
			classElements[j] = els[i];
			j ++;
		};
	};
	return classElements;
};

/**
 * 遍历
 * @param {Object}
 * @param {Function}
 */
$.each = function (obj, callback) {
    var name, i = 0,
        length = obj.length,
        isObj = length === undefined;

    if (isObj) {
        for (name in obj) {
            if (callback.call(obj[name], name, obj[name]) === false) break;
        };
    } else {
        for (var value = obj[0]; i < length && callback.call(value, i, value) !== false; value = obj[++i]) {};
    };
	
	return obj;
};

/**
 * 移除节点
 */
$.fn.remove = function () {
	var elem = this[0];

	while (elem.firstChild) {
		$.event.remove(elem.firstChild);
		$.removeData(elem.firstChild);
		elem.removeChild(elem.firstChild);
	};

	$.event.remove(elem);
	$.removeData(elem);
	elem.parentNode.removeChild(elem);
	'CollectGarbage' in window && setTimeout(CollectGarbage, 1);
	return this;
};

/**
 * 写入数据缓存
 * @param	{String}	名称
 * @param	{Object}	数据
 */
$.fn.data = function (name, data) {
	var ret = $.data(this[0], name, data);
	if (data !== undefined) return ret; 
	return this;
};

/**
 * 删除数据缓存
 * @param	{String}	名称
 * @param	{Object}	数据
 */
$.fn.removeData = function (name) {
	$.removeData(this[0], name);
	return this;
};

$.uuid = 0;
$.cache = {};
$.expando = '@cache' + (new Date).getTime();

$.data = function (elem, name, data) {
	var cache = $.cache,
		id = $._getUid(elem);
	
	if (!cache[id]) cache[id] = {};
	if (data !== undefined) cache[id][name] = data;
	
	return cache[id][name];
};

$._getUid = function (elem) {
	var expando = $.expando,
		id = elem === window ? 0 : elem[expando];
	if (id === undefined) elem[expando] = id = ++ $.uuid;
	return id;
};

$.removeData = function (elem, name) {
	var expando = $.expando,
		cache = $.cache,
		id = $._getUid(elem),
		thisCache = id && cache[id];

	if (!thisCache) return;
	if (name) return delete thisCache[name];
	
	delete cache[id];
	if (elem.removeAttribute) {
		elem.removeAttribute(expando);
	} else {
		elem[expando] = null;
	};
};

/**
 * 事件绑定
 * @param	{String}	类型
 * @param	{Function}	要绑定的函数
 */
$.fn.bind = function (type, callback) {
	$.event.add(this[0], type, callback);
	return this;
};

/**
 * 移除事件
 * @param	{String}	类型
 * @param	{Function}	要卸载的函数
 */
$.fn.unbind = function(type, callback) {
	$.event.remove(this[0], type, callback);
	return this;
};

// 事件机制
$.event = {
	
	// 添加
	add: function (elem, type, callback) {
		var types, listeners,
			data = $.data(elem, '@events') || $.data(elem, '@events', {}),
			ecma = 'addEventListener' in elem,
			add = ecma ? 'addEventListener' : 'attachEvent';
		
		types = data[type] = data[type] || {};
		listeners = types.listeners = types.listeners || [];
		listeners.push(callback);
		
		if (!types.handler) {
			types.elem = elem;
			types.handler = this.handler($._getUid(elem));
			
			type = ecma ? type : 'on' + type;
			elem[add](type, types.handler, false);
		};
	},
	
	// 卸载
	remove: function (elem, type, callback) {
		var i, types, listeners,
			data = $.data(elem, '@events'),
			ecma = 'removeEventListener' in elem,
			remove = ecma ? 'removeEventListener' : 'detachEvent';
		
		if (!data) return;
		if (type === undefined) {
			for (i in data) this.remove(elem, i);
			//return;
		};
		
		types = data[type];
		if (!types) return;
		
		listeners = types.listeners;
		if (callback === undefined) {
			types.listeners = [];
		} else {
			for (i = 0; i < listeners.length; i ++) {
				listeners[i] === callback && listeners.splice(i--, 1);
			};
		};
		
		if (listeners.length === 0) {
			delete data[type];
			type = ecma ? type : 'on' + type;
			elem[remove](type, types.handler, false);
		};
	},
	
	handler: function (id) {
		return function (event) {
			event = $.event.fix(event || window.event);
			var cache = $.cache[id]['@events'][event.type];
			for (var i = 0, fn; fn = cache.listeners[i++];) {
				if (fn.call(cache.elem, event) === false) {
					event.preventDefault();
					event.stopPropagation();
				};
			};
		};
	},
	
	// 兼容处理
	fix: function (e) {
		if (e.target) return e;
		var event = {
			target: e.srcElement || document,
			preventDefault: function () {e.returnValue = false},
			stopPropagation: function () {e.cancelBubble = true}
		};
		for (var i in e) event[i] = e[i];
		
		return event;
	}
};

// 获取css
$.css = 'defaultView' in document && 'getComputedStyle' in document.defaultView ?
	function (elem, name) {
		return document.defaultView.getComputedStyle(elem, false)[name]
} :
	function (elem, name) {
		return elem.currentStyle[name] || '';
};

/**
 * 获取滚动条位置 - [不支持写入]
 * $.fn.scrollLeft, $.fn.scrollTop
 * @example		获取文档垂直滚动条：$(document).scrollTop()
 * @return		{Number}	返回滚动条位置
 */
$.each(['Left', 'Top'], function (i, name) {
    var method = 'scroll' + name;

    $.fn[method] = function (val) {
        var elem = this[0], win;

		win = getWindow(elem);
		return win ?
			('pageXOffset' in win) ?
				win[i ? 'pageYOffset' : 'pageXOffset'] :
				win.document.documentElement[method] || win.document.body[method] :
			elem[method];
    };
});

function getWindow (elem) {
	return $.isWindow(elem) ?
		elem :
		elem.nodeType === 9 ?
			elem.defaultView || elem.parentWindow :
			false;
};

/**
 * 获取窗口或文档尺寸 - [只支持window与document读取]
 * @example 
   获取文档宽度：$(document).width()
   获取可视范围：$(window).width()
 * @return	{Number}
 */
$.each(['Height', 'Width'], function (i, name) {
    var type = name.toLowerCase();

    $.fn[type] = function (size) {
        var elem = this[0];
        if (!elem) {
            return size == null ? null : this;
        };

		return $.isWindow(elem) ?
			elem.document.documentElement['client' + name] || elem.document.body['client' + name] :
			(elem.nodeType === 9) ?
				Math.max(
					elem.documentElement['client' + name],
					elem.body['scroll' + name], elem.documentElement['scroll' + name],
					elem.body['offset' + name], elem.documentElement['offset' + name]
				) : null;
    };

});

//-------------end
return $}(window));







/*!
	对话框主程序			
------------------------------------------------------------------*/
;(function ($, window, undefined) {

var _box, _tmplEngine,
	_count = 0,
	_$window = $(window),
	_$document = $(document),
	_elem = document.documentElement,
	_isIE6 = !-[1,] && !('minWidth' in _elem.style),
	_isMobile = 'ontouchend' in _elem && !('onmousemove' in _elem)
		|| /(iPhone|iPad|iPod)/i.test(navigator.userAgent),
	_isFixed = !_isIE6 && !_isMobile,
	_eventDown = _isMobile ? 'touchstart' : 'mousedown',
	_expando = 'artDialog' + (new Date).getTime();

var artDialog = function (config, yesFn, noFn) {
	config = config || {};
	if (typeof config === 'string' || config.nodeType === 1) {
		config = {content: config, fixed: !_isMobile};
	};
	
	var api, buttons = [],
		defaults = artDialog.defaults,
		elem = config.follow = this.nodeType === 1 && this || config.follow;
		
	// 合并默认配置
	for (var i in defaults) {
		if (config[i] === undefined) config[i] = defaults[i];		
	};
	
	// 返回跟随模式或重复定义的ID
	if (typeof elem === 'string') elem = $(elem)[0];
	config.id = elem && elem[_expando + 'follow'] || config.id || _expando + (_count ++);
	api = artDialog.list[config.id];
	if (elem && api) return api.follow(elem).zIndex().focus();
	if (api) return api.zIndex();
	
	// 目前主流移动设备对fixed支持不好
	if (_isMobile) config.fixed =  false;
	
	// 按钮队列
	if (!$.isArray(config.button)) {
		config.button = config.button ? [config.button] : [];
	};
	yesFn = yesFn || config.yesFn;
	noFn = noFn || config.noFn;
	yesFn && config.button.push({
		name: config.yesText,
		callback: yesFn,
		focus: true
	});
	noFn && config.button.push({
		name: config.noText,
		callback: noFn
	});
	
	// zIndex全局配置
	artDialog.defaults.zIndex = config.zIndex;
	
	return artDialog.list[config.id] = _box ?
		_box._init(config, true) : new artDialog.fn._init(config);
};

artDialog.fn = artDialog.prototype = {
	
	_init: function (config, isReset) {
		var that = this;
		that.config = config;
		that._isClose = false;
		that._listeners = {};
		that._minWidth = config.minWidth;
		that._minHeight = config.minHeight;
		
		if (!isReset) {
			that._wrap = document.createElement('div');
			that.DOM = {
				wrap: $(that._wrap)
			};
			that._outerTmpl();
		};
		
		that._wrap.style.cssText = 'position:'
		+ (config.fixed ? 'fixed' : 'absolute')
		+ ';left:0;top:0';
		that._wrap.className = config.skin;
		
		that._innerTmpl();
		isReset ? _box = null : that._eventProxy();
		that.size(config.width, config.height);
		config.follow ? that.follow(config.follow) : that.position();
		config.focus && that.focus();
		config.lock && that.lock();
		that.zIndex(config.zIndex).time(config.time);
		!config.show && that.hide();
		config.initFn && config.initFn.call(that, window);
		
		return that;
	},
	
	/**
	 * 设置内容
	 * @param	{String, HTMLElement, Object}	内容 (可选)
	 * @return	{this, HTMLElement}				如果无参数则返回内容容器DOM对象
	 */
	content: function (msg) {
		var prev, next, parent, display,
			that = this,
			content = that.DOM.content,
			elem = content[0];
		
		that._elemBack = null;

		if (!msg && msg !== 0) {
			return elem;
		} else if (typeof msg === 'string') {
			content.html(msg);
		} else if (msg.nodeType === 1) {
		
			// 让传入的元素在对话框关闭后可以返回到原来的地方
			display = msg.style.display;
			prev = msg.previousSibling;
			next = msg.nextSibling;
			parent = msg.parentNode;
			that._elemBack = function () {
				if (prev && prev.parentNode) {
					prev.parentNode.insertBefore(msg, prev.nextSibling);
				} else if (next && prev.parentNode) {
					next.parentNode.insertBefore(msg, next);
				} else if (parent) {
					parent.appendChild(msg);
				};
				msg.style.display = display;
			};
			
			content.html('');
			elem.appendChild(msg);
			msg.style.display = 'block';
			
		};
		
		return that.position();
	},
	
	/**
	 * 设置标题
	 * @param	{String}			标题内容
	 * @return	{this, HTMLElement}	如果无参数则返回内容器DOM对象
	 */
	title: function (text) {
		var DOM = this.DOM,
			titleWrap = DOM.titleWrap[0],
			title = DOM.title;
		if (text === undefined) {
			return title[0];
		} else {
			title.html(text);
		};
		titleWrap.style.display = 'block';
		return this;
	},
	

	/* 位置居中 */
	position: function () {
		var that = this,
			wrap = that.DOM.wrap[0],
			fixed = that.config.fixed,
			dl = fixed ? 0 : _$document.scrollLeft(),
			dt = fixed ? 0 : _$document.scrollTop(),
			ww = _$window.width(),
			wh = _$window.height(),
			ow = wrap.offsetWidth,
			oh = wrap.offsetHeight,
			style = wrap.style;
			
		// 水平居中
		var left = (ww - ow) / 2 + dl;
		
		// 黄金比例垂直居中
		var top = Math.max(Math.min((oh < 4 * wh / 7
		? wh * 0.382 - oh / 2
		: (wh - oh) / 2) + dt, wh - oh + dt), dt);

		style.left = left + 'px';
		style.top = top + 'px';
		
		/*that.config.follow = null;*/
		return that;
	},
	
	/**
	 *	尺寸
	 *	@param	{Number, String}	宽度
	 *	@param	{Number, String}	高度
	 */
	size: function (width, height) {
		var style = this.DOM.main[0].style;
		
		if (typeof width === 'number') width = width + 'px';
		if (typeof height === 'number') height = height + 'px';
			
		style.width = width;
		style.height = height;
		
		return this;
	},
	
	/**
	 * 跟随元素
	 * @param	{HTMLElement}
	 */
	follow: function (elem) {
		var $elem, that = this;

		if (typeof elem === 'string' || elem && elem.nodeType === 1) {
			$elem = $(elem);
		};
		if (!$elem || $elem.css('display') === 'none') {
			return that.position(that.config.left, that.config.top);
		};
		
		var fixed = that.config.fixed,
			winWidth = _$window.width(),
			winHeight = _$window.height(),
			docLeft =  _$document.scrollLeft(),
			docTop = _$document.scrollTop(),
			offset = $elem.offset(),
			width = $elem[0].offsetWidth,
			height = $elem[0].offsetHeight,
			left = fixed ? offset.left - docLeft : offset.left,
			top = fixed ? offset.top - docTop : offset.top,
			wrap = that.DOM.wrap[0],
			style = wrap.style,
			wrapWidth = wrap.offsetWidth,
			wrapHeight = wrap.offsetHeight,
			setLeft = left - (wrapWidth - width) / 2,
			setTop = top + height,
			dl = fixed ? 0 : docLeft,
			dt = fixed ? 0 : docTop;
			
		setLeft = setLeft < dl ? left :
		(setLeft + wrapWidth > winWidth) && (left - wrapWidth > dl)
		? left - wrapWidth + width
		: setLeft;

		setTop = (setTop + wrapHeight > winHeight + dt)
		&& (top - wrapHeight > dt)
		? top - wrapHeight
		: setTop;
		
		style.left = setLeft + 'px';
		style.top = setTop + 'px';
		
		that.config.follow = elem;
		$elem[0][_expando + 'follow'] = that.config.id;
		return that;
	},
	
	/**
	 * 自定义按钮
	 * @example
				 button({
					name: 'login',
					callback: function () {},
					disabled: false,
					focus: true
				}, .., ..)
	 */
	button: function () {
		var that = this,
			ags = arguments,
			elem = that.DOM.buttons[0],
			list = $.isArray(ags[0]) ? ags[0] : [].slice.call(ags);
		
		if (!list.length) {
			elem.style.display = 'none';
			return that;
		};
		
		$.each(list, function (i, val) {
			var name = val.name,
				listeners = that._listeners,
				strongButton = 'aui_state_highlight',
				isNewButton = !listeners[name],
				button = !isNewButton ?
					listeners[name].elem :
					document.createElement('button');
					
			if (!listeners[name]) listeners[name] = {};
			if (val.callback) listeners[name].callback = val.callback;
			if (val.className) button.className = val.className;
			if (val.focus) {
				that._focus && that._focus.removeClass(strongButton);
				that._focus = $(button).addClass(strongButton);
				that.focus();
			};
			
			button[_expando + 'callback'] = name;
			button.disabled = !!val.disabled;

			if (isNewButton) {
				button.innerHTML = name;
				listeners[name].elem = button;
				elem.appendChild(button);
			};
		});
		
		elem.style.display = 'block';
		
		return that;
	},
	
	/** 显示对话框 */
	show: function () {
		this.DOM.wrap.show();
		this._lockMaskWrap && this._lockMaskWrap.show();
		return this;
	},
	
	/** 隐藏对话框 */
	hide: function () {
		this.DOM.wrap.hide();
		this._lockMaskWrap && this._lockMaskWrap.hide();
		return this;
	},
	
	/** 关闭对话框 */
	close: function () {
		var that = this,
			DOM = that.DOM,
			wrap = DOM.wrap,
			list = artDialog.list,
			fn = that.config.closeFn,
			follow = that.config.follow;
		
		if (that._isClose) return that;
		that.time();
		if (typeof fn === 'function' && fn.call(that) === false) {
			return that;
		};
		
		that.unlock();
		
		that._elemBack && that._elemBack();
		that._timer = that._focus = null;
		wrap[0].style.cssText = 'display:none';
		wrap[0].className = '';
		DOM.center.html('');
		
		if (artDialog.focus === that) artDialog.focus = null;
		if (follow) follow[_expando + 'follow'] = null;
		delete list[that.config.id];
		that._isClose = true;
		
		if (!_box) {
			_box = that;
		} else {
			that._uneventProxy();
			wrap.remove();
		};
		
		return that;
	},
	
	/**
	 * 定时关闭
	 * @param	{Number}	单位为秒, 无参数则停止计时器
	 */
	time: function (second) {
		var that = this,
			cancel = that.config.noText,
			timer = that._timer;
			
		timer && clearTimeout(timer);
		
		if (second) {
			that._timer = setTimeout(function(){
				that._trigger(cancel);
			}, 1000 * second);
		};
		
		return that;
	},
	
	/** 给按钮附加焦点 */
	focus: function () {
		var elemFocus, content,
			that = this,
			config = that.config,
			DOM = that.DOM;
			
		elemFocus = that._focus && that._focus[0] || DOM.close[0];
		
		try {
			elemFocus && elemFocus.focus();
		} catch (e) {};
		
		return that;
	},
	
	/** 置顶z-index */
	zIndex: function () {
		var that = this,
			wrap = that.DOM.wrap,
			index = artDialog.defaults.zIndex ++,
			focusElem = artDialog.focus;
			
		wrap.css('zIndex', index);
		that._lockMask && that._lockMask.css('zIndex', index - 1);
		
		// 设置最高层的样式
		if (focusElem) focusElem.DOM.wrap.removeClass('aui_state_focus');
		artDialog.focus = that;
		wrap.addClass('aui_state_focus');
		
		return that;
	},
	
	/** 设置屏锁 */
	lock: function () {
		if (this._lock) return this;
		
		var that = this,
			index = artDialog.defaults.zIndex += 2,
			wrap = that.DOM.wrap,
			config = that.config,
			opacity = 'filter:alpha(opacity=' + (config.opacity * 100) + ');opacity:' + config.opacity,
			docWidth = _$window.width(),
			docHeight = _$document.height(),
			lockMaskWrap = that._lockMaskWrap || $(document.body.appendChild(document.createElement('div'))),
			lockMask = that._lockMask || $(lockMaskWrap[0].appendChild(document.createElement('div'))),
			sizeCss = !_isFixed ? 'position:absolute;width:' + docWidth + 'px;height:' + docHeight
				+ 'px' : 'position:fixed;width:100%;height:100%';
		
		wrap.css('zIndex', index);
		
		lockMaskWrap[0].style.cssText = sizeCss + ';z-index:'
		+ (index - 1) + ';top:0;left:0;overflow:hidden;';
		
		lockMask[0].style.cssText = 'height:100%;background:'
		+ config.background + ';' + opacity;
			
		lockMask[0].ondblclick = function () {that.close()};
		
		that._lockMaskWrap = lockMaskWrap;
		that._lockMask = lockMask;
		
		that._lock = true;
		return that;
	},
	
	/** 解开屏锁 */
	unlock: function () {
		var that = this;
		
		if (!that._lock) return that;
		that._lockMask[0].ondblclick = null;
		that._lockMaskWrap[0].style.display = 'none';
		that._lock = false;
		if (_box) {
			that._lockMaskWrap.remove();
			that._lockMaskWrap = that._lockMask = null;
		};

		return that;
	},
	
	// 插入修饰结构 （只运行一次）
	_outerTmpl: function () {
		var that = this,
			wrap = that._wrap;
			
		wrap.innerHTML = _tmplEngine('outer', that.config);
		document.body.appendChild(wrap);
		
		that._getDOM(wrap);
	},
	
	// 插入内容区域 （可能运行多次）
	_innerTmpl: function () {
		var that = this,
			config = that.config,
			DOM = that.DOM,
			center = DOM.center;
		
		
		center.html(_tmplEngine('inner', config));
		that._getDOM(center[0]);
		
		that.button(config.button).content(config.content);
	},
	
	// 获取元素
	_getDOM: function (parent) {
		var i = 0,
			DOM = this.DOM,
			els = parent.getElementsByTagName('*'),
			elsLen = els.length;
			
		for (; i < elsLen; i ++) {
			DOM[els[i].className.split('aui_')[1]] = $(els[i]);
		};
	},
	
	// 按钮事件触发
	_trigger: function (id) {
		var that = this,
			fn = that._listeners[id] && that._listeners[id].callback;
		return typeof fn !== 'function' || fn.call(that) !== false ?
			that.close() : that;
	},
	
	// 事件代理
	_eventProxy: function () {
		var winResize, resizeTimer,
			that = this,
			DOM = that.DOM,
			winSize = _$window.width() * _$window.height();
			
		that._click = function (event) {
			var target = event.target, callbackID;
			
			if (target.disabled) return false; // IE BUG
			
			if (target === DOM.close[0]) {
				that._trigger(that.config.noText);
				return false;
			} else {
				callbackID = target[_expando + 'callback'];
				callbackID && that._trigger(callbackID);
			};
		};
		
		that._eventDown = function () {
			that.zIndex();
		};
		
		winResize = function () {
			var newSize,
				oldSize = winSize,
				elem = that.config.follow;
			
			if ('all' in document) {
				// IE6~7 window.onresize bug
				newSize = _$window.width() * _$window.height();
				winSize = newSize;
				if (oldSize === newSize) return;
			};
			
			
			if (elem) {
				that.follow(elem);
			} else {
				that.position();
			};
		};
		
		that._winResize = function () {
			resizeTimer && clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				winResize();
			}, 40);
		};
		
		// 监听点击
		DOM.wrap.bind('click', that._click).bind(_eventDown, that._eventDown);
		
		// 窗口调节事件
		_$window.bind('resize', that._winResize);
	},
	
	// 卸载事件代理
	_uneventProxy: function () {
		var that = this,
			DOM = that.DOM;
		
		DOM.wrap.unbind('click', that._click).unbind(_eventDown, that._eventDown);
		_$window.unbind('resize', that._winResize);
	}
	
};

artDialog.fn._init.prototype = artDialog.fn;
$.fn.dialog = $.fn.artDialog = function () {
	var config = arguments;
	this[this.live ? 'live' : 'bind']('click', function () {
		artDialog.apply(this, config);
		return false;
	});
	return this;
};



/** 最顶层的对话框API */
artDialog.focus = null;



/** 对话框列表 */
artDialog.list = {};



// 微型模板引擎 - JavaScript Micro-Templating
// @see http://ejohn.org/blog/javascript-micro-templating/
_tmplEngine = (function(){
	var cache = {};
	return function tmpl(str, data){
		var fn = !/\W/.test(str) ?
		  cache[str] = cache[str] ||
			tmpl(artDialog.templates[str]) :
		  new Function("obj",
			"var p=[],print=function(){p.push.apply(p,arguments);};" +
			"with(obj){p.push('" +
			str
			  .replace(/[\r\t\n]/g, " ")
			  .split("<%").join("\t")
			  .replace(/((^|%>)[^\t]*)'/g, "$1\r")
			  .replace(/\t=(.*?)%>/g, "',$1,'")
			  .split("\t").join("');")
			  .split("%>").join("p.push('")
			  .split("\r").join("\\'")
		  + "');}return p.join('');");
		return data ? fn(data) : fn;
	};
})();



// 全局快捷键
_$document.bind('keydown', function (event) {
	var target = event.target,
		nodeName = target.nodeName,
		rinput = /^INPUT|TEXTAREA$/,
		api = artDialog.focus,
		keyCode = event.keyCode;

	if (!api || !api.config.esc || rinput.test(nodeName)) return;
		
	keyCode === 27 && api._trigger(api.config.noText);
});



/** 模板 */
artDialog.templates = {

	// Template: UI Framework
	outer: [
	'<div class="aui_outer">',
		'<table class="aui_border">',
			'<tbody>',
				'<tr>',
					'<td class="aui_nw"></td>',
					'<td class="aui_n"></td>',
					'<td class="aui_ne"></td>',
				'</tr>',
				'<tr>',
					'<td class="aui_w"></td>',
					'<td class="aui_center"></td>',
					'<td class="aui_e"></td>',
				'</tr>',
				'<tr>',
					'<td class="aui_sw"></td>',
					'<td class="aui_s"></td>',
					'<td class="aui_se"></td>',
				'</tr>',
			'</tbody>',
		'</table>',
	'</div>'].join(''),
	
	// Template: Content Framework
	inner: [
	'<table class="aui_inner">',
		'<tbody>',
			'<tr>',
				'<td class="aui_header">',
					'<div class="aui_titleWrap">',
						'<div class="aui_title" <% if (title === false) { %>style="display:none"<% } %>>',
							'<%=title%>',
						'</div>',
						'<a class="aui_close" <% if (noFn === false) { %>style="display:none"<% } %>',
							' href="javascript:/*artDialog*/;"><%=closeText%></a>',
					'</div>',
				'</td>',
			'</tr>',
			'<tr>',
				'<td class="aui_main">',
					'<div class="aui_content" style="padding:<%=padding%>">',
						'<div class="aui_loading"><span>loading..</span></div>',
					'</div>',
				'</td>',
			'</tr>',
			'<tr>',
				'<td class="aui_footer">',
					'<div class="aui_buttons" style="display:none"></div>',
				'</td>',
			'</tr>',
		'</tbody>',
	'</table>'].join('')

};



/**
 * 默认配置
 */
artDialog.defaults = {

	content: null,				// 消息内容
	title: '\u6d88\u606f',		// 标题. 默认'消息'
	button: null,				// 自定义按钮
	yesFn: null,				// 确定按钮回调函数
	noFn: null,					// 取消按钮回调函数
	yesText: '\u786E\u5B9A',	// 确定按钮文本. 默认'确定'
	noText: '\u53D6\u6D88',		// 取消按钮文本. 默认'取消'
	closeText: '\xd7',			// 关闭按钮文本. 默认'×'
	width: 'auto',				// 内容宽度
	height: 'auto',				// 内容高度
	padding: '20px 25px',		// 内容与边界填充距离
	skin: '',					// 皮肤名(多皮肤共存预留接口)
	initFn: null,				// 对话框初始化后执行的函数
	closeFn: null,				// 对话框关闭执行的函数
	time: null,					// 自动关闭时间
	esc: true,					// 是否支持Esc键关闭
	focus: true,				// 是否支持对话框按钮聚焦
	show: true,					// 初始化后是否显示对话框
	follow: null,				// 跟随某元素
	lock: false,				// 是否锁屏
	background: '#000',			// 遮罩颜色
	opacity: .7,				// 遮罩透明度
	fixed: false,				// 是否静止定位
	zIndex: 1987				// 对话框叠加高度值(重要：此值不能超过浏览器最大限制)
	
};

window.artDialog = $.dialog = $.artDialog = artDialog;
}((window.jQuery && (window.art = jQuery)) || window.art, this));



