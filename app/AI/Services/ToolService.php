<?php

namespace App\AI\Services;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Tool;

/**
 * Runs AI prompts with registered tools, without a full agent loop.
 *
 * The AI sees the available tools and decides which to call mid-response.
 * Your PHP function runs, the result feeds back to the AI
 * and the AI produces a final answer using that result.
 *
 * Lighter alternative to LarAgent - no history, no memory, just one prompt + tools.
 * For full agents with history and RAG, use LarAgent instead.
 */
class ToolService
{
    private array $tools = [];

    /**
     * Add a tool the AI is allowed to call during run().
     * Chain multiple calls to register several tools at once.
     */
    public function registerTool(Tool $tool): self
    {
        $this->tools[] = $tool;

        return $this;
    }

    /**
     * Send a prompt to the AI with all registered tools available.
     * The AI decides which tools to call, runs them, then returns a final answer.
     */
    public function run(string $prompt, int $maxSteps = 5): string
    {
        $response = Prism::text()
            ->using(Provider::from(config('ai.providers.default')), config('ai.models.tools'))
            ->withTools($this->tools)
            ->withMaxSteps($maxSteps)
            ->withPrompt($prompt)
            ->asText();

        return $response->text;
    }
}
