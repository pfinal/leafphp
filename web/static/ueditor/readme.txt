下载ueditor到lib中，将php目录所有php文件，重命名为.bak

问题描述：最新版 Google Chrome 浏览器，点击上传图片时，等待非常久（重复点击会出现N个文件浏览对话框）
解决方案：通过js创建input file元素的时候accept属性为"image/*",这个地方修改为"image/jpeg,image/png"就可以正常弹出了

所以，使用修改后的ueditor.all.js文件，删除 ueditor.all.min.js
