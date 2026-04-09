<?php

namespace App\Console\Commands\Dev;

use App\AI\Services\ConversationService;
use Illuminate\Console\Command;

class TestConversationService extends Command
{
    protected $signature = 'ai:test-conversation-service';
    protected $description = 'Test ConversationService';

    public function __construct(private ConversationService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $conversation = $this->service->startConversation(
            'You are a car dealership assistant. Be concise.'
        );

        $this->info('Conversation started. ID: ' . $conversation->id);

        $reply1 = $this->service->chat($conversation, 'What BMWs do you have?');
        $this->line('AI: ' . $reply1);

        $reply2 = $this->service->chat($conversation, 'What was the first car you mentioned?');
        $this->line('AI: ' . $reply2);
    }
}
