/**
 * leaf.js
 *
 * @author  Zou Yiliang
 * @date 20150531
 */

;window.leaf = {};

(function ($) {

    /**
     * 弹窗提示
     *
     * leaf.alert("提示信息", callback, {title: '标题'})
     */
    leaf.alert = function (content, success, options) {

        //兼容 leaf.alert("提示信息", "标题", callback)
        var title = undefined
        if (typeof success === "string") {
            title = success
            success = options
            options = undefined
        }

        var defaultOptions = {title: ""}
        options = options || defaultOptions

        for (var key in defaultOptions) {
            if (!(key in options)) {
                options[key] = defaultOptions[key]
            }
        }

        //兼容 leaf.alert("提示信息", "标题", callback)
        if (typeof title !== "undefined") {
            options.title = title
        }

        success = success || function () {
        }

        if (window.weui && typeof window.weui.alert === "function") {
            weui.alert(content, success, options)
        } else if (window.popup && typeof window.popup.alert === "function") {
            popup.alert(content, success)
        } else {
            alert(content)
            success()
        }
    }

    /**
     * 显示loading图标
     *
     * var load = leaf.loading();
     * load.start();
     * setTimeout(function(){load.stop()},3000);
     *
     * 传入btn时，应该用button元素，否则无法禁用click事件
     *
     */
    leaf.loading = function (btn, content) {
        var d = {};
        d.btn = btn ? $(btn) : false;

        d.start = function () {
            if (btn) {

                if (d.btn.hasClass('disabled')) {
                    return d;
                }

                d.btn.addClass('disabled').attr("disabled", "disabled");

                var loadingText = content || (d.btn.get(0).hasAttribute("data-loading-text") ? d.btn.attr("data-loading-text") : "正在加载...");

                d.btn.data("oldHtml", d.btn.html());
                d.btn.html("<span class='leaf-icon-loading'></span> " + loadingText);

            } else {

                //weui的loading有点丑
                if (window.popup && typeof window.popup.loading === "function") {
                    popup.loading(true);
                } else if (window.weui && typeof window.weui.loading === "function") {
                    d.weuiLoading = weui.loading('loading');
                } else {
                    //不支持loading
                }
            }

            return d;
        };
        d.stop = function () {

            if (d.btn) {
                d.btn.removeClass("disabled").removeAttr("disabled");
                d.btn.html(d.btn.data("oldHtml"));
            } else {

                if (window.popup && typeof window.popup.loading === "function") {
                    popup.loading(false)
                } else if (window.weui && typeof window.weui.loading === "function") {
                    d.weuiLoading.hide()
                } else {
                    //不支持loading
                }

                popup.loading(false)
            }
        };
        return d;
    }

    leaf.messageIndex = 99999;

    /**
     *
     * @param content
     * @param type success|danger|warning|info
     * @param time 显示时间
     */
    leaf.message = function (content, type, time) {
        type = type || "success";
        time = time || 2000;
        leaf.messageIndex++;
        var message = $('<div class="leaf-message" style="position: absolute;top:100px;width:100%;text-align: center;z-index: ' + leaf.messageIndex + '"><span class="icon"></span><span  style="min-width: 300px;display: inline-block" class="alert alert-' + type + '">' + content + '</span></div>');
        $("body").append(message);
        setTimeout(function () {
            message.remove();
        }, time)
    };

    /**
     *
     * @param content
     * @param time
     */
    leaf.messageDanger = function (content, time) {
        leaf.message(content, 'danger', time);
    };

    /**
     * 弹窗提示，自动关闭 一般用于操作成功时的提示场景
     *
     * leaf.toast('操作成功', {duration: 3000, callback:function(){}})
     *
     * 兼容旧版如下方式调用:
     * leaf.toast()
     * leaf.toast('操作成功')
     * leaf.toast('操作成功', 3000)
     * leaf.toast('操作成功', function(){})
     * leaf.toast('操作成功', 3000, function(){})
     */
    leaf.toast = function (content, options, callback) {

        content = content || '操作成功';

        if (typeof  options === "number") {
            callback = callback || function () {

            }
            options = {duration: options, callback: callback}
        }

        if (typeof options === "function") {
            options = {callback: options}
        }

        var defaultOptions = {
            duration: 1500, callback: function () {
            }
        }

        options = options || defaultOptions
        for (var key in defaultOptions) {
            if (!(key in options)) {
                options[key] = defaultOptions[key]
            }
        }

        if (window.popup && typeof window.popup.cute === "function") {
            popup.cute(content, options.duration, options.callback)
        } else if (window.weui && typeof window.weui.toast === "function") {
            weui.toast(content, options)
        } else {
            alert(content)
            callback()
        }
    }

    /**
     * 确认弹框
     *
     * leaf.confirm("你确定要执行xxx吗", function(){
     *   alert('执行');
     * },function(){
     *   alert('不执行');
     * });
     *
     * @param content 提示信息
     * @param success 点击确定后的回调函数
     * @param cancel 点击取消后的回调函数
     */
    leaf.confirm = function (content, success, cancel) {

        success = success || function () {
        };
        cancel = cancel || function () {
        };

        if (window.weui && typeof window.weui.confirm === "function") {
            weui.confirm(content, success, cancel);
        } else if (window.popup && typeof window.popup.confirm === "function") {
            popup.confirm(content, success, cancel);
        } else {
            if (confirm(content)) {
                success()
            } else {
                cancel()
            }
        }
    }

    /**
     * 弹出显示页面隐藏的内容
     * @param selector 需要弹出元素的jQuery选择器
     * @param callback 关闭时回调函数
     */
    leaf.show = function (selector, callback) {

        callback = callback || function () {
        };

        var elem = $(selector);
        elem.show();


        var dia = $.popup({
            content: elem,
            className: "popup-show-div",
            modal: true
        });
        dia.show();

        this.close = function () {
            dia.hide();
            callback();
        };
        return this;
    }

    /**
     * 弹出显示ajax获取的内容
     * @param url
     * @param data
     * @param callback 关闭时执行
     */
    leaf.ajaxShow = function (url, data, callback) {
        callback = callback || function () {
        }
        if (typeof data == "function") {
            callback = data
            data = {}
        }
        var layerId = "leafAjaxPopupLayer";
        var div;
        if ($("#" + layerId).length == 0) {
            div = $("<div id=\"" + layerId + "\" style=\"display:none;\"></div>");
            div.appendTo($("body"));
        } else {
            div = $("#" + layerId);
        }

        var _this = this;

        $.get(url, data, function (str) {
            div.html(str);

            var o = leaf.show("#" + layerId, callback);
            _this.close = function () {
                o.close();
            }

        });
        return _this;
    }

    /**
     * 中文验证
     *
     * 示例
     * leaf.isChinese('我是中文');
     *
     * @param str
     * @returns boolean
     */
    leaf.isChinese = function (str) {
        var reg = /^[\u4E00-\u9FA5]+$/;
        return reg.test(str);
    }

    /**
     * 邮箱验证
     *
     * 示例
     * leaf.isEmail('123456@qq.com');
     *
     * @param str
     * @returns boolean
     */
    leaf.isEmail = function (str) {
        var reg = /^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/;
        return reg.test(str);
    }

    /**
     * 手机验证
     *
     * 示例
     * leaf.isMobile('13888888888');
     *
     * @param str
     * @returns boolean
     */
    leaf.isMobile = function (str) {
        var reg = /^1\d{10}$/;
        return reg.test(str);
    }

    /**
     * 数字验证
     *
     * 示例
     * leaf.isNumber('123');
     *
     * @param str
     * @returns boolean
     */
    leaf.isNumber = function (str) {
        var reg = /^\d+$/;
        return reg.test(str);
    }

    leaf.isWeixin = function () {
        var ua = navigator.userAgent.toLowerCase();
        return ua.indexOf('micromessenger') >= 0;
    }

    leaf.isAndroid = function () {
        var ua = navigator.userAgent.toLowerCase();
        return ua.indexOf('android') >= 0;
    }

    leaf.isIos = function () {
        var ua = navigator.userAgent.toLowerCase();
        return (ua.indexOf('iphone') >= 0) || (ua.indexOf('ipad') >= 0);
    }

    /**
     * 将 Date 转化为指定格式的String
     * @param date     date = new Date();      date = new Date(Linux时间戳*1000):
     * @param fmt "yyyy-m-d h:i:s"
     * @returns string
     */
    leaf.dateFormat = function (date, fmt) {
        // 月(m)、日(d)、小时(h)、分(i)、秒(s)、季度(q) 可以用 1-2 个占位符，
        // 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
        // 例子：
        // (new Date()).Format("yyyy-m-dd hh:ii:ss.S") ==> 2006-07-02 08:09:04.423
        // (new Date()).Format("yyyy-m-d h:i:s.S")      ==> 2006-7-2 8:9:4.18

        var o = {
            "m+": date.getMonth() + 1, //月份
            "d+": date.getDate(), //日
            "h+": date.getHours(), //小时
            "i+": date.getMinutes(), //分
            "s+": date.getSeconds(), //秒
            "q+": Math.floor((date.getMonth() + 3) / 3), //季度
            "S": date.getMilliseconds() //毫秒
        };
        if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
        for (var k in o) {
            if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        }
        return fmt;
    };

    /**
     * 转为html实体
     * @param str
     * @returns {string}
     */
    leaf.htmlEncode = function (str) {
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;')
            .replace(/</g, '&lt;').replace(/>/g, '&gt;');
    };

    function parse_str(query) {
        var g = {}
        if (typeof query === 'undefined') {
            return g
        }
        var GET = query.split('&')
        for (var i = 0; i < GET.length; i++) {
            var q = GET[i].split('=')
            g[q[0]] = decodeURI(q[1])
        }
        return g
    }

    /**
     * 获取GET参数
     * @param key
     * @returns {*}
     */
    leaf.getParam = function (key) {
        var url = location.href
        var index = url.indexOf('?')
        if (index == -1) return null
        var query = url.substr(index + 1, url.length)
        var GET = parse_str(query)
        if (!key || $.type(key) !== 'string') return GET
        return GET[key] ? GET[key] : null
    }

})(jQuery);

