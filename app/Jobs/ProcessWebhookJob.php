<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Example domain job - rename and replace handle() for your own use case.
 *
 * Receives an uploaded file from WebhookController and processes it.
 * Runs async via Horizon - webhook returns 200 immediately while this runs in background.
 *
 * TEMPLATE: Replace handle() logic with your domain processing.
 * Examples:
 * - PDF catalog → PdfExtractionService + EmbeddingService
 * - Image → MultiModalService
 * - CSV → parse rows and store in DB
 */
class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $filePath,
        public readonly string $mimeType,
        public readonly array $metadata = [],
    ) {}

    /**
     * Process the uploaded file.
     * TEMPLATE: Replace this with your domain logic.
     * Always clean up the file after processing.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing webhook file', [
                'file' => $this->filePath,
                'mime_type' => $this->mimeType,
                'metadata' => $this->metadata,
            ]);

            // TEMPLATE: Add your processing logic here.
            // Example for PDF catalogs:
            // $items = app(PdfExtractionService::class)->extract($this->filePath, ['name', 'price']);
            // foreach ($items as $item) {
            //     app(EmbeddingService::class)->generateAndStore(json_encode($item));
            // }

        } finally {
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }

    /**
     * Log failure details when all retry attempts are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessWebhookJob failed', [
            'file' => $this->filePath,
            'error' => $exception->getMessage(),
        ]);
    }
}
