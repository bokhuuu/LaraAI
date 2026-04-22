<?php

namespace App\AI\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use App\AI\Services\UsageTrackingService;

class TextGenerationService
{

    public function __construct(private UsageTrackingService $usageTracker) {}

    public function generate(string $prompt, string $systemPrompt = ''): string
    {
        $request = Prism::text()
            ->using(Provider::Ollama, 'llama3.2:1b')
            ->withPrompt($prompt);

        if ($systemPrompt) {
            $request = $request->withSystemPrompt($systemPrompt);
        }

        $response = $request->asText();

        $this->usageTracker->track(
            feature: 'text_generation',
            provider: 'ollama',
            model: 'llama3.2:1b',
            promptTokens: $response->usage->promptTokens,
            completionTokens: $response->usage->completionTokens,
        );

        return $response->text;
    }
}
