<?php

use App\AI\Services\UsageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new UsageTrackingService();
});

test('track creates usage log with correct data', function () {
    $log = $this->service->track(
        feature: 'text_generation',
        provider: 'ollama',
        model: 'llama3.2:1b',
        promptTokens: 100,
        completionTokens: 50,
    );

    expect($log->feature)->toBe('text_generation');
    expect($log->prompt_tokens)->toBe(100);
    expect($log->completion_tokens)->toBe(50);
    expect($log->total_tokens)->toBe(150);
    expect($log->cost_usd)->toBe(0.0);

    $this->assertDatabaseHas('ai_usage_logs', [
        'feature' => 'text_generation',
        'total_tokens' => 150,
    ]);
});

test('track calculates cost correctly for paid models', function () {
    $log = $this->service->track(
        feature: 'text_generation',
        provider: 'openai',
        model: 'gpt-4o',
        promptTokens: 100,
        completionTokens: 50,
    );

    // 100 * 0.000005 + 50 * 0.000015 = 0.0005 + 0.00075 = 0.00125
    expect($log->cost_usd)->toBe(0.00125);
});
