<?php

use App\AI\Services\AbTestService;
use App\Models\AbPromptResult;
use App\Models\AbPromptTest;
use App\Models\PromptVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    PromptVersion::create([
        'key' => 'prompt_a',
        'content' => 'You are a friendly assistant.',
        'version' => 1,
        'is_active' => true,
    ]);

    PromptVersion::create([
        'key' => 'prompt_b',
        'content' => 'You are an expert advisor.',
        'version' => 1,
        'is_active' => true,
    ]);

    AbPromptTest::create([
        'name' => 'test_assistant_tone',
        'prompt_key_a' => 'prompt_a',
        'prompt_key_b' => 'prompt_b',
        'traffic_split' => 50,
        'is_active' => true,
    ]);
});

it('assigns a variant and returns the correct prompt for a session', function () {
    $service = app(AbTestService::class);

    $prompt = $service->getPromptForSession('session-123');

    expect($prompt)->toBeIn([
        'You are a friendly assistant.',
        'You are an expert advisor.',
    ]);

    expect(AbPromptResult::where('session_id', 'session-123')->exists())->toBeTrue();
});

it('returns the same variant for the same session', function () {
    $service = app(AbTestService::class);

    $first = $service->getPromptForSession('session-456');
    $second = $service->getPromptForSession('session-456');

    expect($first)->toBe($second);
    expect(AbPromptResult::where('session_id', 'session-456')->count())->toBe(1);
});

it('records a vote against the session variant', function () {
    $service = app(AbTestService::class);

    $service->getPromptForSession('session-789');
    $service->recordVote('session-789', 1);

    expect(AbPromptResult::where('session_id', 'session-789')->value('vote'))->toBe(1);
});

it('returns null when no active test exists', function () {
    AbPromptTest::query()->update(['is_active' => false]);

    $service = app(AbTestService::class);
    $prompt = $service->getPromptForSession('session-000');

    expect($prompt)->toBeNull();
});

it('calculates correct results for a test', function () {
    $service = app(AbTestService::class);
    $test = AbPromptTest::first();

    AbPromptResult::create([
        'ab_prompt_test_id' => $test->id,
        'session_id' => 'session-1',
        'variant' => 'A',
        'prompt_key' => 'prompt_a',
        'vote' => 1,
    ]);

    AbPromptResult::create([
        'ab_prompt_test_id' => $test->id,
        'session_id' => 'session-2',
        'variant' => 'A',
        'prompt_key' => 'prompt_a',
        'vote' => -1,
    ]);

    AbPromptResult::create([
        'ab_prompt_test_id' => $test->id,
        'session_id' => 'session-3',
        'variant' => 'B',
        'prompt_key' => 'prompt_b',
        'vote' => 1,
    ]);

    $results = $service->getResults('test_assistant_tone');

    expect($results['A']['positive_rate'])->toBe(50.0);
    expect($results['B']['positive_rate'])->toBe(100.0);
});
