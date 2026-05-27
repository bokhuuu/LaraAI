<?php

/**
 * AI Configuration
 *
 * Central configuration for all AI-related settings.
 * Override values in .env file — no code changes needed.
 *
 * Providers: ollama (local dev), openrouter (production)
 * Models: configure per use case for cost/performance balance
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
        'openrouter/free'              => ['input' => 0,          'output' => 0],
        'ollama/llama3.2:1b'           => ['input' => 0,          'output' => 0],
        'ollama/llama3.1:8b'           => ['input' => 0,          'output' => 0],
        'gpt-4o'                       => ['input' => 0.000005,   'output' => 0.000015],
        'gpt-4o-mini'                  => ['input' => 0.00000015, 'output' => 0.0000006],
        'anthropic/claude-sonnet-4-6'  => ['input' => 0.000003,   'output' => 0.000015],
    ],
];
