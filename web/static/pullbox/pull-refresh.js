;
(function () {
	/*!
	 * @file   下拉刷新+加载更多
	 *
	 * @author xiaohua
	 * @contact coolr@foxmail.com
	 *
	 * @LICENSE MIT
	 *
	 */

	window.pullbox = function (opt) {
		for (var i in opt) {
			options[i] = opt[i];
		}
		init();
	}

	var options = {
		boxSelector: 'body',
		reFreshDistance: 60,
		loadMoreDistance: 100,
		onPull: null,
		onReFresh: null,//添加下拉刷新功能 仅需定义该函数
		onLoadMore: null//添加加载更多功能 仅需定义该函数
	}

	function init() {
		var elembody = document.documentElement || document.body;
		var hasTouch = "ontouchstart" in window ? 1 : 0;
		var box = document.querySelector(options.boxSelector);//必须有
		var distance = 0, reFreshDistance = options.reFreshDistance;
		var state = 'ready';// null(pc端)| ready | refresh
		if (!hasTouch) {
			state = 'null'//pc比触屏移动端多一个状态,需点击屏幕后才能转成ready成为可拖拽状态
		}
		var eventMap = [
			{'START': 'mousedown', 'MOVE': 'mousemove', 'END': 'mouseup'},
			{'START': 'touchstart', 'MOVE': 'touchmove', 'END': 'touchend'}
		];
		var EVENT = eventMap[hasTouch];
		var posStart = { 'y': 0, 't': 0}, posMove = { 'y': 0, 't': 0}, posEnd = {'y': 0, 't': 0};
		var loadMoreLock = false;


		function getScrollTop() {
			return document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset || window.scrollY || 0;
		}

		function bindEvent(type, bindData, callback) {
			box.addEventListener(type, function (e) {
				if (hasTouch) {
					bindData.y = e.changedTouches[0].clientY;
				} else {
					bindData.y = e.clientY;
				}
				bindData.t = new Date().getTime();
				callback();
				if (type == EVENT.MOVE && getScrollTop() == 0 && distance > 0) {
					e.preventDefault();//阻止微信浏览器下拽的网页的提示
				}
			});
		}

		function onStart() {
			if (!hasTouch && state == 'null') {
				state = 'ready'
			}
		}

		function onMove() {
			distance = posMove.y - posStart.y;
			if (state == 'ready' && getScrollTop() == 0) {
				options.onPull && options.onPull(distance, reFreshDistance);
				if (distance >= 0 && distance < reFreshDistance) {
					setDistance(distance);
				} else if (distance >= reFreshDistance) {
					setDistance(reFreshDistance);//达到 refresh 触发条件
				}
			}
		}

		function onEnd() {
			if (state == 'ready') {
				if (distance >= reFreshDistance && getScrollTop() == 0) {
					state = 'refresh';
					options.onReFresh({
						'finish': onReFreshFinish
					});
				} else {
					onReFreshFinish();
				}
			}
		}

		function onReFreshFinish() {
			state = 'ready';
			if (!hasTouch) {
				state = 'null';
			}
			setDistance(0, 400);
		}

		function setDistance(y, duration) {
			duration = duration || 0
			box.style['-webkit-transition-timing-function'] = 'cubic-bezier(0.1, 0.57, 0.1, 1)';
			box.style['transition-timing-function'] = 'cubic-bezier(0.1, 0.57, 0.1, 1)';
			box.style['-webkit-transition-duration'] = duration + 'ms';
			box.style['transition-duration'] = duration + 'ms';
			box.style['-webkit-transform'] = 'translate(0px, ' + y + 'px) translateZ(0px)';
			box.style['transform'] = 'translate(0px, ' + y + 'px) translateZ(0px)';
		}

		if (options.onReFresh) {
			box.style['margin-top'] = -options.reFreshDistance + 'px';
			bindEvent(EVENT.START, posStart, onStart);
			bindEvent(EVENT.MOVE, posMove, onMove);
			bindEvent(EVENT.END, posEnd, onEnd);
		}

		function loadMore() {
			if (loadMoreLock == false && (getScrollTop() + elembody.clientHeight > elembody.offsetHeight - options.loadMoreDistance)) {
				loadMoreLock = true;
				options.onLoadMore({
					'finish': function () {
						loadMoreLock = false;
						setTimeout(loadMore, 100);//内容过少自动加载更多
					}
				});
			}
		}

		if (options.onLoadMore) {
			document.addEventListener("scroll", loadMore, false);
			loadMore();
		}
	}

})();