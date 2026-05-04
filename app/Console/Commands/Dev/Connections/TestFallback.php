<?php

namespace App\Console\Commands\Dev\Connections;

use App\AI\Services\AIFallbackService;
use Illuminate\Console\Command;

class TestFallback extends Command
{
    protected $signature = 'ai:test-fallback';
    protected $description = 'Test AI fallback providers';

    public function __construct(private AIFallbackService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $result = $this->service->generateText(
            prompt: 'Describe BMW X5 in one sentence.',
            systemPrompt: 'You are a car assistant.'
        );

        $this->info($result);
    }
}
