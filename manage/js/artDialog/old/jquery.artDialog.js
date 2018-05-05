/*
 * artDialog 4.0.5
 * Date: 2011-07-30 14:29
 * http://code.google.com/p/artdialog/
 * (c) 2009-2010 TangBin, http://www.planeArt.cn
 *
 * This is licensed under the GNU LGPL, version 2.1 or later.
 * For details, see: http://creativecommons.org/licenses/LGPL/2.1/
 */
 
(function ($, window, undefined) {

$.log = function (content) {window.console && console.log(content)};
$(function () {
	!window.jQuery && document.compatMode === 'BackCompat'
	&& alert('artDialog Error: document.compatMode === "BackCompat"');
});

var _box, _thisScript, _skin, _path, _tmplEngine,
	_count = 0,
	_$window = $(window),
	_$document = $(document),
	_$html = $('html'),
	_$body = $(function(){_$body = $('body')}),
	_elem = document.documentElement,
	_isIE6 = !-[1,] && !('minWidth' in _elem.style),
	_isMobile = 'ontouchend' in _elem && !('onmousemove' in _elem)
		|| /(iPhone|iPad|iPod)/i.test(navigator.userAgent),
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
	config.id = elem && elem[_expando + 'follow'] || config.id || _expando + _count;
	api = artDialog.list[config.id];
	if (elem && api) return api.follow(elem).zIndex().focus();
	if (api) return api.zIndex();
	
	// 目前主流移动设备对fixed支持不好
	if (_isMobile) config.fixed = false;
	
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
	
	_count ++;
	return artDialog.list[config.id] = _box ?
		_box._init(config, true) : new artDialog.fn._init(config);
};

artDialog.fn = artDialog.prototype = {

	version: '4.0.5',
	
	_init: function (config, isReset) {
		var that = this;
		
		that.config = config;
		that._isClose = false;
		that._listeners = {};
		that._minWidth = config.minWidth;
		that._minHeight = config.minHeight;
		that._fixed = _isIE6 ? false : config.fixed;
		
		if (!isReset) {
			that._wrap = document.createElement('div');
			that.DOM = {
				wrap: $(that._wrap)
			};
			that._outerTmpl();
		};
		
		that._wrap.style.cssText = 'position:absolute;left:0;top:0';
		that._wrap.className = config.skin;
		that._innerTmpl();
		
		if (isReset) {
			_box = null;
		} else {
			that._eventProxy();
		};

		that.size(config.width, config.height);
		config.follow ? that.follow(config.follow) : that.position(config.left, config.top);
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
	 * @param	{String}						模板 (可选, 需要msg参数类型为 Object 才能生效)
	 * @return	{this, HTMLElement}				如果无参数则返回内容容器DOM对象
	 */
	content: function (msg, tmpl) {
		var prev, next, parent, display,
			that = this,
			content = that.DOM.content,
			elem = content[0];
		
		that._elemBack = null;
		
		if (!msg && msg !== 0) {
			return elem;
		} else if (tmpl) {
			content.html(_tmplEngine(tmpl, msg));
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
		
		_isIE6 && that._selectFix();
		that._runScript(elem);
		
		return that;
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
	
	/**
	 * 位置
	 * @param	{Number, String}
	 * @param	{Number, String}
	 */
	position: function (left, top) {
		var scaleLeft, scaleTop,
			that = this,
			wrap = that.DOM.wrap,
			ie6Fixed = _isIE6 && that.config.fixed,
			docLeft = _$document.scrollLeft(),
			docTop = _$document.scrollTop(),
			dl = that._fixed ? 0 : docLeft,
			dt = that._fixed ? 0 : docTop,
			ww = _$window.width(),
			wh = _$window.height(),
			ow = wrap[0].offsetWidth,
			oh = wrap[0].offsetHeight,
			style = wrap[0].style;
		
		if (!left && left !== 0) left = that._scaleLeft;
		if (!top && top !== 0) top = that._scaleTop;
			
		// 转换left百分比值为数值
		if (typeof left === 'string') {
			scaleLeft = that._toNumber(left, ww - ow);
			if (scaleLeft !== null) {
				that._scaleLeft = left;
				left = scaleLeft + dl;
			};
		} else if (ie6Fixed && typeof left === 'number') {
			left += docLeft;
		};

		// 黄金比例垂直居中
		if (top === 'goldenRatio') {
			that._scaleTop = top;
			top = Math.max(Math.min((oh < 4 * wh / 7 ?
				wh * 0.382 - oh / 2 :
				(wh - oh) / 2) + dt, wh - oh + dt), dt);
		
		// 转换top百分比值为数值
		} else if (typeof top === 'string') {
			scaleTop = that._toNumber(top, wh - oh);
			if (scaleTop !== null) {
				that._scaleTop = top;
				top = scaleTop + dt;
			};
		} else if (ie6Fixed && typeof top === 'number') {
			top += docTop;
		};

		if (typeof left === 'number') style.left = left + 'px';
		if (typeof top === 'number') style.top = top + 'px';
		
		/*that.config.follow = null;*/
		that._autoPositionType();
		
		return that;
	},
	
	/**
	 *	尺寸
	 *	@param	{Number, String}	宽度
	 *	@param	{Number, String}	高度
	 *	@return	this
	 */
	size: function (width, height) {
		var maxWidth, maxHeight, scaleWidth, scaleHeight,
			that = this,
			DOM = that.DOM,
			wrap = DOM.wrap,
			main = DOM.main,
			wrapStyle = wrap[0].style,
			style = main[0].style;
			
		if (!width && width !== 0) width = that._scaleWidth;
		if (!height && height !== 0) height = that._scaleHeight;
				
		// 转换宽度百分比为数值
		if (typeof width === 'string') {
			maxWidth = _$window.width() - wrap[0].offsetWidth + main[0].offsetWidth;
			scaleWidth = that._toNumber(width, maxWidth);
			if (scaleWidth !== null) {
				that._scaleWidth = width;
				width = scaleWidth;
			} else if (width.indexOf('px') !== -1) {
				width = parseInt(width);
			};
		};
		
		// 转换高度百分比为数值
		if (typeof height === 'string') {
			maxHeight = _$window.height() - wrap[0].offsetHeight + main[0].offsetHeight;
			scaleHeight = that._toNumber(height, maxHeight);
			if (scaleHeight !== null) {
				that._scaleHeight = height;
				height = scaleHeight;
			} else if (height.indexOf('px') !== -1) {
				height = parseInt(height);
			};
		};
		
		if (typeof width === 'number') {
			wrapStyle.width = 'auto';
			style.width = Math.max(that._minWidth, width) + 'px';
			wrapStyle.width = wrap[0].offsetWidth + 'px'; // 防止未定义宽度的表格遇到浏览器右边边界伸缩
		} else if (typeof width === 'string') {
			style.width = width;
			width === 'auto' && wrap.css('width', 'auto');
		};
		
		if (typeof height === 'number') {
			style.height = Math.max(that._minHeight, height) + 'px';
		} else if (typeof height === 'string') {
			style.height = height;
		};
		
		_isIE6 && that._selectFix();
		
		return that;
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
		
		var winWidth = _$window.width(),
			winHeight = _$window.height(),
			docLeft =  _$document.scrollLeft(),
			docTop = _$document.scrollTop(),
			offset = $elem.offset(),
			width = $elem[0].offsetWidth,
			height = $elem[0].offsetHeight,
			left = that._fixed ? offset.left - docLeft : offset.left,
			top = that._fixed ? offset.top - docTop : offset.top,
			wrap = that.DOM.wrap[0],
			style = wrap.style,
			wrapWidth = wrap.offsetWidth,
			wrapHeight = wrap.offsetHeight,
			setLeft = left - (wrapWidth - width) / 2,
			setTop = top + height,
			dl = that._fixed ? 0 : docLeft,
			dt = that._fixed ? 0 : docTop;
		
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
		that._autoPositionType();
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
		_isIE6 && that._selectFix();
		
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
		if (typeof fn === 'function' && fn.call(that, window) === false) {
			return that;
		};
		
		that.unlock();
		
		that._elemBack && that._elemBack();
		that._setAbsolute();
		that._timer = that._focus = null;
		that._scaleLeft = that._scaleTop = null;
		that._scaleWidth = that._scaleHeight = null;
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
			docWidth = _$document.width(),
			docHeight = _$document.height(),
			lockMaskWrap = that._lockMaskWrap || $(_$body[0].appendChild(document.createElement('div'))),
			lockMask = that._lockMask || $(lockMaskWrap[0].appendChild(document.createElement('div'))),
			domTxt = '(document).documentElement',
			sizeCss = _isMobile ? 'width:' + docWidth + 'px;height:' + docHeight
				+ 'px' : 'width:100%;height:100%',
			ie6Css = _isIE6 ?
				'position:absolute;left:expression(' + domTxt + '.scrollLeft);top:expression('
				+ domTxt + '.scrollTop);width:expression(' + domTxt
				+ '.clientWidth);height:expression(' + domTxt + '.clientHeight)'
			: '';
		
		wrap.css('zIndex', index);
		
		lockMaskWrap[0].style.cssText = sizeCss + ';position:fixed;z-index:'
			+ (index - 1) + ';top:0;left:0;overflow:hidden;' + ie6Css;
		lockMask[0].style.cssText = 'height:100%;background:' + config.background
			+ ';filter:alpha(opacity=0);opacity:0';
		
		// 让IE6锁屏遮罩能够盖住下拉控件
		if (_isIE6) lockMask.html(
			'<iframe src="about:blank" style="width:100%;height:100%;position:absolute;' +
			'top:0;left:0;z-index:-1;filter:alpha(opacity=0)"></iframe>');
			
		lockMask.stop().animate({opacity: config.opacity}, config.duration);
		lockMask[0].ondblclick = function () {
			that.close();
		};
		
		that._lockMaskWrap = lockMaskWrap;
		that._lockMask = lockMask;
		
		that._lock = true;
		return that;
	},
	
	/** 解开屏锁 */
	unlock: function () {
		var that = this,
			lockMaskWrap = that._lockMaskWrap,
			lockMask = that._lockMask;
		
		if (!that._lock) return that;
		var style = lockMaskWrap[0].style;
		
		lockMask[0].ondblclick = null;
		lockMask.stop().animate({opacity: 0}, that.config.duration, function () {
			if (_isIE6) {
				style.removeExpression('width');
				style.removeExpression('height');
				style.removeExpression('left');
				style.removeExpression('top');
			};
			style.cssText = 'display:none';
			
			if (_box) {
				lockMaskWrap.remove();
				that._lockMaskWrap = that._lockMask = null;
			};
		});

		that._lock = false;
		return that;
	},
	
	// 插入修饰结构 （只运行一次）
	_outerTmpl: function () {
		var that = this,
			wrap = that._wrap;
			
		wrap.innerHTML = _tmplEngine('outer', that.config);
		document.body.appendChild(wrap);
		
		that._getDOM(wrap);
		_isIE6 && that._pngFix(wrap);
	},
	
	// 插入内容区域 （可能运行多次）
	_innerTmpl: function () {
		var that = this,
			config = that.config,
			DOM = that.DOM,
			center = DOM.center;
		
		center.html(_tmplEngine('inner', config));
		that._getDOM(center[0]);
		_isIE6 && that._pngFix(center[0]);
		DOM.se.css('cursor', config.resize ? 'se-resize' : 'auto');
		DOM.title.css('cursor', config.drag ? 'move' : 'auto');
		
		that.button(config.button).content(config.content, config.tmpl);
	},
	
	// 获取元素
	_getDOM: function (parent) {
		var DOM = this.DOM,
			els = parent.getElementsByTagName('*'),
			elsLen = els.length;
			
		for (var i = 0; i < elsLen; i ++) {
			DOM[els[i].className.split('aui_')[1]] = $(els[i]);
		};
	},
	
	// 百分比转换成数值
	_toNumber: function (scale, length) {
		return scale.indexOf('%') !== -1 ?
			parseInt(length * scale.split('%')[0] / 100) : null;
	},
	
	// 让IE6 CSS支持PNG背景
	_pngFix: function (parent) {
		var i = 0, elem, png, pngPath,
			path = artDialog.defaults.path + '/skins/',
			list = parent.getElementsByTagName('*');
		
		for (; i < list.length; i ++) {
			elem = list[i];
			png = elem.currentStyle['png'];
			if (png) {
				pngPath = path + png;
				elem.style.backgroundImage = 'none';
				elem.style.p = pngPath;
				elem.runtimeStyle.filter = "progid:DXImageTransform.Microsoft." +
					"AlphaImageLoader(src='" + pngPath + "',sizingMethod='crop')";
			};
		};
		elem = null;
	},
	
	// 强制覆盖IE6下拉控件
	_selectFix: function () {
		var elem = this.DOM.wrap[0],
			expando = _expando + 'iframeMask',
			iframe = elem[expando],
			width = elem.offsetWidth,
			height = elem.offsetHeight,
			left = - (width - elem.clientWidth) / 2 + 'px',
			top = - (height - elem.clientHeight) / 2 + 'px';

		width = width + 'px';
		height = height + 'px';
		
		if (iframe) {
			iframe.style.width = width;
			iframe.style.height = height;
		} else {
			iframe = elem.appendChild(document.createElement('iframe'));
			elem[expando] = iframe;
			iframe.src = 'about:blank';
			iframe.style.cssText = 'position:absolute;z-index:-1;left:'
				+ left + ';top:' + top
				+ ';width:' + width + ';height:' + height
				+ ';filter:alpha(opacity=0)';
		};
	},
	
	// 解析HTML片段中自定义类型脚本:
	// <script type="text/dialog"></script>
	_runScript: function (elem) {
		var fun, i = 0, n = 0,
			tags = elem.getElementsByTagName('script'),
			length = tags.length,
			script = [];
			
		for (; i < length; i ++) {
			if (tags[i].type === 'text/dialog') {
				script[n] = tags[i].innerHTML;
				n ++;
			};
		};
		
		if (script.length) {
			script = script.join('');
			fun = new Function(script);
			fun.call(this);
		};
	},
	
	// 自动切换定位类型
	_autoPositionType: function () {
		var that = this;
		that[that.config.fixed ? '_setFixed' : '_setAbsolute']();
	},
	
	
	// 设置静止定位
	// IE6 Fixed @see: http://www.planeart.cn/?p=877
	_setFixed: (function () {
		_isIE6 && $(function () {
			var bg = 'backgroundAttachment';
			if (_$html.css(bg) !== 'fixed' && _$body.css(bg) !== 'fixed') {
				_$html.css({
					backgroundImage: 'url(about:blank)',
					backgroundAttachment: 'fixed'
				});
			};
		});
		
		return function () {
			var $elem = this.DOM.wrap,
				style = $elem[0].style;
			
			if (_isIE6) {
				var left = parseInt($elem.css('left')),
					top = parseInt($elem.css('top')),
					sLeft = _$document.scrollLeft(),
					sTop = _$document.scrollTop(),
					txt = '(document.documentElement)';
				
				this._setAbsolute();
				style.setExpression('left', 'eval(' + txt + '.scrollLeft + '
					+ (left - sLeft) + ') + "px"');
				style.setExpression('top', 'eval(' + txt + '.scrollTop + '
					+ (top - sTop) + ') + "px"');
			} else {
				style.position = 'fixed';
			};
		};
	}()),
	
	// 设置绝对定位
	_setAbsolute: function () {
		var style = this.DOM.wrap[0].style;
			
		if (_isIE6) {
			style.removeExpression('left');
			style.removeExpression('top');
		};

		style.position = 'absolute';
	},
	
	// 按钮事件触发
	_trigger: function (id) {
		var that = this,
			fn = that._listeners[id] && that._listeners[id].callback;
		return typeof fn !== 'function' || fn.call(that, window) !== false ?
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
				elem = that.config.follow,
				width = that._scaleWidth,
				height = that._scaleHeight,
				left = that._scaleLeft,
				top = that._scaleTop;
			
			if ('all' in document) {
				// IE6~7 window.onresize bug
				newSize = _$window.width() * _$window.height();
				winSize = newSize;
				if (oldSize === newSize) return;
			};
			
			if (width || height) that.size(width, height);
			
			if (elem) {
				that.follow(elem);
			} else if (left || top) {
				that.position(left, top);
			};
		};
		
		that._winResize = function () {
			resizeTimer && clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				winResize();
			}, 40);
		};
		
		// 监听点击
		DOM.wrap.bind('click', that._click)
		.bind(_eventDown, that._eventDown);
		
		// 窗口调节事件
		_$window.bind('resize', that._winResize);
	},
	
	// 卸载事件代理
	_uneventProxy: function () {
		var that = this,
			DOM = that.DOM;
		
		DOM.wrap.unbind('click', that._click)
		.unbind(_eventDown, that._eventDown);
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



// 获取artDialog路径
_path = window['_artDialog_path'] || (function (script, i, me) {
	for (i in script) {
		// 如果通过第三方脚本加载器加载本文件，请保证文件名含有"artDialog"字符
		if (script[i].src && script[i].src.indexOf('artDialog') !== -1) me = script[i];
	};
	
	_thisScript = me || script[script.length - 1];
	me = _thisScript.src.replace(/\\/g, '/');
	return me.lastIndexOf('/') < 0 ? '.' : me.substring(0, me.lastIndexOf('/'));
}(document.getElementsByTagName('script')));




// 无阻塞载入CSS (如"artDialog.js?skin=aero")
_skin = window['_artDialog_skin'] || _thisScript.src.split('skin=')[1];
if (_skin) {
	var link = document.createElement('link');
	link.rel = 'stylesheet';
	link.href = _path + '/skins/' + _skin + '.css?' + artDialog.fn.version;
	$('head')[0].appendChild(link);
};



// 触发浏览器预先缓存背景图片
_$window.bind('load', function () {
	setTimeout(function () {
		if (!_count) {
			artDialog({left:-9999, time: 9, lock:false, focus: false});
		};
	}, 150);
});



// 开启IE6 CSS背景图片缓存
try {
	document.execCommand('BackgroundImageCache', false, true);
} catch (e) {};



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
				'<td <% if (icon) { %>colspan="2"<% } %> class="aui_header">',
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
				'<% if (icon) { %>',
				'<td class="aui_icon">',
					'<div class="aui_icon_<%=icon%>" style="',
						'background:url(<%=path%>/skins/icons/<%=icon%>.png) no-repeat center center;_png:icons/<%=icon%>.png',
					'"></div>',
				'</td>',
				'<% } %>',
				'<td class="aui_main">',
					'<div class="aui_content" style="padding:<%=padding%>">',
						'<div class="aui_loading"><span>loading..</span></div>',
					'</div>',
				'</td>',
			'</tr>',
			'<tr>',
				'<td <% if (icon) { %>colspan="2"<% } %> class="aui_footer">',
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
	tmpl: null,					// 供插件定义内容模板
	button: null,				// 自定义按钮
	yesFn: null,				// 确定按钮回调函数
	noFn: null,					// 取消按钮回调函数
	yesText: '\u786E\u5B9A',	// 确定按钮文本. 默认'确定'
	noText: '\u53D6\u6D88',		// 取消按钮文本. 默认'取消'
	closeText: '\xd7',			// 关闭按钮文本. 默认'×'
	width: 'auto',				// 内容宽度
	height: 'auto',				// 内容高度
	minWidth: 96,				// 最小宽度限制
	minHeight: 32,				// 最小高度限制
	padding: '20px 25px',		// 内容与边界填充距离
	skin: '',					// 皮肤名(多皮肤共存预留接口)
	icon: null,					// 消息图标名称
	initFn: null,				// 对话框初始化后执行的函数
	closeFn: null,				// 对话框关闭执行的函数
	time: null,					// 自动关闭时间
	esc: true,					// 是否支持Esc键关闭
	focus: true,				// 是否支持对话框按钮聚焦
	show: true,					// 初始化后是否显示对话框
	follow: null,				// 跟随某元素
	path: _path,				// artDialog路径
	lock: false,				// 是否锁屏
	background: '#000',			// 遮罩颜色
	opacity: .7,				// 遮罩透明度
	duration: 300,				// 遮罩透明度渐变动画速度
	fixed: false,				// 是否静止定位
	left: '50%',				// X轴坐标
	top: 'goldenRatio',			// Y轴坐标
	zIndex: 1987,				// 对话框叠加高度值(重要：此值不能超过浏览器最大限制)
	resize: true,				// 是否允许用户调节尺寸
	drag: true					// 是否允许用户拖动位置
	
};

window.artDialog = $.dialog = $.artDialog = artDialog;
}((window.jQuery && (window.art = jQuery)) || window.art, this));







/*!
	可选外置模块：话框拖拽支持
------------------------------------------------------------------*/
;(function ($) {

var _dragEvent, _use,
	_$window = $(window),
	_$document = $(document),
	_elem = document.documentElement,
	_isIE6 = !-[1,] && !('minWidth' in _elem.style),
	_isLosecapture = 'onlosecapture' in _elem,
	_isSetCapture = 'setCapture' in _elem,
	_isTouch = 'createTouch' in _elem,
	_startEvent = _isTouch ? 'touchstart' : 'mousedown',
	_moveEvent = _isTouch ? 'touchmove' : 'mousemove',
	_endEvent = _isTouch ? 'touchend' : 'mouseup';


// 拖拽事件
artDialog.dragEvent = function () {
	var that = this,
		proxy = function (name) {
			var fn = that[name];
			that[name] = function () {
				return fn.apply(that, arguments);
			};
		};
		
	proxy('start');
	proxy('move');
	proxy('end');
};

artDialog.dragEvent.prototype = {

	// 开始拖拽
	onstart: $.noop,
	start: function (event) {
		var that = this;
		event = that._fix(event);
		_$document
			.bind(_moveEvent, that.move)
			.bind(_endEvent, that.end);
			
		that._clientX = event.clientX;
		that._clientY = event.clientY;
		that.onstart(event.clientX, event.clientY);
		event.preventDefault();
	},
	
	// 正在拖拽
	onmove: $.noop,
	move: function (event) {
		var that = this;
		event = that._fix(event);
		event.preventDefault();
		that.onmove(
			event.clientX - that._clientX,
			event.clientY - that._clientY
		);
	},
	
	// 结束拖拽
	onend: $.noop,
	end: function (event) {
		var that = this;
		event = that._fix(event);
		_$document
			.unbind(_moveEvent, that.move)
			.unbind(_endEvent, that.end);
		
		event && that.onend(event.clientX, event.clientY);
	},
	
	_fix: function (event) {
		return _isTouch ? event.touches.item(0) : event;
	}
	
};

_use = function (event) {
	var limit, startWidth, startHeight, startLeft, startTop, isResize,
		api = artDialog.focus,
		config = api.config,
		DOM = api.DOM,
		wrap = DOM.wrap,
		title = DOM.title,
		main = DOM.main;

	// 清除文本选择
	var clsSelect = 'getSelection' in window ? function () {
		window.getSelection().removeAllRanges();
	} : function () {
		try {
			document.selection.empty();
		} catch (e) {};
	};
	
	// 对话框准备拖动
	_dragEvent.onstart = function (x, y) {
		if (isResize) {
			startWidth = main[0].offsetWidth;
			startHeight = main[0].offsetHeight;
		} else {
			startLeft = parseInt(wrap.css('left'));
			startTop = parseInt(wrap.css('top'));
		};
		
		_$document.bind('dblclick', _dragEvent.end);
		!_isIE6 && _isLosecapture ?
			title.bind('losecapture', _dragEvent.end) :
			_$window.bind('blur', _dragEvent.end);
		_isSetCapture && title[0].setCapture();
		
		wrap.addClass('aui_state_drag');
		api.focus();
	};
	
	// 对话框拖动进行中
	_dragEvent.onmove = function (x, y) {
		if (isResize) {
			var wrapStyle = wrap[0].style,
				style = main[0].style,
				width = x + startWidth,
				height = y + startHeight;
			
			wrapStyle.width = 'auto';
			style.width = Math.max(0, width) + 'px';
			wrapStyle.width = wrap[0].offsetWidth + 'px';
			style.height = Math.max(0, height) + 'px';
		} else {
			var style = wrap[0].style,
				left = x + startLeft,
				top = y + startTop;

			style.left = Math.max(limit.minX, Math.min(limit.maxX, left)) + 'px';
			style.top = Math.max(limit.minY, Math.min(limit.maxY, top)) + 'px';
		};
			
		clsSelect();
		_isIE6 && api._selectFix();
	};
	
	// 对话框拖动结束
	_dragEvent.onend = function (x, y) {
		_$document.unbind('dblclick', _dragEvent.end);
		!_isIE6 && _isLosecapture ?
			title.unbind('losecapture', _dragEvent.end) :
			_$window.unbind('blur', _dragEvent.end);
		_isSetCapture && title[0].releaseCapture();
		
		_isIE6 && api._autoPositionType();
		
		wrap.removeClass('aui_state_drag');
	};
	
	isResize = event.target === DOM.se[0] ? true : false;
	limit = (function () {
		var maxX, maxY,
			wrap = api.DOM.wrap[0],
			fixed = wrap.style.position === 'fixed',
			ow = wrap.offsetWidth,
			oh = wrap.offsetHeight,
			ww = _$window.width(),
			wh = _$window.height(),
			dl = fixed ? 0 : _$document.scrollLeft(),
			dt = fixed ? 0 : _$document.scrollTop(),
			
		// 坐标最大值限制
		maxX = ww - ow + dl;
		maxY = wh - oh + dt;
		
		return {
			minX: dl,
			minY: dt,
			maxX: maxX,
			maxY: maxY
		};
	})();
	
	_dragEvent.start(event);
};

// 代理 mousedown 事件触发对话框拖动
_$document.bind(_startEvent, function (event) {
	var api = artDialog.focus;
	if (!api) return;

	var target = event.target,
		config = api.config,
		DOM = api.DOM;
	
	if (config.drag !== false && target === DOM.title[0]
	|| config.resize !== false && target === DOM.se[0]) {
		_dragEvent = _dragEvent || new artDialog.dragEvent();
		_use(event);
	};
});

})(window.jQuery || window.art);