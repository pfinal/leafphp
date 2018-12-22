<?php

namespace Service;

use Leaf\Application;
use Leaf\Cache;
use Leaf\Json;
use Leaf\Log;
use Leaf\Request;
use Leaf\UploadedFile;
use Leaf\Url;
use Leaf\Util;
use Middleware\CsrfMiddleware;

/**
 * 文件上传
 *
 * use \Service\UploadTrait;
 *
 * //上传到临时目录
 * return Json::render($this->uploadToTemp('file'));
 *
 * //提交表单时，移动到正式目录
 * $this->moveFromTemp($fileKey, 'uploads/avatar')
 *
 * //配置存储目录 先安装组件 composer require pfinal/storage
 * $app['storage'] = function () use ($app) {
 *   return new \PFinal\Storage\Local([
 *       'basePath' => $app['path'] . '/web/',
 *       'baseUrl' => \Leaf\Url::asset('/', true),
 *   ]);
 * };
 *
 * @author  Zou Yiliang
 */
trait UploadTrait
{
    /**
     * 上传文件到临时目录
     * @param string $name
     * @param array $config 例如
     * $config = [
     *     'thumb' => [
     *         'm' => array('w' => 400, 'h' => 400, 'resize' => true),
     *     ],
     * ];
     * @return array
     */
    protected function uploadToTemp($name = 'file', $config = array())
    {
        $config += array(
            'basePath' => 'temp',
            'rootPath' => Application::$app['path'] . '/web/',
            'baseUrl' => Url::asset('/'),
            'thumb' => array(),
        );

        if (isset(Application::$app['storage'])) {
            $config['basePath'] = 'temp';
            $config['rootPath'] = Application::$app->getRuntimePath() . '/';
        } else {
            //预览用临时图片
            $config['thumb'] = $config['thumb'] + array('_temp' => array('w' => 120, 'h' => 120, 'resize' => true));
        }

        $up = new UploadedFile($config);

        if ($up->doUpload($name)) {

            //上传后的文件信息
            $file = $up->getFile();

            //生成fileKey传递给前端
            $file['fileKey'] = Util::guid();

            //如果是api，注意不要使用session
            //Session::setFlash($file['fileKey'], $file);
            //Session::setFlash($file['fileKey'] . '.config', $config);
            Cache::set($file['fileKey'], $file, 60 * 10);
            Cache::set($file['fileKey'] . '.config', $config, 60 * 10);

            //云存储
            if (isset(Application::$app['storage'])) {
                $tempFile = rtrim($config['rootPath'], '/\\') . DIRECTORY_SEPARATOR . rtrim($file['basePath'], '/\\') . DIRECTORY_SEPARATOR . $file['name'];
                $bool = Application::$app['storage']->put($file['basePath'] . $file['name'], file_get_contents($tempFile));
                $file['url'] = Application::$app['storage']->url($file['basePath'] . $file['name']);
                $file['thumbnailUrl'] = ['_temp' => $file['url']];

                unlink($tempFile);

                if ($bool) {
                    return array('status' => true, 'file' => $file);
                } else {
                    Log::debug(Application::$app['storage']->error());
                    return array('status' => false, 'message' => Application::$app['storage']->error());
                }
            }

            //{"status":true,"file":{"originalName":"touxiang2.jpg","name":"201708/16/5993ad13c9823.jpg","basename":"5993ad13c9823.jpg","basePath":"temp/","subPath":"201708/16/","size":179327,"type":"image/jpeg","url":"/yuntu/web/temp/201708/16/5993ad13c9823.jpg","thumbnailUrl":{"_temp":"/yuntu/web/temp/201708/16/_temp/5993ad13c9823.jpg"},"fileKey":"1011A64B-CE45-6744-C326-1BE83FDD3AA3"}}
            return array('status' => true, 'file' => $file);

        } else {
            return array('status' => false, 'message' => $up->getError());
        }
    }

    /**
     * 从临时目录移动图片到指定目录
     * @param $fileKey
     * @param string $baseDir
     * @return string
     */
    protected function moveFromTemp($fileKey, $dir = 'uploads')
    {
        $fileKey = trim($fileKey);

        //if (empty($fileKey) || ($fileInfo = Session::getFlash($fileKey)) == null) {
        //    return '';
        //}

        if (empty($fileKey) || ($fileInfo = Cache::get($fileKey)) == null) {
            return '';
        }

        $config = Cache::get($fileKey . '.config');
        Cache::delete($fileKey);
        Cache::delete($fileKey . '.config');

        //云存储
        if (isset(Application::$app['storage'])) {
            $bool = Application::$app['storage']->rename($fileInfo['basePath'] . $fileInfo['name'], rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $fileInfo['name']);
            if ($bool) {
                return $fileInfo['name'];
            } else {
                Log::debug(Application::$app['storage']->error());
                return '';
            }
        }

        $tempRoot = rtrim($config['rootPath'], '/\\') . DIRECTORY_SEPARATOR . rtrim($config['basePath'], '/\\') . DIRECTORY_SEPARATOR;
        $publishRoot = rtrim($config['rootPath'], '/\\') . DIRECTORY_SEPARATOR;

        $fileName = $fileInfo['name'];

        //图片目录
        $baseDir = $publishRoot . rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;

        //创建目录并移动文件
        Util::createDirectory(dirname($baseDir . $fileName));
        rename($tempRoot . $fileName, $baseDir . $fileName);

        //删除预览用临时图片
        unlink($tempRoot . dirname($fileName) . DIRECTORY_SEPARATOR . '_temp' . DIRECTORY_SEPARATOR . basename($fileName));
        unset($config['thumb']['_temp']);

        //移动缩略图
        foreach ($config['thumb'] as $subDir => $thumbRule) {
            $tempFile = dirname($fileName) . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . basename($fileName);
            $file = dirname($fileName) . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . basename($fileName);
            Util::createDirectory(dirname($baseDir . $file));
            rename($tempRoot . $tempFile, $baseDir . $file);
        }

        return $fileName;
    }

    /**
     * 从手机上传
     *
     * 1. 添加生成二维码的组件
     * composer require bacon/bacon-qr-code
     *
     * 2. 加一个路由，对应js中的 mobileUploadUrl，代码如下
     * 注意 要清除登录验证和Csrf中间件 @ClearMiddleware auth|csrf
     * public function mobileUpload(Request $request)
     * {
     *     return $this->fromMobile($request);
     * }
     *
     * 3. 表单中加一个按扭用于显示二维码
     * <a data-show=".images1" style="position: relative;top:14px;margin-left:20px;" href="javascript:;" class="js-from-mobile"> <span class="glyphicon glyphicon-camera"></span> 从手机上传</a>
     *
     * 4. js参考 upload.twig.example
     *
     * @param Request $request
     * @return string
     */
    public function fromMobile($request)
    {
        ///pc 生成二维码
        if ($request->get('action') == 'qrcode') {

            //未登录上传时验证
            Cache::set('AllowUpload:' . CsrfMiddleware::getTokenFromSession(), true, 60 * 30);

            $renderer = new \BaconQrCode\Renderer\Image\Png();
            $renderer->setHeight(256);
            $renderer->setWidth(256);
            $writer = new \BaconQrCode\Writer($renderer);

            $url = $request->get('url');
            $qrcode = 'data:image/png;base64,' . base64_encode($writer->writeString($url));
            return '<center><div>请使用手机扫码</div><img src="' . $qrcode . '"></center>';
        }

        ///pc ajax
        if ($request->isXmlHttpRequest()) {
            $fileInfo = Cache::get($request->get('tag'));
            if ($fileInfo == null) {
                return Json::render(['status' => false]);
            }

            //Session::setFlash($fileInfo['file']['fileKey'], $fileInfo['file']);
            Cache::set($fileInfo['file']['fileKey'], $fileInfo['file'], 60 * 10);
            return Json::render($fileInfo);
        }

        ///mobile

        //安全检查
        if (!Cache::get('AllowUpload:' . CsrfMiddleware::getTokenFromRequest($request))) {
            return Json::render(['status' => false, 'message' => '_token无效']);
        }

        //接收上传的文件
        if ($request->isMethod('post')) {

            $fileInfo = $this->uploadToTemp('file');
            if ($fileInfo['status']) {
                Cache::set($request->get('tag'), $fileInfo, 60 * 60);
            }
            return Json::render($fileInfo);
        }

        //移动端视图
        return $this->getMobileView($request->getRequestUri());

    }

    protected function getMobileView($uploadUri)
    {
        return <<<HTML
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="https://res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css">
    <title>文件上传</title>
</head>
<body>
<div class="weui-tab__content upload-area" style="display: block;">
    <div class="weui-cells weui-cells_form" id="uploaderCustom">
        <div class="weui-cell">
            <div class="weui-cell__bd">
                <div class="weui-uploader">
                    <div class="weui-uploader__hd"><p class="weui-uploader__title">请点选择图片或拍照</p></div>
                    <div class="weui-uploader__bd">
                        <ul class="weui-uploader__files" id="uploaderCustomFiles">
                            <!--<li class="weui-uploader__file" style="background-image: url('https://www.baidu.com/img/baidu_jgylogo3.gif');"></li>-->
                        </ul>
                        <div class="weui-uploader__input-box">
                            <input id="uploaderCustomInput"  class="weui-uploader__input" type="file" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="weui-btn-area">
        <a id="uploaderCustomBtn" href="javascript:" class="weui-btn weui-btn_primary">上传</a>
    </div>
</div>

<div class="weui-msg" style="display: none;">
    <div class="weui-msg__icon-area"><i class="weui-icon-success weui-icon_msg"></i></div>
    <div class="weui-msg__text-area">
        <h2 class="weui-msg__title">上传成功</h2>
        <p class="weui-msg__desc">请到电脑上继续操作</p>
    </div>
</div>

<script type="text/javascript" src="https://res.wx.qq.com/open/libs/weuijs/1.1.3/weui.min.js"></script>
<script>
    //https://github.com/Tencent/weui.js/blob/master/docs/component/uploader.md
    var uploadCustomFileList = [];
    weui.uploader('#uploaderCustom', {
        url: '{$uploadUri}',
        auto: false,
        onQueued: function() {
            uploadCustomFileList.push(this);
        },
        onBeforeQueued:function() {
           if(uploadCustomFileList.length > 0){
               weui.alert("一次只能上传一张图片")
               return false
           }
        },
        onSuccess: function (ret) {
            console.log(this, ret);
            if (ret.status) {
                document.querySelector(".weui-msg").style.display = ""
                document.querySelector(".upload-area").style.display = "none"
            }else{
                weui.alert("上传失败 " + ret.message)
            }
            // return true; // 阻止默认行为，不使用默认的成功态
        },
        onError: function (err) {
            //console.log(this, err);
            //return true; // 阻止默认行为，不使用默认的失败态
            weui.alert(err)
        }
    });

    // 手动上传按钮
    document.getElementById("uploaderCustomBtn").addEventListener('click', function () {
        uploadCustomFileList.forEach(function (file) {
            file.upload();
        });
    });

    // 缩略图预览
    document.querySelector('#uploaderCustomFiles').addEventListener('click', function (e) {
        var target = e.target;

        while (!target.classList.contains('weui-uploader__file') && target) {
            target = target.parentNode;
        }
        if (!target) return;

        var url = target.getAttribute('style') || '';
        var id = target.getAttribute('data-id');

        if (url) {
            url = url.match(/url\((.*?)\)/)[1].replace(/"/g, '');
        }
        var gallery = weui.gallery(url, {
            onDelete: function onDelete() {
                weui.confirm('确定删除该图片？', function () {
                    var index;
                    for (var i = 0, len = uploadCustomFileList.length; i < len; ++i) {
                        var file = uploadCustomFileList[i];
                        if (file.id == id) {
                            index = i;
                            break;
                        }
                    }
                    if (index) uploadCustomFileList.splice(index, 1);

                    target.remove();
                    gallery.hide();
                    
                });
            }
        });
    });
</script>
</body>
</html>
HTML;
    }
}
