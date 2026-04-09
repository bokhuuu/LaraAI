<?php

namespace App\Console\Commands\Dev\Services;

use App\AI\Services\TextGenerationService;
use Illuminate\Console\Command;

class TestTextService extends Command
{
    protected $signature = 'ai:text-service';
    protected $description = 'Test TextGenerationService';

    public function __construct(private TextGenerationService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $result = $this->service->generate(
            prompt: 'Describe a BMW X5 in two sentences.',
            systemPrompt: 'You are a car dealership assistant. Be concise.'
        );

        $this->info($result);
    }
}
