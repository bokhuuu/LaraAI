<?php

namespace App\Console\Commands;

use App\AI\Services\ConversationService;
use Illuminate\Console\Command;

/**
 * Interactive terminal chatbot for development and testing.
 *
 * Starts a conversation via ConversationService and loops until the user types 'exit'.
 * Full message history is maintained across each turn so the AI remembers context.
 * Useful for quickly testing AI responses without a browser.
 *
 * Usage: php artisan ai:chat
 */
class Chat extends Command
{
    protected $signature = 'ai:chat';

    protected $description = 'Have a conversation with AI';

    public function __construct(private ConversationService $conversationService)
    {
        parent::__construct();
    }

    /**
     * Start a conversation and loop until the user types 'exit'.
     * Each message is saved to DB and full history sent to AI on every turn.
     */
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
