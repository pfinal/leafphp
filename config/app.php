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

//中间件
$app['auth'] = 'Middleware\AuthMiddleware';
$app['cors'] = 'Middleware\CorsMiddleware';
$app['csrf'] = 'Middleware\CsrfMiddleware';

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
