<?php

//php crontab.php start
//php crontab.php start -d
//php crontab.php start status

//composer require workerman/workerman
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use \Workerman\Lib\Timer;

Worker::$logFile = __DIR__ . '/runtime/crontab.log';
Worker::$pidFile = __DIR__ . '/runtime/crontab.pid';

$task = new Worker();

$task->onWorkerStart = function ($task) {

    //每60秒执行一次
    Timer::add(60, function () {
        exec('php ' . __DIR__ . '/console crontab', $out);
    });
};

Worker::runAll();
