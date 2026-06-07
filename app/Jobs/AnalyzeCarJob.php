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
 * Example async job - rename and adapt for your own domain.
 *
 * Runs AI processing in the background queue instead of during the HTTP request.
 * Demonstrates: async execution, batching, retry with backoff, failure handling.
 *
 * Dispatched to Redis queue, picked up and executed by Laravel Horizon.
 * Retries up to 3 times with 60 second gaps before calling failed().
 */
class AnalyzeCarJob implements ShouldQueue
{
    use Batchable, Queueable;

    /** How many times to attempt this job before giving up and calling failed(). */
    public int $tries = 3;

    /** Seconds to wait between retry attempts after a failure. */
    public int $backoff = 60;

    /**
     * Store the data this job needs when it runs.
     * Serialized into Redis on dispatch, deserialized when the worker picks it up.
     */
    public function __construct(
        public readonly string $content,
        public readonly ObjectSchema $schema,
    ) {}

    /**
     * Run the AI extraction and store the result.
     * Called by the queue worker - never called directly.
     * Replace Document::create() with EmbeddingService::generateAndStore()
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

    /**
     * Called when all retry attempts are exhausted.
     * Log the failure here - in production also alert via Slack or mark record as failed.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AnalyzeCarJob failed', [
            'content' => $this->content,
            'error' => $exception->getMessage(),
        ]);
    }
}
