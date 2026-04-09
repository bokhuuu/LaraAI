<?php

namespace App\AI\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Tool;

class ToolService
{
    private array $tools = [];

    public function registerTool(Tool $tool): self
    {
        $this->tools[] = $tool;
        return $this;
    }

    public function run(string $prompt, int $maxSteps = 5): string
    {
        $response = Prism::text()
            ->using(Provider::Ollama, 'llama3.1:8b')
            ->withTools($this->tools)
            ->withMaxSteps($maxSteps)
            ->withPrompt($prompt)
            ->asText();

        return $response->text;
    }
}
