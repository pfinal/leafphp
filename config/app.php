<?php

//应用基准目录
$app['path'] = dirname(__DIR__);

$app->register(new \Leaf\Provider\LogServiceProvider());
$app->register(new \Leaf\Provider\DatabaseServiceProvider());
$app->register(new \Leaf\Provider\TwigServiceProvider());
$app->register(new \Leaf\Provider\SessionProvider());
$app->register(new \Leaf\Provider\CaptchaProvider());
$app->register(new \Leaf\Provider\CacheProvider());
$app->register(new \Leaf\Provider\QueueProvider());

//如果开启路由缓存，则不支持使用闭包路由
$app['route.cache'] = false;

// 全局中间件
$app['middleware'] = array_merge($app['middleware'], ['Middleware\CorsMiddleware']);

//中间件
$app['auth'] = 'Middleware\AuthMiddleware';
$app['cors'] = 'Middleware\CorsMiddleware';
$app['csrf'] = 'Middleware\CsrfMiddleware';

//模板中获取当前登录用户 {{app.user.username}}
$app['twig.app'] = $app->extend('twig.app', function ($twigApp, $app) {
    $twigApp['user'] = function () {
        return \Service\Auth::getUser();
    };
    return $twigApp;
});

//数据库连接配置
$app['db.config'] = array(
    'host' => 'localhost',
    'database' => 'test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'tablePrefix' => '',
);

if (file_exists(__DIR__ . '/app-local.php')) {
    require __DIR__ . '/app-local.php';
}

//事件
include __DIR__ . '/event.php';
