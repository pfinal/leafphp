<?php

use Leaf\Request;
use Leaf\Url;

/**
 * UEditor 辅助类
 * @author  Zou Yiliang
 * @since   1.0
 */
class UeditorController
{
    /**
     * ueditor上传入口
     * @param Request $request
     */
    public function upload(Request $request)
    {
        // 配置文件
        $path = Url::asset('static/ueditor/lib/php/config.json', true);

        // config.json文件有很多注释，json不支持注释，替换为空字符串
        $config = json_decode(preg_replace('/\/\*[\s\S]+?\*\//', '', file_get_contents($path)), true);

        $action = $request->get('action');
        switch ($action) {

            case 'config':
                return json_encode($config);

            case 'uploadimage':  // 上传图片
            case 'uploadscrawl': // 上传涂鸦
            case 'uploadvideo':  // 上传视频
            case 'uploadfile':   // 上传文件

                return self::doUpload($config);

            case 'listimage':   // 列出图片
            case 'listfile':    // 列出文件

                //action=listimage&start=0&size=20
                //action=listimage&start=20&size=20
                //action=listimage&start=40&size=20

                return self::listFile($action, $request->get('start', 0), $request->get('size', 20));

            case 'catchimage':  // 抓取远程文件

                $fieldName = $config['catcherFieldName'];
                $sources = $request->get($fieldName);

                $list = array();
                foreach ($sources as $url) {

                    //todo ...

                    /*$list[] = array(
                        "state" => 'SUCCESS', //未知错误、找不到上传文件...
                        "url" => '/uploads/201612/1481898560497033.jpg',
                        "size" => 3459,
                        "title" => '1481898560497033.jpg',
                        "original" => htmlspecialchars('原始文件名'),
                        "source" => htmlspecialchars($url)
                    );*/
                }

                return json_encode(array(
                    'state' => count($list) ? 'SUCCESS' : 'ERROR',
                    'list' => $list
                ));

                break;

            default:
                return json_encode(array(
                    'state' => '请求地址出错'
                ));
        }
    }

    /**
     * @param string $action 值为: "listimage" 或 "listfile"
     * @param $offset
     * @param $limit
     * @return string
     */
    protected function listFile($action, $offset, $limit)
    {
        $total = 0;

        $list = array(//array('url' => '/uploads/201612/1481898560497033.jpg', 'mtime' => 1481898560),
        );

        //todo ...

        if ($total == 0) {
            return json_encode(array(
                'state' => 'no match file',
                'list' => array(),
                'start' => $offset,
                'total' => $total,
            ));
        } else {
            return json_encode(array(
                'state' => "SUCCESS",
                'list' => $list,
                'start' => $offset,
                'total' => $total,
            ));
        }
    }

    /**
     * 处理上传
     * @param $config
     * @return string
     */
    protected function doUpload($config)
    {
        $upload = new \Leaf\UploadedFile();
        $upload->extensionName = [
            "png", "jpg", "jpeg", "gif", "bmp",
            "flv", "swf", "mkv", "avi", "rm", "rmvb", "mpeg", "mpg",
            "ogg", "ogv", "mov", "wmv", "mp4", "webm", "mp3", "wav", "mid",
            "rar", "zip", "tar", "gz", "7z", "bz2", "cab", "iso",
            "doc", "docx", "xls", "xlsx", "ppt", "pptx", "pdf", "txt", "md", "xml",
        ];
        if ($upload->doUpload($config['imageFieldName'])) {

            $file = $upload->getFile();

            $arr = array(
                'state' => 'SUCCESS',             // 上传状态，上传成功时必须返回"SUCCESS"
                'url' => $file['url'],
                'title' => $file['basename'],     //上传到服务器的文件名
                'original' => $file['name'],      //原文件名
                'type' => $file['type'],
                'size' => $file['size'],
            );
        } else {
            $error = $upload->getError();
            $arr = array(
                'state' => $error ? $error : '未知错误',
            );
        }
        return json_encode($arr);
    }
}