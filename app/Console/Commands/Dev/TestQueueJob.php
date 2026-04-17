<?php

namespace App\Console\Commands\Dev;

use App\Jobs\AnalyzeCarJob;
use Illuminate\Console\Command;

class TestQueueJob extends Command
{
    protected $signature = 'ai:test-queue';
    protected $description = 'Test async AI job';

    public function handle()
    {
        $this->info('Dispatching job...');

        AnalyzeCarJob::dispatch('Red Ferrari F40 1992, sports car, 15000 km, price $500000');

        $this->info('Job dispatched. Check jobs table in phpMyAdmin.');
        $this->info('Run: php artisan queue:work to process it.');
    }
}
