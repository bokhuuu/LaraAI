<?php

namespace App\AI\Services;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class AIFallbackService
{
    private function getProviders(): array
    {
        return collect(config('ai.fallback'))
            ->map(fn($p) => [
                'provider' => Provider::from($p['provider']),
                'model'    => $p['model'],
            ])
            ->toArray();
    }

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
