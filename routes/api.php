<?php

use App\Http\Controllers\AI\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/ai/health', [HealthCheckController::class, 'check']);
