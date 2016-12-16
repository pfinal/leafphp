<?php

use Leaf\Route;
use Leaf\View;

//编辑器示例
Route::any('ueditor-demo', function () {
    return View::render('editor.twig');
});

//编辑器文件上传支持
Route::any('ueditor', 'UeditorController@upload');