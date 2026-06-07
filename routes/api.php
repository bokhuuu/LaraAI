<?php

use App\Http\Controllers\AI\FeedbackController;
use App\Http\Controllers\AI\HealthCheckController;
use App\Http\Controllers\AI\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/ai/health', [HealthCheckController::class, 'check']);

Route::post('/ai/feedback', [FeedbackController::class, 'store']);

Route::post('/webhook', [WebhookController::class, 'receive']);
