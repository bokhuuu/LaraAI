<?php

namespace App\Console\Commands\Dev\Agents;

use App\AI\Agents\CarAssistantAgent;
use Illuminate\Console\Command;

class TestCarAssistantAgent extends Command
{
    protected $signature = 'ai:test-agent';
    protected $description = 'Test CarAssistantAgent';

    public function handle()
    {
        $response = CarAssistantAgent::for('rag_test')
            ->respond('I need a reliable family car under $15,000');

        $this->info($response);
    }
}
