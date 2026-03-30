<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;

class Chat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Have a conversation with AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $conversation = Conversation::create();
        $this->info('Conversation started. Type "exit" to quit.');

        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'system',
            'content' => 'You are a helpful car dealership assistant.',
        ]);

        while (true) {
            $userInput = $this->ask('You');

            if ($userInput === 'exit') {
                $this->info('Goodbye!');
                break;
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $userInput,
            ]);

            $messages = $conversation->messages()->orderBy('created_at')->get();

            $prismMessages = $messages->map(function ($message) {
                return match ($message->role) {
                    'user' => new UserMessage($message->content),
                    'assistant' => new AssistantMessage($message->content),
                    'system' => new SystemMessage($message->content),
                };
            })->values()->all();

            $response = Prism::text()
                ->using(Provider::Ollama, 'llama3.2:1b')
                ->withMessages($prismMessages)
                ->asText();

            Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $response->text,
            ]);

            $this->info('AI: ' . $response->text);
        }
    }
}
