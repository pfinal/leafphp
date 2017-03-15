<?php

// 应用基准目录
$app['path'] = dirname(__DIR__);

$app->register(new \Leaf\Provider\LogServiceProvider());
$app->register(new \Leaf\Provider\DatabaseServiceProvider());
$app->register(new \Leaf\Provider\TwigServiceProvider());
$app->register(new \Leaf\Provider\SessionProvider());
$app->register(new \Leaf\Provider\CaptchaProvider());
$app->register(new \Leaf\Provider\CacheProvider());

//中间件
$app['cors'] = 'Middleware\CorsMiddleware';
$app['auth'] = 'Middleware\AuthMiddleware';

//数据库连接配置
$app['db.config'] = array(
    'dsn' => 'mysql:host=localhost;dbname=test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'tablePrefix' => '',
);

if (file_exists(__DIR__ . '/app-local.php')) {
    require __DIR__ . '/app-local.php';
}
