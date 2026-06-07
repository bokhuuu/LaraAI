<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores positive feedback', function () {
    $response = $this->postJson('/api/ai/feedback', [
        'vote' => 1,
        'response_text' => 'Here are some cars under $20k...',
        'comment' => 'Very helpful!',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'id']);

    $this->assertDatabaseHas('ai_feedback', [
        'vote' => 1,
        'response_text' => 'Here are some cars under $20k...',
        'comment' => 'Very helpful!',
    ]);
});

it('stores negative feedback without comment', function () {
    $response = $this->postJson('/api/ai/feedback', [
        'vote' => -1,
        'response_text' => 'That answer was wrong.',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('ai_feedback', [
        'vote' => -1,
        'comment' => null,
    ]);
});

it('rejects invalid vote', function () {
    $response = $this->postJson('/api/ai/feedback', [
        'vote' => 5,
        'response_text' => 'Some response.',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.vote.0', 'Vote must be 1 (positive) or -1 (negative).');
});

it('rejects missing response text', function () {
    $response = $this->postJson('/api/ai/feedback', [
        'vote' => 1,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.response_text.0', 'Response text is required.');
});
