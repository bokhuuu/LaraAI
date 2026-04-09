<?php

namespace App\Console\Commands\Dev\Services;

use App\AI\Services\EmbeddingService;
use Illuminate\Console\Command;

class TestEmbeddingService extends Command
{
    protected $signature = 'ai:test-embedding-service';
    protected $description = 'Test EmbeddingService';

    public function __construct(private EmbeddingService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Storing documents...');

        $this->service->generateAndStore('BMW X5 2019, SUV, excellent condition, 45000 km');
        $this->service->generateAndStore('Toyota Camry 2020, sedan, fuel efficient, 30000 km');
        $this->service->generateAndStore('Mercedes C200 2018, luxury sedan, leather seats');
        $this->service->generateAndStore('Ford F150 2021, pickup truck, towing package');
        $this->service->generateAndStore('Honda Civic 2022, compact sedan, low mileage');

        $this->info('Searching...');

        $results = $this->service->search('I need a family sedan', 3);

        foreach ($results as $result) {
            $score = round($result['score'], 4);
            $content = $result['document']->content;
            $this->line("[$score] $content");
        }
    }
}
