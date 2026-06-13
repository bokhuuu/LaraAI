<?php

use App\AI\Services\TokenBudgetService;
use App\Models\AiUsageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

test('check returns true when under monthly token budget', function () {
    AiUsageLog::create([
        'feature' => 'test',
        'provider' => 'ollama',
        'model' => 'llama3.2:1b',
        'prompt_tokens' => 1000,
        'completion_tokens' => 500,
        'total_tokens' => 1500,
        'cost_usd' => 0.0,
    ]);

    config(['ai.token_budget.monthly_limit' => 10000]);
    config(['ai.token_budget.enabled' => true]);

    $service = app(TokenBudgetService::class);

    expect($service->check())->toBeTrue();
});

test('check returns false when monthly token budget is exceeded', function () {
    AiUsageLog::create([
        'feature' => 'test',
        'provider' => 'ollama',
        'model' => 'llama3.2:1b',
        'prompt_tokens' => 8000,
        'completion_tokens' => 2000,
        'total_tokens' => 10000,
        'cost_usd' => 0.0,
    ]);

    config(['ai.token_budget.monthly_limit' => 5000]);
    config(['ai.token_budget.enabled' => true]);

    $service = app(TokenBudgetService::class);

    expect($service->check())->toBeFalse();
});

test('check always returns true when budget is disabled', function () {
    AiUsageLog::create([
        'feature' => 'test',
        'provider' => 'ollama',
        'model' => 'llama3.2:1b',
        'prompt_tokens' => 8000,
        'completion_tokens' => 2000,
        'total_tokens' => 10000,
        'cost_usd' => 0.0,
    ]);

    config(['ai.token_budget.monthly_limit' => 100]);
    config(['ai.token_budget.enabled' => false]);

    $service = app(TokenBudgetService::class);

    expect($service->check())->toBeTrue();
});

test('remaining returns correct tokens left in budget', function () {
    AiUsageLog::create([
        'feature' => 'test',
        'provider' => 'ollama',
        'model' => 'llama3.2:1b',
        'prompt_tokens' => 3000,
        'completion_tokens' => 2000,
        'total_tokens' => 5000,
        'cost_usd' => 0.0,
    ]);

    config(['ai.token_budget.monthly_limit' => 10000]);
    config(['ai.token_budget.enabled' => true]);

    $service = app(TokenBudgetService::class);

    expect($service->remaining())->toBe(5000);
});
