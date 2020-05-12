<?php

use Leaf\Route;
use Leaf\View;

Route::get('/', function () {
    return View::render('home.twig');
});

Route::get('/doc', function () {

    $controllers = array(
        'Util\Doc',
        // 'ApiBundle\Controller\UserController',
    );

    return \Util\Doc::makeDoc($controllers);
});
