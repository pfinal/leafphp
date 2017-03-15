/**
 * leafjs
 *
 *  依赖 popup-1.1
 *  <link rel="stylesheet" href="static/popup/popup.css"/>
 *  <script src="static/popup/popup.min.js"></script>
 *  <link href="static/leaf/css/leaf.css" rel="stylesheet" />
 *  <script src="static/leaf/leaf-mobile.js"></script>
 *
 * @author  Zou Yiliang
 * @date 20150531
 */

;window.leaf = {};

(function ($) {

    /**
     * 弹窗提示
     *
     * leaf.alert("提示信息", callback)
     * leaf.alert("提示信息", "标题", callback)
     */
    leaf.alert = function (content, title, success) {
        success = success || function () {
            };

        if (typeof title == "function") {
            success = title;
            title = undefined;
        }

        title = title || "系统提示";

        popup.alert(content, success);
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
                popup.loading(true);
            }

            return d;
        };
        d.stop = function () {

            if (d.btn) {
                d.btn.removeClass("disabled").removeAttr("disabled");
                d.btn.html(d.btn.data("oldHtml"));
            } else {
                popup.loading(false);
            }
        };
        return d;
    }

    leaf.messageIndex = 99999;

    /**
     *
     * @param content
     * @param type success|danger|warning|info
     */
    leaf.message = function (content, type) {
        type = type || "success";
        leaf.messageIndex++;
        var message = $('<div class="leaf-message" style="position: absolute;top:100px;width:100%;text-align: center;z-index: ' + leaf.messageIndex + '"><span class="icon"></span><span  style="min-width: 300px;display: inline-block" class="alert alert-' + type + '">' + content + '</span></div>');
        $("body").append(message);
        setTimeout(function () {
            message.remove();
        }, 2000)
    }

    /**
     * 自动消失 弹窗提示
     *
     * leaf.toast()
     * leaf.toast('操作成功')
     * leaf.toast('操作成功', 3000)
     * leaf.toast('操作成功',function(){})
     */
    leaf.toast = function (content, time, callback) {

        content = content || '操作成功';
        time = time || 2000;
        if (typeof time == "function") {
            callback = time;
            time = 2000;
        }
        callback = callback || function () {
            }

        popup.cute(content, time, callback);

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

        popup.confirm(content, success, cancel);
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
     */
    leaf.ajaxShow = function (url, data) {
        var layerId = "leafAjaxPopupLayer";
        var div;
        if ($("#" + layerId).length == 0) {
            div = $("<div id=\"" + layerId + "\" style=\"display:none;\"></div>");
            div.appendTo($("body"));
        } else {
            div = $("#" + layerId);
        }

        $.get(url, data, function (str) {
            div.html(str);
            this.close = leaf.show("#" + layerId).close;
        });
        return this;
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

    /**
     * 将 Date 转化为指定格式的String
     * @param date     date = new Date();      date = new Date(Linux时间戳*1000):
     * @param format "yyyy-m-d h:i:s"
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
})(jQuery);

