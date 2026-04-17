<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/stream', [App\Http\Controllers\StreamingController::class, 'stream']);

Route::get('/stream-demo', function () {
    return view('ai.stream-demo');
});
