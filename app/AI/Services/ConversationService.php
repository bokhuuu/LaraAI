<?php

namespace App\AI\Services;

use App\Models\Conversation;
use App\Models\Message;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use App\AI\Services\RateLimitingService;

/**
 * ConversationService
 *
 * Manages stateful AI conversations without LarAgent agent loop.
 * Use when you need simple chat without tool calling or autonomous decisions.
 *
 * - startConversation(): creates conversation + system prompt
 * - addMessage(): saves any message to history
 * - getMessages(): loads history as Prism message objects
 * - chat(): full conversation turn with rate limiting
 *
 * TEMPLATE USAGE: Use for simple chatbots and Q&A flows.
 * For agents with tool calling, use LarAgent instead.
 */
class ConversationService
{
    public function __construct(
        private RateLimitingService $rateLimiter
    ) {}

    /** Create new conversation session with system prompt as first message. */
    public function startConversation(string $systemPrompt): Conversation
    {
        $conversation = Conversation::create();

        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'system',
            'content' => $systemPrompt,
        ]);

        return $conversation;
    }

    /** Save a message to conversation history. Role: 'user' | 'assistant' | 'system' */
    public function addMessage(Conversation $conversation, string $role, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
        ]);
    }

    /** Load full conversation history as Prism message objects for AI consumption. */
    public function getMessages(Conversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn(Message $message) => match ($message->role) {
                'user' => new UserMessage($message->content),
                'assistant' => new AssistantMessage($message->content),
                'system' => new SystemMessage($message->content),
            })
            ->values()
            ->all();
    }

    /**
     * Execute one full conversation turn with rate limiting:
     * save user message → load history → call AI → save response → return text.
     *
     * @throws \RuntimeException When rate limit exceeded
     */
    public function chat(Conversation $conversation, string $userMessage, string $userId = 'default'): string
    {
        if (!$this->rateLimiter->check('chat', $userId)) {
            throw new \RuntimeException('Rate limit exceeded for chat');
        }

        $this->addMessage($conversation, 'user', $userMessage);

        $messages = $this->getMessages($conversation);

        $response = Prism::text()
            ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
            ->withMessages($messages)
            ->asText();

        $this->addMessage($conversation, 'assistant', $response->text);

        $this->rateLimiter->increment('chat', $userId);

        return $response->text;
    }
}
