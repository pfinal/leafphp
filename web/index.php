<?php

// composer class autoload
$loader = require __DIR__ . '/../vendor/autoload.php';

// create app
$app = new \Leaf\Application();

// config
require __DIR__ . '/../config/app.php';

$app->run();