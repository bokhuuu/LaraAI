<?php

use Illuminate\Support\Facades\Route;

// Default Laravel welcome page
Route::get('/', function () {
    return view('welcome');
});

// AI streaming
Route::get('/stream', [App\Http\Controllers\AI\StreamingController::class, 'stream']);
Route::get('/chat', fn() => view('ai.chat'));
