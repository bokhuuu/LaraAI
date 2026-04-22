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
        $agent = CarAssistantAgent::for('mcp_test_2');
        $response1 = $agent->respond('Please use the memory tool to store this: my name is Zura and I am looking for a Toyota under $15,000.');
        $this->info('1: ' . $response1);

        $agent2 = CarAssistantAgent::for('mcp_test_3');
        $response2 = $agent2->respond('What do you know about Zura from the knowledge base?');
        $this->info('2: ' . $response2);
    }
}
