<?php

use App\Http\Controllers\AI\StreamingController;
use Illuminate\Support\Facades\Route;

// Landing page - replace with your domain homepage or remove
Route::get('/', function () {
    return view('welcome');
});

// AI endpoints
Route::get('/stream', [StreamingController::class, 'stream'])->name('ai.stream');
Route::get('/chat', fn() => view('ai.chat'))->name('ai.chat');
