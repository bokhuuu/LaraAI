<?php

namespace App\AI\Services;

use App\Models\AiUsageLog;

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

    private function calculateCost(string $model, int $promptTokens, int $completionTokens): float
    {
        $rates = $this->costs[$model] ?? ['input' => 0, 'output' => 0];

        return ($promptTokens * $rates['input']) + ($completionTokens * $rates['output']);
    }
}
