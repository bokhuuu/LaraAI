<?php

namespace App\AI\Services;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

/**
 * Generates AI text with automatic provider fallback for high availability.
 *
 * If the primary provider fails after retries, automatically switches to the next
 * provider in the chain defined in config/ai.php under 'fallback'.
 * Users get a response even when one provider is down.
 *
 * Use this instead of TextGenerationService when uptime is critical.
 */
class AIFallbackService
{
    /** Load the ordered list of providers and models to try from config. */
    private function getProviders(): array
    {
        return collect(config('ai.fallback'))
            ->map(fn($p) => [
                'provider' => Provider::from($p['provider']),
                'model' => $p['model'],
            ])
            ->toArray();
    }

    /**
     * Generate text trying each provider in order until one succeeds.
     * Each provider is retried before moving to the next.
     * Throws RuntimeException only if every provider in the chain fails.
     */
    public function generateText(string $prompt, string $systemPrompt = ''): string
    {
        $lastException = null;

        foreach ($this->getProviders() as $config) {
            try {
                $request = Prism::text()
                    ->using($config['provider'], $config['model'])
                    ->withClientRetry(
                        times: config('ai.retry.times', 3),
                        sleepMilliseconds: config('ai.retry.sleep_ms', 1000)
                    )
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
