<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return 'Login page placeholder';
})->name('login');

Route::get('/test-api-route', fn() => 'ok');
