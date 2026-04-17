<?php

namespace App\Console\Commands\Dev\Connections;

use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class TestStreaming extends Command
{
    protected $signature = 'ai:test-streaming';
    protected $description = 'Test streaming responses';

    public function handle()
    {
        $this->info('Streaming response:');
        $this->line('');

        $fullResponse = '';

        $stream = Prism::text()
            ->using(Provider::OpenRouter, 'openrouter/free')
            ->withSystemPrompt('You are a car dealership assistant.')
            ->withPrompt('Describe the BMW X5 in detail.')
            ->asStream();

        foreach ($stream as $chunk) {
            if ($chunk instanceof \Prism\Prism\Streaming\Events\TextDeltaEvent) {
                $fullResponse .= $chunk->delta;
                echo $chunk->delta;
            }
        }

        $this->line('');
        $this->line('');
        $this->info('Complete response length: ' . strlen($fullResponse) . ' characters');
    }
}
