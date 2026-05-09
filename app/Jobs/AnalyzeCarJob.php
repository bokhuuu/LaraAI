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
use Illuminate\Support\Facades\Log;

/**
 * AnalyzeCarJob
 *
 * Example async job demonstrating AI processing in queue.
 * Extracts structured car data from text using StructuredOutputService.
 *
 * Features demonstrated:
 * - ShouldQueue interface (async execution)
 * - Batchable trait (group multiple jobs)
 * - Retry logic ($tries, $backoff)
 * - Failed job handling (failed() method)
 * - Service injection in handle()
 *
 * TEMPLATE USAGE: Rename to YourDomainAnalyzeJob.
 * Replace schema and logic in handle() with your domain.
 */
class AnalyzeCarJob implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    /** Job data - car description text to analyze. */
    public function __construct(
        public string $carDescription
    ) {}

    /**
     * Extract structured car data from description and store as document.
     * TEMPLATE: Replace schema and Document::create with your domain logic.
     */
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

        // TEMPLATE: Replace with EmbeddingService::generateAndStore($this->carDescription)
        // to automatically generate and store embedding vectors
        Document::create([
            'content' => $this->carDescription,
            'embedding' => [],
        ]);

        Log::info('Car analyzed', $result);
    }

    /** Log error details when all retry attempts are exhausted. */
    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeCarJob failed', [
            'car' => $this->carDescription,
            'error' => $exception->getMessage(),
        ]);
    }
}
