<?php

namespace App\Console\Commands\Dev\Connections;

use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class TestOpenRouter extends Command
{
    protected $signature = 'ai:test-openrouter';
    protected $description = 'Test OpenRouter connection';

    public function handle()
    {
        $response = Prism::text()
            ->using(Provider::OpenRouter, 'openrouter/free')
            ->withSystemPrompt('You are a car dealership assistant.')
            ->withPrompt('What is a BMW X5 in one sentence?')
            ->asText();

        $this->info($response->text);
    }
}
