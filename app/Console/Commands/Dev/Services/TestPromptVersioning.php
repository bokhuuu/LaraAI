<?php

namespace App\Console\Commands\Dev\Services;

use App\AI\Services\PromptService;
use Illuminate\Console\Command;

class TestPromptVersioning extends Command
{
    protected $signature = 'ai:test-prompts';
    protected $description = 'Test prompt versioning';

    public function __construct(private PromptService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $v1 = $this->service->create(
            key: 'car_assistant',
            content: 'You are a helpful car dealership assistant.',
            description: 'Initial version'
        );
        $this->info("Created v{$v1->version}: {$v1->content}");

        $v2 = $this->service->create(
            key: 'car_assistant',
            content: 'You are an expert car dealership advisor with 20 years experience.',
            description: 'More authoritative tone'
        );
        $this->info("Created v{$v2->version}: {$v2->content}");

        $active = $this->service->get('car_assistant');
        $this->info("Active: {$active}");

        $previous = $this->service->rollback('car_assistant');
        $this->info("Rolled back to v{$previous->version}: {$previous->content}");

        $active = $this->service->get('car_assistant');
        $this->info("Active after rollback: {$active}");
    }
}
