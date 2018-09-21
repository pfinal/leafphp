<?php

$dbConfig = \Leaf\Application::$app['db.config'];

//提取主机和数据库名
if (isset($dbConfig['dsn'])) {
    if (preg_match('/dbname=(.*)/', $dbConfig['dsn'], $arr)) {
        $dbConfig['database'] = $arr[1];
    } else {
        throw new \Exception('database error');
    }

    if (preg_match('/host=(.*?);/', $dbConfig['dsn'], $arr)) {
        $dbConfig['host'] = $arr[1];
    } else {
        throw new \Exception('host error');
    }
}

return array(
    "paths" => array(
        "migrations" => "database/migrations",
        "seeds" => "database/seeds"
    ),
    "environments" => array(
        "default_migration_table" => "migrations",
        "default_database" => "prod",
        "prod" => array(
            "adapter" => "mysql",
            "host" => $dbConfig["host"],
            "name" => $dbConfig["database"],
            "user" => $dbConfig["username"],
            "pass" => $dbConfig["password"],
            "charset" => 'utf8',
        ),
    )
);