<?php

namespace App\Console\Commands\Dev;

use App\Jobs\AnalyzeCarJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class TestQueueJob extends Command
{
    protected $signature = 'ai:test-queue';
    protected $description = 'Test async AI job';

    public function handle()
    {
        $cars = [
            'Red Ferrari F40 1992, sports car, 15000 km, price $500000',
            'Blue Toyota Camry 2020, sedan, 30000 km, price $8000',
            'Black BMW X5 2019, SUV, 45000 km, price $35000',
        ];

        $jobs = collect($cars)->map(
            fn($car) => new AnalyzeCarJob($car)
        )->toArray();

        $batch = Bus::batch($jobs)
            ->then(function ($batch) {
                \Log::info('All cars analyzed successfully', [
                    'total' => $batch->totalJobs,
                ]);
            })
            ->catch(function ($batch, $e) {
                \Log::error('Batch failed', ['error' => $e->getMessage()]);
            })
            ->finally(function ($batch) {
                \Log::info('Batch finished', [
                    'processed' => $batch->processedJobs(),
                    'failed' => $batch->failedJobs,
                ]);
            })
            ->dispatch();

        $this->info('Batch dispatched. ID: ' . $batch->id);
    }
}
