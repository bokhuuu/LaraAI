<?php

namespace App\Console\Commands\Dev\Services;

use App\AI\Services\PromptService;
use Illuminate\Console\Command;

class TestPromptHistory extends Command
{
    protected $signature = 'ai:test-prompts-history';
    protected $description = 'Test prompt history';

    public function __construct(private PromptService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $v1 = $this->service->create(
            key: 'test_history',
            content: 'You test history of prompts',
            description: 'Initial version'
        );

        $v2 = $this->service->create(
            key: 'test_history',
            content: 'You test history of prompts',
            description: 'Second version'
        );

        $v3 = $this->service->create(
            key: 'test_history',
            content: 'You test history of prompts',
            description: 'Third version'
        );

        $history = $this->service->getHistory('test_history');

        foreach ($history as $prompt) {
            $this->info("v{$prompt->version}: {$prompt->content}");
        }
    }
}
