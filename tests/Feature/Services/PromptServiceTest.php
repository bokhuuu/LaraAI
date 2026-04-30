<?php

use App\AI\Services\PromptService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new PromptService();
});

test('creates new version', function () {
    $prompt = $this->service->create('car_assistant', 'You are helpful', 'Initial');

    expect($prompt->version)->toBe(1);
    expect($prompt->is_active)->toBeTrue();
});

test('creates new version and deactivates old', function () {
    $v1 = $this->service->create('car_assistant', 'You are helpful', 'Initial');
    expect($v1->version)->toBe(1);
    expect($v1->is_active)->toBeTrue();

    $v2 = $this->service->create('car_assistant', 'You are expert', 'New');
    expect($v2->version)->toBe(2);
    expect($v2->is_active)->toBeTrue();

    $v1->refresh();
    expect($v1->is_active)->toBeFalse();
});

test('rollback restores previous version', function () {
    $v1 = $this->service->create('car_assistant', 'You are helpful', 'Initial');
    $v2 = $this->service->create('car_assistant', 'You are expert', 'New');

    $this->service->rollback('car_assistant');

    $v1->refresh();
    $v2->refresh();

    expect($v1->is_active)->toBeTrue();
    expect($v2->is_active)->toBeFalse();
});

test('returns all versions ordered', function () {

    $this->service->create('car_assistant', 'You are helpful', 'Initial');
    $this->service->create('car_assistant', 'You are expert', 'New');
    $this->service->create('car_assistant', 'You are guru', 'Newest');

    $history = $this->service->getHistory('car_assistant');

    expect($history->first()->version)->toBe(3);
    expect($history->last()->version)->toBe(1);
    expect($history)->toHaveCount(3);
});

test('returns active prompt content', function () {
    $this->service->create('car_assistant', 'You are helpful', 'Initial');
    $this->service->create('car_assistant', 'You are expert', 'New');

    $content = $this->service->get('car_assistant', 'Prompt not found');

    expect($content)->toBe('You are expert');
});
