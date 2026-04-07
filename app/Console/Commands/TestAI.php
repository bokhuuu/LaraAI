<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class TestAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Prism with Ollama';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2:1b')
            ->withSystemPrompt('You are a helpful assistant.')
            ->withPrompt('What is Laravel in one sentence?')
            ->asText();

        $this->info($response->text);
    }
}
