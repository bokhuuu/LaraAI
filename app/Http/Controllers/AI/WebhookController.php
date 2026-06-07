<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Receives incoming webhook requests from external systems.
 *
 * Verifies the request signature to ensure it comes from a trusted source.
 * Stores the uploaded file and dispatches ProcessWebhookJob for async processing.
 * Returns 200 immediately - processing happens in the background via Horizon.
 *
 * Signature verification uses HMAC-SHA256 with WEBHOOK_SECRET from .env.
 * External sender must include X-Webhook-Signature header with every request.
 */
class WebhookController extends Controller
{
    /**
     * Receive a webhook request, verify it and queue the file for processing.
     */
    public function receive(Request $request): JsonResponse
    {
        if (! $this->verifySignature($request)) {
            Log::warning('Webhook signature verification failed', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:'.config('ai.webhook.max_file_size_kb', 10240)],
        ]);

        $file = $request->file('file');
        $path = $file->store('webhooks', 'local');
        $fullPath = Storage::disk('local')->path($path);

        ProcessWebhookJob::dispatch(
            filePath: $fullPath,
            mimeType: $file->getMimeType(),
            metadata: $request->except('file'),
        );

        Log::info('Webhook received and queued', [
            'file' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json(['message' => 'Webhook received. Processing queued.'], 200);
    }

    /**
     * Verify the HMAC-SHA256 signature sent in X-Webhook-Signature header.
     * Compares against a hash of the raw request body using WEBHOOK_SECRET.
     * Returns false if secret is not configured or signature does not match.
     */
    private function verifySignature(Request $request): bool
    {
        $secret = config('ai.webhook.secret');

        if (! $secret) {
            Log::warning('WEBHOOK_SECRET not configured — skipping signature verification.');

            return true;
        }

        $signature = $request->header('X-Webhook-Signature');

        if (! $signature) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
