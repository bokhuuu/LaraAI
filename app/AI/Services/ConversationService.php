<?php

namespace App\AI\Services;

use App\Models\Conversation;
use App\Models\Message;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;

class ConversationService
{
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

    public function addMessage(Conversation $conversation, string $role, string $content): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'role' => $role,
            'content' => $content,
        ]);
    }

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

    public function chat(Conversation $conversation, string $userMessage): string
    {
        $this->addMessage($conversation, 'user', $userMessage);

        $messages = $this->getMessages($conversation);

        $response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2:1b')
            ->withMessages($messages)
            ->asText();

        $this->addMessage($conversation, 'assistant', $response->text);

        return $response->text;
    }
}
