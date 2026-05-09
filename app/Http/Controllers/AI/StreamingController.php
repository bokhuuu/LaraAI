<?php

namespace App\Http\Controllers\AI;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\AI\Services\PromptService;

/**
 * StreamingController
 *
 * Demonstrates AI streaming responses via Server-Sent Events (SSE).
 * Browser receives tokens in real-time as AI generates them.
 *
 * Endpoints:
 * - GET /stream → SSE stream of AI response chunks
 * - GET /stream-demo → Browser demo page
 *
 * TEMPLATE USAGE: Replace prompt with your domain question.
 * Use EventSource in browser to receive chunks.
 */
class StreamingController extends Controller
{
    /**
     * Stream AI response via Server-Sent Events.
     * Each token sent as: data: {"text":"..."}\n\n
     * Stream ends with: data: [DONE]\n\n
     *
     * TEMPLATE USAGE: Replace systemPrompt and prompt with your domain content.
     */
    public function stream(): StreamedResponse
    {
        return response()->stream(function () {
            $stream = Prism::text()
                ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
                ->withSystemPrompt(app(PromptService::class)->get('car_assistant', 'You are a car dealership assistant.'))
                ->withPrompt('Describe the BMW X5 in detail.')
                ->asStream();

            foreach ($stream as $chunk) {
                if ($chunk instanceof TextDeltaEvent) {
                    echo "data: " . json_encode(['text' => $chunk->delta]) . "\n\n";
                    ob_flush();
                    flush();
                }
            }

            echo "data: [DONE]\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
