<?php

// composer class autoload
require __DIR__ . '/../vendor/autoload.php';

// creates its own scope and keep the global namespace clean
call_user_func(function () {

    $app = new \Leaf\Application();

    require __DIR__ . '/../config/app.php';

    $app->run();
});
