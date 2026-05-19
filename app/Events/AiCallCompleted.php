<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after every successful AI text generation call.
 * Listeners handle side effects: usage tracking, cost alerts, analytics.
 */
class AiCallCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly string $feature,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $promptTokens,
        public readonly int $completionTokens,
    ) {}
}
