;
(function ($) {

	/*!
	 * popup.js
	 * @author  Xiao Hua <coolr@foxmail.com>
	 * @since   1.1
	 *
	 */
	$.popup = function (options) {
		return new popup(options)
	};
	var popup = function (options) {
		var settings = {
			title: "", // string | jq对象
			content: "", // string | jq对象
			button: [],
			width: "auto", //"300px" | 50% 设置宽度度
			height: "auto", //"300px" | 50%\ auto 设置高度
			modal: false, //true 模态 | false 非模态（点击遮罩不移除弹出层）
			callback: null,
			className: ""
		};
		this.options = $.extend(settings, options)
		this.init()
	}
	popup.prototype =
	{
		init: function () {
			var that = this;
			var style = 'style="width:' + that.options.width + ';height:' + that.options.height + ';"'

			var html = '<div  class="popup"><table><tr><td  data-role="mask"><div class="popup-container" ' + style + '>';
			if (that.options.title.length) {
				html += '<div class="popup-header">' + that.options.title + '</div>';
			}
			html += '<div class="popup-content" ></div>';
			if (that.options.button.length) {
				html += '<div class="popup-footer">';
				var btnArr = that.options.button;
				for (var i in btnArr) {
					html += ('<a href="javascript:;" style="width:' + (100 / btnArr.length) + '%;">' + btnArr[i]['text'] + '</a>')
				}
				html += '</td></tr></table></div>';
			}
			this.$element = $(html).appendTo("body");
			this.$element.find(".popup-content").html(that.options.content);
			this.$element.find(".popup-footer>a").each(function (index, button) {
				$(button).on("click", function (event) {
					var callback = that.options.button[index]["callback"];
					if (typeof callback == "function") {
						callback(event, that)
					}
				})
			});

			//给回调函数和遮罩模态弹出绑定响应事件
			$(that.$element).on({
				"click": function (event) {
					var _element = event.target;

					//data-role="mask"事件响应,移除非模态弹出层
					if (($(_element).data("role") == "mask") && (that.options.modal == false)) {
						that.close()
					}

					if ($(_element).data("callback")) {
						var callback = that.options.callback && that.options.callback[$(_element).data("callback")]
						if (typeof callback == "function") {
							callback(event, that)
						}
					}
				}
			})
			//
			$(window).on("resize", function () {
				//窗体变化保持居中
				if (that.$element.hasClass("is-visible")) {
					that.show()
				}
			})
		},
		show: function () {
			var that = this;
			that.$element.addClass("is-visible");
			that.$element.addClass(that.options.className)
		},
		hide: function () {
			this.$element.removeClass("is-visible")
		},
		close: function () {
			this.$element.remove();
		}
	}

	// 扩展函数
	var popupFn = {};
	popupFn.alert = function (content, callback) {
		var p = $.popup({
			content: content,
			modal: true,
			className: "popup-alert",
			button: [
				{
					"text": "确认",
					"callback": function (event, popup, button, index) {
						popup.close();
						callback && callback();
					}}
			]
		})
		p.show();
		return p;
	}

	popupFn.confirm = function (content, yesCallback, noCallback) {
		var p = $.popup({
			content: content,
			modal: true,
			className: "popup-confirm",
			button: [
				{
					"text": "取消",
					"callback": function (event, popup, button, index) {
						noCallback && noCallback();
						popup.close();
					}
				},
				{
					"text": "确认",
					"callback": function (event, popup, button, index) {
						yesCallback && yesCallback();
						popup.close();
					}
				}
			]
		})
		p.show();
		return p;
	}

	popupFn.cute = function (content, timeout, callback) {
		var p = $.popup({
			content: content,
			modal: true,
			className: "popup-cute"
		});
		p.show();

		timeout = timeout || 1200;
		if (typeof timeout == "function") {
			callback = timeout;
			timeout = 1200;
		}

		if (timeout != 0) {
			setTimeout(function () {
				p.close();
				callback && callback();
			}, timeout);
		}
		return p;
	}

	popupFn.loading = function (bool) {
		if (bool == true) {
			if ($(".popup-loading").length == 0) {
				$.popup({
					className: "popup-loading",
					content: "",
					modal: true
				}).show();
			}
		} else {
			$(".popup-loading").remove();
		}

	};

	window.popup = popupFn

}(window.jQuery || window.Zepto));
