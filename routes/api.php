<?php

use App\Http\Controllers\AI\HealthCheckController;
use Illuminate\Support\Facades\Route;

// AI health check endpoint — used by monitoring tools
Route::get('/ai/health', [HealthCheckController::class, 'check']);
