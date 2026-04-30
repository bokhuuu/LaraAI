<?php

use App\AI\Services\RateLimitingService;

use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->service = new RateLimitingService();
});

test('check returns true when under limit', function () {
    $result = $this->service->check('text_generation', 'user_1');
    expect($result)->toBeTrue();
});

test('check returns false when limit exceeded', function () {
    Cache::put('ai_rate_limit:user_1:text_generation', 51, 3600);

    $result = $this->service->check('text_generation', 'user_1');
    expect($result)->toBeFalse();
});

test('increment increases counter', function () {
    $this->service->increment('text_generation', 'user_1');
    $this->service->increment('text_generation', 'user_1');

    $remaining = $this->service->remaining('text_generation', 'user_1');
    expect($remaining)->toBe(48);
});

test('remaining returns correct count', function () {
    $remaining = $this->service->remaining('text_generation', 'user_1');
    expect($remaining)->toBe(50);
});

test('different users have separate counters', function () {
    $this->service->increment('text_generation', 'user_1');
    $this->service->increment('text_generation', 'user_1');

    $user1remaining = $this->service->remaining('text_generation', 'user_1');
    $user2remaining = $this->service->remaining('text_generation', 'user_2');

    expect($user1remaining)->toBe(48);
    expect($user2remaining)->toBe(50);
});
