<?php /** @noinspection ALL */

//php console migrate:create Demo

$dbConfig = \Leaf\Application::$app['db.config'];

if (isset($dbConfig['dsn'])) {

    // mysql:host=127.0.0.1;port=3306;dbname=test

    if (preg_match('/dbname=(.*)/', $dbConfig['dsn'], $matches)) {
        $dbConfig['database'] = $matches[1];
    } else {
        throw new \Exception('database error');
    }

    if (preg_match('/host=(.*?);/', $dbConfig['dsn'], $matches)) {
        $dbConfig['host'] = $matches[1];
    } else {
        throw new \Exception('host error');
    }

    if (preg_match('/port=(\d+)/', $dbConfig['dsn'], $matches)) {
        $dbConfig['port'] = $matches[1];
    }
}

return array(
    'paths' => array(
        'migrations' => 'database/migrations',
        'seeds' => 'database/seeds',
    ),
    'environments' => array(
        'default_migration_table' => 'migrations',
        'default_database' => 'prod',
        'prod' => array(
            'adapter' => 'mysql',
            'host' => $dbConfig['host'],
            'port' => isset($dbConfig['port']) ? $dbConfig['port'] : 3306,
            'name' => $dbConfig['database'],
            'user' => $dbConfig['username'],
            'pass' => $dbConfig['password'],
            'charset' => 'utf8mb4',
        ),
    )
);
