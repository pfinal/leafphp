# popup
好用的webapp弹出层

# 参数
```javascript
     $.popup({
              title: "", // string
              content: "内容...", // string | jq对象
              button: [
                  {
                      "text": "是",
                      "callback": function (event, popup) {
                          popup.close()
                      }
                  },
                  {
                      "text": "否",
                      "callback": function (event, popup) {
                          popup.close()
                      }
                  }
              ],
              width: " 90%", //"300px" | 50% 设置主体宽度
              height: "auto", //"300px" | 50%\ auto 设置主体高度
              modal: false, //true 模态 | false 非模态（点击遮罩不移除弹出层）
              className: "",//添加自定义样式名
              callback: {
                  "clickCallbackExample": function (event, popup) {//在属于popup内部dom上自定义data-callback="clickCallbackExample"属性,点击后会回调执行这里
                  }
              }
          }).show()

```
