<?php

declare(strict_types=1);

namespace App\AI\Services;

use App\Models\AiUsageLog;

/**
 * Records token usage and cost for every AI call to the database.
 *
 * Called automatically via UsageTrackingListener whenever AiCallCompleted fires.
 * Stores which feature and model was used, how many tokens were consumed,
 * and the USD cost calculated from rates defined in config/ai.php.
 */
class UsageTrackingService
{
    /**
     * Save a single AI call's usage and cost to ai_usage_logs.
     * Cost is calculated automatically from model rates in config.
     */
    public function track(
        string $feature,
        string $provider,
        string $model,
        int $promptTokens,
        int $completionTokens
    ): AiUsageLog {
        $cost = $this->calculateCost($model, $promptTokens, $completionTokens);

        return AiUsageLog::create([
            'feature' => $feature,
            'provider' => $provider,
            'model' => $model,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost_usd' => $cost,
        ]);
    }

    /**
     * Calculate USD cost for a call based on token counts and model rates.
     * Rates are defined in config/ai.php under the 'costs' key.
     * Unknown models default to zero cost.
     */
    public function calculateCost(string $model, int $promptTokens, int $completionTokens): float
    {
        $rates = config('ai.costs.'.$model, ['input' => 0, 'output' => 0]);

        return ($promptTokens * $rates['input']) + ($completionTokens * $rates['output']);
    }
}
