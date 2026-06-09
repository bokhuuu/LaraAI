<?php

/**
 * Central configuration for all AI-related settings.
 * Every model, provider, limit, cost rate and threshold is defined here.
 * Change behavior by setting values in .env - never edit PHP files directly.
 *
 * Sections:
 * - cache_ttl: how long to reuse cached AI responses
 * - models: which model to use per use case
 * - providers: ollama for local dev, openrouter for production
 * - fallback: provider chain for AIFallbackService
 * - rate_limits: max calls per user per feature per time window
 * - costs: USD rates per token per model for UsageTrackingService
 * - retry: how many times to retry failed AI calls
 * - default_system_prompt: fallback when no DB prompt exists
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Response Cache TTL
    |--------------------------------------------------------------------------
    | How long to cache identical AI responses (seconds).
    | Same prompt = cached response = zero cost on repeat requests.
    */
    'cache_ttl' => env('AI_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    | Configure which model to use per use case.
    | text/embeddings: use smaller/faster models
    | tools/agent: use larger models for reliable tool calling
    */
    'models' => [
        'text' => env('AI_TEXT_MODEL', 'llama3.2:1b'),
        'tools' => env('AI_TOOLS_MODEL', 'llama3.1:8b'),
        'embeddings' => env('AI_EMBEDDINGS_MODEL', 'nomic-embed-text'),
        'agent' => env('AI_AGENT_MODEL', 'llama3.1:8b'),
        'vision' => env('AI_VISION_MODEL', 'google/gemini-flash-1.5'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Embeddings
    |--------------------------------------------------------------------------
    | search_limit: max documents returned by EmbeddingService::search().
    | Increase for broader RAG context, decrease for tighter relevance.
    */
    'embeddings' => [
        'search_limit' => env('AI_SEARCH_LIMIT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    | default: used for development (ollama = local, free)
    | production: used for production (openrouter = paid, fast)
    */
    'providers' => [
        'default' => env('AI_DEFAULT_PROVIDER', 'ollama'),
        'production' => env('AI_PRODUCTION_PROVIDER', 'openrouter'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Chain
    |--------------------------------------------------------------------------
    | AIFallbackService tries providers in order.
    | First fails → tries second. Both fail → throws exception.
    */
    'fallback' => [
        [
            'provider' => 'openrouter',
            'model' => env('AI_FALLBACK_PRIMARY_MODEL', 'openrouter/free'),
        ],
        [
            'provider' => 'ollama',
            'model' => env('AI_FALLBACK_SECONDARY_MODEL', 'llama3.2:1b'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    | Per-feature limits: max calls allowed within ttl window (seconds).
    | Add new features here as needed.
    */
    'rate_limits' => [
        'text_generation' => ['max' => 50,  'ttl' => 3600],
        'embedding' => ['max' => 100, 'ttl' => 3600],
        'chat' => ['max' => 30,  'ttl' => 3600],
        'analysis' => ['max' => 20,  'ttl' => 86400],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Alert Threshold (USD)
    |--------------------------------------------------------------------------
    | CostAlertListener sends a Slack alert when a single AI call
    | exceeds this amount. Increase for production paid models.
    | TEMPLATE: adjust per your budget requirements.
    */
    'cost_alert_threshold' => env('AI_COST_ALERT_THRESHOLD', 0.01),

    /*
    |--------------------------------------------------------------------------
    | Model Cost Rates (per token, USD)
    |--------------------------------------------------------------------------
    | Used by UsageTrackingService to calculate cost per AI call.
    | Input/output rates per token in USD.
    | Free/local models use 0. Add new models here as needed.
    */
    'costs' => [
        'openrouter/free' => ['input' => 0,          'output' => 0],
        'ollama/llama3.2:1b' => ['input' => 0,          'output' => 0],
        'ollama/llama3.1:8b' => ['input' => 0,          'output' => 0],
        'gpt-4o' => ['input' => 0.000005,   'output' => 0.000015],
        'gpt-4o-mini' => ['input' => 0.00000015, 'output' => 0.0000006],
        'anthropic/claude-sonnet-4-6' => ['input' => 0.000003,   'output' => 0.000015],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    | How many times to retry a failed AI call and how long to wait between
    | attempts (milliseconds). Applied across all services uniformly.
    */
    'retry' => [
        'times' => env('AI_RETRY_TIMES', 3),
        'sleep_ms' => env('AI_RETRY_SLEEP_MS', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default System Prompt
    |--------------------------------------------------------------------------
    | Fallback system prompt used when no versioned prompt exists in the DB.
    | Keep generic - domain-specific prompts belong in prompt_versions table.
    */
    'default_system_prompt' => env('AI_DEFAULT_SYSTEM_PROMPT', 'You are a helpful assistant.'),

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    | secret: HMAC-SHA256 key for verifying incoming webhook requests.
    | max_file_size_kb: maximum accepted file size in kilobytes.
    */
    'webhook' => [
        'secret' => env('WEBHOOK_SECRET'),
        'max_file_size_kb' => env('WEBHOOK_MAX_FILE_SIZE_KB', 10240),
    ],

    /*
    |--------------------------------------------------------------------------
    | Max Tokens
    |--------------------------------------------------------------------------
    | Maximum tokens for AI response.
    | NOTE: usingMaxTokens() not yet supported in current Prism version.
    | Add ->usingMaxTokens(config('ai.max_tokens')) when Prism adds support.
    */
    'max_tokens' => env('AI_MAX_TOKENS', 4096),
];
