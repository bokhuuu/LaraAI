<?php

use Illuminate\Support\Facades\Route;

// Default Laravel welcome page
Route::get('/', function () {
    return view('welcome');
});

// AI streaming demonstration
Route::get('/stream', [App\Http\Controllers\AI\StreamingController::class, 'stream']);
Route::get('/stream-demo', function () {
    return view('ai.stream-demo');
});
