/**
 * leaf.js
 *
 *  依赖artDialog
 *  <link href="static/art-dialog/css/ui-dialog.css" rel="stylesheet" />
 *  <script src="static/art-dialog/dist/dialog-min.js"></script>
 *
 *  <link href="static/leaf/css/leaf.css" rel="stylesheet" />
 *  <script src="static/leaf/leaf.js"></script>
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
        var d = dialog({
            zIndex: 2048,
            backdropOpacity: 0.4,
            title: title,
            content: "<div style='min-width:18em'>" + content + "</div>",
            okValue: "确定",
            autofocus: true,
            ok: function () {
            },
            onclose: success
        });
        d.showModal();
    }

    /**
     * 显示loading图标
     *
     * var load = leaf.loading();   或者 var load = leaf.loading(按扭对象或选择器);
     * load.start();
     * setTimeout(function(){load.stop()},3000);
     */
    leaf.loading = function (btn, content) {
        if (btn) {
            var d = {};
        } else {
            content = content || "正在加载, 请稍后 ...";
            var d = dialog({
                content: '<div class="ui-dialog-loading" style="margin-top:0px">Loading..</div><div style="margin-top: 10px">' + content + '</div>',
                backdropOpacity: 0.05
            });
        }

        d.btn = btn ? $(btn) : false;
        d.start = function () {
            if (d.btn) {

                d.btn.addClass('disabled').attr("disabled", "disabled");

                var loadingText = content || (d.btn.get(0).hasAttribute("data-loading-text") ? d.btn.attr("data-loading-text") : "正在加载...");

                d.btn.data('oldHtml', d.btn.html());
                d.btn.html("<span class='leaf-icon-loading'></span> " + loadingText);

            } else {
                d.showModal();
            }
            return d;
        };
        d.stop = function () {

            if (d.btn) {
                d.btn.removeClass('disabled').removeAttr("disabled");
                d.btn.html(d.btn.data('oldHtml'));
            } else {
                d.close().remove();
            }

        };
        return d;
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

        var d = dialog({
            //zIndex: 2048,
            content: "<div style='_width:120px;min-width: 120px;text-align: center;padding-top:15px;padding-bottom: 15px;'>" + content + "</div>",
            backdropOpacity: 0.05
        });
        d.showModal();
        setTimeout(function () {
            d.close().remove();
            callback();
        }, time);
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
    };

    /**
     *
     * @param content
     */
    leaf.messageDanger = function (content) {

        leaf.message(content, 'danger');
    };

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

        var d = dialog({
            //zIndex: 10000,
            backdropOpacity: 0.4,
            title: '系统提示',
            content: "<div style='min-width:18em'>" + content + "</div>",
            okValue: "确定",
            autofocus: false,
            ok: function () {
                this.close().remove();
                success();
            },
            cancelValue: "取消",
            cancel: function () {
                this.close().remove();
                cancel();
            }
        });
        d.showModal();
    }

    leaf.leafShowCount = 0;


    /**
     * 弹出显示页面隐藏的内容
     * @param selector 需要弹出元素的jQuery选择器
     * @param callback 关闭时回调函数
     */
    leaf.show = function (selector, callback) {
        var obj = {};
        obj.callback = callback || function () {
            };

        obj.elem = $(selector).show();

        //弹出层初始化 参数都在这里配置
        obj.dia = dialog({

            //添加id属性，是为了支持 .ui-popup[aria-labelledby^="title:leafShowDialog"] 定位到弹出层，做样式修改
            //id不重复才能在多次弹层
            /*
             去掉弹出层默认边框和padding
             .ui-popup[aria-labelledby^="title:leafShowDialog"] .ui-dialog {
             border-radius: 0;
             }
             .ui-popup[aria-labelledby^="title:leafShowDialog"] .ui-dialog-body {
             padding: 0;
             }
             */
            id: "leafShowDialog" + (leaf.leafShowCount++),
            content: obj.elem.get(0),
            backdropOpacity: 0.4
        });

        var close = $('<a href="javascript:;"><span class="leaf-show-dialog-close"></span></a>');
        obj.elem.prepend(close);
        close.click(function () {
            obj.close();
        });

        obj.dia.showModal();//show()显示 showModal()有遮盖层
        obj.close = function () {
            this.dia.close().remove();
            this.callback();
            close.remove();
            obj = null;
        };
        return obj;
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
     * @param date
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


    $(function () {
        //重要操作的confirm
        //<a href="delete.php" class="leaf-confirm" data-dialog-content="您确定执行此操作吗">删除</a>
        $(document).on("click", "a.leaf-confirm,a.leaf-confirm", function () {

            var _this = this;

            var content = _this.hasAttribute("data-dialog-content") ? _this.getAttribute("data-dialog-content") : "您确定执行此操作吗?";
            var title = _this.hasAttribute("data-dialog-title") ? _this.getAttribute("data-dialog-title") : "提示";

            var d = dialog({
                zIndex: 2048,
                autofocus: false,
                title: title,
                content: "<div style='min-width:18em'>" + content + "</div>",
                okValue: "确定",
                ok: function () {
                    var url = _this.href;
                    window.location = url;
                },
                cancelValue: "取消",
                cancel: function () {
                }
            });
            d.showModal();

            return false;
        });

        //全选
        //data-selector是目标对象jQuery选择器
        //<input type="checkbox" class="leaf-select-all" data-selector=":checkbox[name='id[]']" />
        $(".leaf-select-all").click(function () {
            var _this = this;
            var selector = $(this).attr("data-selector");
            $(selector).each(function () {
                this.checked = _this.checked;
            });
        });

        //批量操作
        $(".leaf-batch").click(function () {
            var $this = $(this);
            var url = $this.attr("href");

            //操作确认
            var content = this.hasAttribute("data-dialog-content") ? this.getAttribute("data-dialog-content") : "您确定执行此操作吗?";
            var title = "提示";

            //没有选择数据警告
            var warningMessage = "请选择需要操作的数据";

            var arr = $(".leaf-batch-item:checked");
            arr.push($(".leaf-batch-item:checked"));

            var data = "";

            var field = "id[]";
            arr.each(function () {
                data = (data == "") ? "" : (data + "&");
                data += field + "=" + this.value;
            });

            if (data == "") {
                leaf.alert(warningMessage);
                return false;
            }

            var d = dialog({
                zIndex: 2048,
                autofocus: false,
                title: title,
                content: "<div style='min-width:18em'>" + content + "</div>",
                okValue: "确定",
                ok: function () {
                    window.location = url + "?" + data;
                },
                cancelValue: "取消",
                cancel: function () {
                }
            });
            d.showModal();

            //阻止默认事件行为和事件冒泡
            return false;
        });

    });

})(jQuery);