<?php

namespace App\Console\Commands;

use App\AI\Services\ConversationService;
use Illuminate\Console\Command;

/**
 * Chat Command
 *
 * Interactive terminal chatbot demonstrating stateful AI conversation.
 * Uses ConversationService for message history management.
 *
 * Usage: php artisan ai:chat
 * Type 'exit' to end the conversation.
 *
 * TEMPLATE USAGE: Replace system prompt with your domain context.
 */
class Chat extends Command
{
    protected $signature = 'ai:chat';
    protected $description = 'Have a conversation with AI';

    public function __construct(private ConversationService $conversationService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $conversation = $this->conversationService->startConversation(
            'You are a helpful car dealership assistant.'
        );

        $this->info('Conversation started. Type "exit" to quit.');

        while (true) {
            $userInput = $this->ask('You');

            if ($userInput === 'exit') {
                $this->info('Goodbye!');
                break;
            }

            $reply = $this->conversationService->chat($conversation, $userInput);

            $this->info('AI: ' . $reply);
        }
    }
}
