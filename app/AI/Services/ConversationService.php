<?php

namespace App\AI\Services;

use App\Models\Conversation;
use App\Models\Message;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

/**
 * Manages back-and-forth chat conversations with memory.
 *
 * AI is stateless - it remembers nothing between calls.
 * This service stores every message in the DB and replays
 * the full history on each turn so the AI has context.
 *
 * Use for simple chat and Q&A. For tool calling or autonomous
 * agents use LarAgent instead.
 */
class ConversationService
{
    public function __construct(
        private RateLimitingService $rateLimiter
    ) {}

    /** Create a new conversation and set the AI's role via system prompt. */
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

    /** Save a single message to the conversation history. Role: 'user' | 'assistant' | 'system' */
    public function addMessage(Conversation $conversation, string $role, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
        ]);
    }

    /**
     * Load the full conversation history from DB as Prism message objects.
     * Called before every AI request to rebuild context from scratch.
     */
    public function getMessages(Conversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message) => match ($message->role) {
                'user' => new UserMessage($message->content),
                'assistant' => new AssistantMessage($message->content),
                'system' => new SystemMessage($message->content),
            })
            ->values()
            ->all();
    }

    /**
     * Handle one full conversation turn.
     * Saves the user message, sends full history to AI, saves and returns the response.
     */
    public function chat(Conversation $conversation, string $userMessage, string $userId = 'default'): string
    {
        if (! $this->rateLimiter->check('chat', $userId)) {
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
