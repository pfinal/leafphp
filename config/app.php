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

if (file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::create(__DIR__ . '/../')->load();
}

$app->registerBundle(new \AppBundle\AppBundle());

//如果开启路由缓存，则不支持使用闭包路由
$app['route.cache'] = false;

// 全局中间件
$app['middleware'] = array_merge($app['middleware'], ['Middleware\CorsMiddleware']);

//中间件
$app['auth'] = 'Middleware\AuthMiddleware';
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
    'host' => getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost',
    'database' => getenv('DB_DATABASE') ? getenv('DB_DATABASE') : 'test',
    'username' => getenv('DB_USERNAME') ? getenv('DB_USERNAME') : 'root',
    'password' => getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : '',
    'tablePrefix' => getenv('DB_PREFIX') ? getenv('DB_PREFIX') : '',
    'port' => getenv('DB_PORT') ? getenv('DB_PORT') : 3306,
    'charset' => 'utf8mb4',
);

if (file_exists(__DIR__ . '/app-local.php')) {
    require __DIR__ . '/app-local.php';
}

//事件
include __DIR__ . '/event.php';
