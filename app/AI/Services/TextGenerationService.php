<?php

namespace App\AI\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class TextGenerationService
{
    public function generate(string $prompt, string $systemPrompt = ''): string
    {
        $request = Prism::text()
            ->using(Provider::Ollama, 'llama3.2:1b')
            ->withPrompt($prompt);

        if ($systemPrompt) {
            $request = $request->withSystemPrompt($systemPrompt);
        }

        return $request->asText()->text;
    }
}
