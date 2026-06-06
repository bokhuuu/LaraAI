<?php

use App\Models\AiUsageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

test('stores monthly cost in cache when log is created', function () {
    AiUsageLog::create([
        'feature' => 'test',
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'prompt_tokens' => 1000,
        'completion_tokens' => 500,
        'total_tokens' => 1500,
        'cost_usd' => 0.0125,
    ]);

    $cached = Cache::get('ai_monthly_cost:'.now()->format('Y-m'));

    expect((float) $cached)->toBe(0.0125);
});

test('accumulates cost across multiple logs', function () {
    AiUsageLog::create([
        'feature' => 'test',
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'prompt_tokens' => 1000,
        'completion_tokens' => 500,
        'total_tokens' => 1500,
        'cost_usd' => 0.0125,
    ]);

    AiUsageLog::create([
        'feature' => 'test',
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'prompt_tokens' => 1000,
        'completion_tokens' => 500,
        'total_tokens' => 1500,
        'cost_usd' => 0.0125,
    ]);

    $cached = Cache::get('ai_monthly_cost:'.now()->format('Y-m'));

    expect((float) $cached)->toBe(0.025);
});
