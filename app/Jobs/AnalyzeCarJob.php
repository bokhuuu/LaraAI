<?php

namespace App\Jobs;

use App\AI\Services\StructuredOutputService;
use App\Models\Document;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;


class AnalyzeCarJob implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public string $carDescription
    ) {}

    public function handle(StructuredOutputService $service): void
    {
        $schema = new ObjectSchema(
            name: 'car',
            description: 'A car listing',
            properties: [
                new StringSchema('brand', 'The car brand'),
                new StringSchema('model', 'The car model'),
                new NumberSchema('year', 'The year of the car'),
                new NumberSchema('price', 'The price in USD'),
            ],
            requiredFields: ['brand', 'model', 'year', 'price']
        );

        $result = $service->extract($this->carDescription, $schema);

        Document::create([
            'content' => $this->carDescription,
            'embedding' => [],
        ]);

        \Log::info('Car analyzed', $result);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('AnalyzeCarJob failed', [
            'car' => $this->carDescription,
            'error' => $exception->getMessage(),
        ]);
    }
}
