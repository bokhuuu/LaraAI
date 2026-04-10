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
        $response = CarAssistantAgent::for('test_session')->respond('What Toyota cars do you have?');
        $this->info($response);
    }
}
