<?php

namespace App\Jobs;

use App\AI\Services\StructuredOutputService;
use App\Models\Document;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Schema\ObjectSchema;

/**
 * Example async job demonstrating AI processing in queue.
 * Extracts structured data from text using StructuredOutputService.
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

    /** Text content to extract structured data from. */
    public function __construct(
        public readonly string $content,
        public readonly ObjectSchema $schema,
    ) {}

    /**
     * Extract structured data from content and store as a document.
     * TEMPLATE: Replace Document::create() with EmbeddingService::generateAndStore()
     * to automatically index content for RAG search.
     */
    public function handle(StructuredOutputService $service): void
    {
        $result = $service->extract($this->content, $this->schema);

        Document::create([
            'content' => $this->content,
            'embedding' => [],
        ]);

        Log::info('Content analyzed', $result);
    }

    /** Log error details when all retry attempts are exhausted. */
    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeCarJob failed', [
            'content' => $this->content,
            'error' => $exception->getMessage(),
        ]);
    }
}
