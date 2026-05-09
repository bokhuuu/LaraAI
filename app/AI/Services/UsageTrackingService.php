<?php

namespace App\AI\Services;

use App\Models\AiUsageLog;

/**
 * UsageTrackingService
 *
 * Tracks token usage and cost per AI call.
 * Stores data in ai_usage_logs table.
 *
 * Cost rates per model defined in $costs array.
 * TEMPLATE USAGE: Call track() after every AI response.
 * Add new model rates to $costs array as needed.
 */
class UsageTrackingService
{
    private array $costs = [
        'openrouter/free' => ['input' => 0, 'output' => 0],
        'ollama/llama3.2:1b' => ['input' => 0, 'output' => 0],
        'ollama/llama3.1:8b' => ['input' => 0, 'output' => 0],
        'gpt-4o' => ['input' => 0.000005, 'output' => 0.000015],
        'gpt-4o-mini' => ['input' => 0.00000015, 'output' => 0.0000006],
        'anthropic/claude-sonnet-4-6' => ['input' => 0.000003, 'output' => 0.000015],
    ];

    /**
     * Track token usage and cost for an AI call.
     * Creates a log entry in ai_usage_logs table.
     *
     * @param string $feature - Which feature made the call (text_generation, chat, etc.)
     * @param string $provider - Which provider was used (ollama, openrouter)
     * @param string $model - Which model was used
     * @param int $promptTokens - Tokens sent to AI
     * @param int $completionTokens - Tokens received from AI
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
     * Calculate cost based on model rates.
     * Unknown models default to zero cost.
     */
    private function calculateCost(string $model, int $promptTokens, int $completionTokens): float
    {
        $rates = $this->costs[$model] ?? ['input' => 0, 'output' => 0];

        return ($promptTokens * $rates['input']) + ($completionTokens * $rates['output']);
    }
}
