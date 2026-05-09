<?php

use App\AI\Services\ConversationService;
use App\AI\Services\RateLimitingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->service = new ConversationService(new RateLimitingService());
});

test('startConversation creates conversation with system message', function () {
    $conversation = $this->service->startConversation('You are a helpful assistant.');

    expect($conversation->id)->not->toBeNull();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role'            => 'system',
        'content'         => 'You are a helpful assistant.',
    ]);
});

test('chat throws exception when rate limit exceeded', function () {
    $conversation = $this->service->startConversation('You are helpful.');

    Cache::put('ai_rate_limit:user_1:chat', 31, 3600);

    expect(fn() => $this->service->chat($conversation, 'Hello', 'user_1'))
        ->toThrow(\RuntimeException::class, 'Rate limit exceeded for chat');
});

test('chat saves user and assistant messages to database', function () {
    Prism::fake([
        TextResponseFake::make()
            ->withText('Hello! How can I help?')
            ->withUsage(new Usage(10, 20))
    ]);

    $conversation = $this->service->startConversation('You are helpful.');

    $this->service->chat($conversation, 'Hi there');

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role'            => 'user',
        'content'         => 'Hi there',
    ]);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role'            => 'assistant',
        'content'         => 'Hello! How can I help?',
    ]);
});
