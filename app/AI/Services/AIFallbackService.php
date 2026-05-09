<?php

namespace App\AI\Services;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

/**
 * AIFallbackService
 *
 * Provides resilient AI text generation with automatic fallback.
 * Tries providers in order: OpenRouter first, Ollama as fallback.
 * Each provider retried twice before moving to next.
 *
 * TEMPLATE USAGE: Use instead of TextGenerationService when
 * high availability is critical. Configure fallback chain
 * in config/ai.php fallback array.
 */
class AIFallbackService
{
    /** Load provider chain from config/ai.php fallback array. */
    private function getProviders(): array
    {
        return collect(config('ai.fallback'))
            ->map(fn($p) => [
                'provider' => Provider::from($p['provider']),
                'model'    => $p['model'],
            ])
            ->toArray();
    }

    /**
     * Generate text with automatic provider fallback.
     * Tries each provider in order, retrying twice before moving to next.
     *
     * @throws \RuntimeException When all providers fail
     */
    public function generateText(string $prompt, string $systemPrompt = ''): string
    {
        $lastException = null;

        foreach ($this->getProviders() as $config) {
            try {
                $request = Prism::text()
                    ->using($config['provider'], $config['model'])
                    ->withClientRetry(times: 2, sleepMilliseconds: 500)
                    ->withPrompt($prompt);

                if ($systemPrompt) {
                    $request = $request->withSystemPrompt($systemPrompt);
                }

                $response = $request->asText();

                Log::info('AI call succeeded', [
                    'provider' => $config['provider']->value,
                    'model' => $config['model'],
                ]);

                return $response->text;
            } catch (\Throwable $e) {
                Log::warning('AI provider failed, trying next', [
                    'provider' => $config['provider']->value,
                    'model' => $config['model'],
                    'error' => $e->getMessage(),
                ]);

                $lastException = $e;
            }
        }

        throw new \RuntimeException(
            'All AI providers failed: ' . $lastException?->getMessage()
        );
    }
}
