<?php

namespace App\Http\Controllers;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamingController extends Controller
{
    public function stream(): StreamedResponse
    {
        return response()->stream(function () {
            $stream = Prism::text()
                ->using(Provider::OpenRouter, 'openrouter/free')
                ->withSystemPrompt('You are a car dealership assistant.')
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
