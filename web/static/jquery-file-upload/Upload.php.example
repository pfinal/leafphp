<?php

namespace Service;

use Leaf\Application;
use Leaf\Session;
use Leaf\UploadedFile;
use Leaf\Url;
use Leaf\Util;

/**
 * 文件上传
 *
 * use \Service\Upload;
 *
 * //上传到临时目录
 * $config = [
 *     'thumb' => [
 *         'm' => array('w' => 400, 'h' => 400, 'resize' => true),
 *     ],
 * ];
 * return Json::render($this->uploadToTemp('file', $config));
 *
 * //提交表单时，移动到正式目录
 * $this->moveFromTemp($fileKey, 'uploads/avatar')
 *
 * @author  Zou Yiliang
 */
trait Upload
{
    /**
     * 上传文件到临时目录
     * @param string $name
     * @param array $config
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

        //预览用临时图片
        $config['thumb'] = $config['thumb'] + array('_temp' => array('w' => 120, 'h' => 120, 'resize' => true));

        $up = new UploadedFile($config);

        if ($up->doUpload($name)) {

            //上传后的文件信息
            $file = $up->getFile();

            //生成fileKey传递给前端
            $file['fileKey'] = Util::guid();

            //如果是api，注意不要使用session
            Session::setFlash($file['fileKey'], $file);
            Session::setFlash($file['fileKey'] . '.config', $config);

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

        if (empty($fileKey) || ($fileInfo = Session::getFlash($fileKey)) == null) {
            return '';
        }

        $config = Session::getFlash($fileKey . '.config');

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
}
