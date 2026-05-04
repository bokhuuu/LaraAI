<?php

return [
    'cache_ttl' => env('AI_CACHE_TTL', 3600),

    'models' => [
        'text' => env('AI_TEXT_MODEL', 'llama3.2:1b'),
        'tools' => env('AI_TOOLS_MODEL', 'llama3.1:8b'),
        'embeddings' => env('AI_EMBEDDINGS_MODEL', 'nomic-embed-text'),
        'agent' => env('AI_AGENT_MODEL', 'llama3.1:8b'),
    ],

    'providers' => [
        'default' => env('AI_DEFAULT_PROVIDER', 'ollama'),
        'production' => env('AI_PRODUCTION_PROVIDER', 'openrouter'),
    ],

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

    'rate_limits' => [
        'text_generation' => ['max' => 50,  'ttl' => 3600],
        'embedding' => ['max' => 100, 'ttl' => 3600],
        'chat' => ['max' => 30,  'ttl' => 3600],
        'analysis' => ['max' => 20,  'ttl' => 86400],
    ],
];
