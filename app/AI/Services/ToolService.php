<?php

namespace App\AI\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Tool;

/**
 * ToolService
 *
 * Manages Prism tool calling without LarAgent:
 * - registerTool(): adds a Tool to the service
 * - run(): sends prompt with registered tools, AI decides which to call
 *
 * TEMPLATE USAGE: Use when you need tool calling without
 * full agent loop. Register tools, run prompt, get result.
 * For full agent with memory/history, use LarAgent instead.
 */
class ToolService
{
    private array $tools = [];

    /**
     * Register a tool the AI can call during run().
     * Returns $this for method chaining: $service->registerTool($t1)->registerTool($t2)
     */
    public function registerTool(Tool $tool): self
    {
        $this->tools[] = $tool;
        return $this;
    }

    /**
     * Send prompt with registered tools. AI decides which tools to call.
     *
     * @param int $maxSteps Max tool calling iterations before forcing final answer
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
