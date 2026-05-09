<?php

namespace App\AI\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use App\AI\Services\UsageTrackingService;
use App\AI\Services\RateLimitingService;
use Illuminate\Support\Facades\Cache;

/**
 * TextGenerationService
 *
 * Handles basic AI text generation with:
 * - Response caching (Cache::remember with md5 hash key)
 * - Rate limiting per user per feature
 * - Token usage tracking
 * - Automatic retry on failure (withClientRetry)
 *
 * TEMPLATE USAGE: Inject this service anywhere you need
 * simple AI text generation. Extend generate() method
 * to add domain-specific logic.
 */
class TextGenerationService
{

    public function __construct(
        private UsageTrackingService $usageTracker,
        private RateLimitingService $rateLimiter
    ) {}

    /**
     * Generate AI text response with caching, rate limiting and usage tracking.
     *
     * @param string $prompt - User message or question
     * @param string $systemPrompt - Developer instructions (optional)
     * @param string $userId - For rate limiting - use auth user ID in production
     * @throws \RuntimeException - When rate limit exceeded
     */
    public function generate(string $prompt, string $systemPrompt = '', string $userId = 'default'): string
    {
        if (!$this->rateLimiter->check('text_generation', $userId)) {
            throw new \RuntimeException('Rate limit exceeded for text generation');
        }

        $cacheKey = 'ai_response:' . md5($systemPrompt . $prompt);

        $text = Cache::remember($cacheKey, ttl: config('ai.cache_ttl', 3600), callback: function () use ($prompt, $systemPrompt) {
            $request = Prism::text()
                ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
                ->withPrompt($prompt)
                ->withClientRetry(
                    times: 3,
                    sleepMilliseconds: 1000,
                );

            if ($systemPrompt) {
                $request = $request->withSystemPrompt($systemPrompt);
            }

            $response = $request->asText();

            $this->usageTracker->track(
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
