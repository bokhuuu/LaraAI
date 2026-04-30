<?php

use App\AI\Services\TextGenerationService;
use App\AI\Services\RateLimitingService;
use App\AI\Services\UsageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->service = new TextGenerationService(
        new UsageTrackingService(),
        new RateLimitingService()
    );
});

test('returns generated text', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('BMW X5 is a luxury SUV')
            ->withUsage(new Usage(10, 20))
    ]);

    $result = $this->service->generate('Describe BMW X5');

    expect($result)->toBe('BMW X5 is a luxury SUV');
});

test('tracks usage after generation', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('BMW X5 is a luxury SUV')
            ->withUsage(new Usage(10, 20))
    ]);

    $this->service->generate('Describe BMW X5');

    $this->assertDatabaseHas('ai_usage_logs', [
        'feature' => 'text_generation',
        'prompt_tokens' => 10,
        'completion_tokens' => 20,
    ]);
});

test('throws exception when rate limit exceeded', function () {
    Cache::put('ai_rate_limit:user_1:text_generation', 51, 3600);

    expect(fn() => $this->service->generate('Describe BMW X5', '', 'user_1'))
        ->toThrow(\RuntimeException::class, 'Rate limit exceeded');
});
