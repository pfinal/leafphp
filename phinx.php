<?php /** @noinspection ALL */

//php console migrate:create Demo

$dbConfig = \Leaf\Application::$app['db.config'];

if (isset($dbConfig['dsn'])) {
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
            'name' => $dbConfig['database'],
            'user' => $dbConfig['username'],
            'pass' => $dbConfig['password'],
            'charset' => 'utf8mb4',
        ),
    )
);