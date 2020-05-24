<?php

//应用基准目录
$app['path'] = dirname(__DIR__);

$app->register(new \Leaf\Provider\LogServiceProvider());
$app->register(new \Leaf\Provider\DatabaseServiceProvider());
$app->register(new \Leaf\Provider\CacheProvider());
$app->register(new \Leaf\Provider\QueueProvider());
$app->register(new \Leaf\Provider\TwigServiceProvider());
$app->register(new \Leaf\Provider\SessionProvider());

if (file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::create(__DIR__ . '/../')->load();
}

$app['route.cache'] = false; // 如果开启路由缓存，则不支持使用闭包路由
$app['debug'] = boolval(getenv('APP_DEBUG') ? getenv('APP_DEBUG') : '0');
$app['env'] = getenv('APP_ENV') ? getenv('APP_ENV') : 'local';

// 全局中间件
$app['middleware'] = array_merge($app['middleware'], ['Middleware\CorsMiddleware']);

//中间件
$app['auth.basic'] = 'Middleware\AuthWithBasicMiddleware';

//数据库连接配置
$app['db.config'] = array(
    'host' => getenv('DB_HOST') ? getenv('DB_HOST') : '127.0.0.1',
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
