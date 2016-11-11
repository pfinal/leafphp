<?php

use Leaf\Route;
use Leaf\View;

Route::get('/', function () {
    return View::render('home.twig');
});