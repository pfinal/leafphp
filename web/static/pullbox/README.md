# pull-refresh
webapp 下拉刷新


<img width="200" src="https://raw.githubusercontent.com/huaable/pull-refresh/master/Screenshot_2015-08-11-22-43-03.png"/>
<img width="200" src="https://raw.githubusercontent.com/huaable/pull-refresh/master/Screenshot_2015-08-11-22-43-13.png"/>
<img width="200" src="https://raw.githubusercontent.com/huaable/pull-refresh/master/Screenshot_2015-08-11-22-43-22.png"/>



HTML
```

<body>
    <!--以下内容均为非必要,可自定义-->
    <div class="pull-refresh"></div>
    <ul id="content">
        ...
    </ul>
    <div class="pull-loadmore"></div>
</body>


```
###pull-refresh

add HTML
```
<div class="pull-refresh"></div>
```
add JavaScript
```

    //添加下拉刷新功能 仅需定义该函数

      var opt = {
        onReFresh:function (handle) {
                setTimeout(function () {//ajax
                    handle.finish();
                }, 500);
            }
       };
       pullbox(opt)

```

### pull-loadmore

add HTML

```
    <div class="pull-loadmore"></div>

```

add Javascript
```
    //添加加载更多功能 仅需定义该函数
   var opt = {
    onLoadMore:function (handle) {
            setTimeout(function () {//ajax
                handle.finish();
            }, 500);
        }
   };
    pullbox(opt)
```

### option

```
     {
        'boxSelector': 'body', // selector like '#demo'  '.demo'
        'reFreshDistance': 60,
        'loadMoreDistance': 100
		'onPull': null,
		'onReFresh': null,//添加下拉刷新功能 仅需定义该函数
		'onLoadMore': null//添加加载更多功能 仅需定义该函数
	}

```
