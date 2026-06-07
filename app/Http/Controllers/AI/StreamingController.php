<?php

declare(strict_types=1);

namespace App\Http\Controllers\AI;

use App\AI\Services\PromptService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AI\StreamPromptRequest;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams AI responses to the browser token by token via Server-Sent Events.
 *
 * Instead of waiting for the full response, the browser receives each word
 * as the AI generates it - same experience as ChatGPT's typing effect.
 *
 * The browser connects via EventSource, receives chunks as they arrive
 * and the stream closes when the AI finishes.
 */
class StreamingController extends Controller
{
    /**
     * Open an SSE connection and stream AI response chunks to the browser.
     * Each chunk arrives as: data: {"text":"..."}\n\n
     * Stream ends with: data: [DONE]\n\n
     *
     * ob_get_level() guard prevents Nginx output buffering from holding chunks.
     * X-Accel-Buffering header tells Nginx to pass chunks through immediately.
     *
     * To protect this endpoint add ->middleware('auth') on the route in routes/web.php.
     */
    public function stream(StreamPromptRequest $request): StreamedResponse
    {
        $prompt = $request->input('message') ?? 'Tell me about our cars.';

        return response()->stream(function () use ($prompt) {
            $stream = Prism::text()
                ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
                ->withSystemPrompt(app(PromptService::class)->get('car_assistant', config('ai.default_system_prompt')))
                ->withPrompt($prompt)
                ->asStream();

            foreach ($stream as $chunk) {
                if ($chunk instanceof TextDeltaEvent) {
                    echo 'data: ' . json_encode(['text' => $chunk->delta]) . "\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            }

            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
