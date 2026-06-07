<?php

namespace App\AI\Services;

use App\Events\AiCallCompleted;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

/**
 * Sends prompts to an AI model and returns the generated text.
 *
 * Caches responses in Redis so identical prompts don't hit the AI twice.
 * Checks and updates per-user rate limits on every call.
 * Fires AiCallCompleted so usage gets tracked automatically.
 *
 * Inject this service wherever plain text generation is needed.
 * Domain-specific logic belongs in Agents and Jobs - not here.
 */
class TextGenerationService
{
    public function __construct(
        private RateLimitingService $rateLimiter
    ) {}

    /**
     * Run a prompt through the AI and return the response text.
     *
     * Blocks the call if the user has hit their rate limit.
     * Returns a cached response if this exact prompt was seen before.
     * Fires AiCallCompleted only on real AI calls, never on cache hits.
     */
    public function generate(string $prompt, string $systemPrompt = '', string $userId = 'default'): string
    {
        if (! $this->rateLimiter->check('text_generation', $userId)) {
            throw new \RuntimeException('Rate limit exceeded for text generation');
        }

        $cacheKey = 'ai_response:' . md5($systemPrompt . $prompt);

        $text = Cache::remember($cacheKey, ttl: config('ai.cache_ttl', 3600), callback: function () use ($prompt, $systemPrompt) {
            $request = Prism::text()
                ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
                ->withPrompt($prompt)
                ->withClientRetry(
                    times: config('ai.retry.times', 3),
                    sleepMilliseconds: config('ai.retry.sleep_ms', 1000)
                );

            if ($systemPrompt) {
                $request = $request->withSystemPrompt($systemPrompt);
            }

            $response = $request->asText();

            AiCallCompleted::dispatch(
                feature: 'text_generation',
                provider: config('ai.providers.default'),
                model: config('ai.models.text'),
                promptTokens: $response->usage->promptTokens,
                completionTokens: $response->usage->completionTokens,
            );

            return $response->text;
        });

        $this->rateLimiter->increment('text_generation', $userId);

        return $text;
    }
}
