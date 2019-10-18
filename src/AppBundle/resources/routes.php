<?php

use Leaf\Route;

Route::group(['middleware' => ['auth', 'csrf']], function () {
    
});