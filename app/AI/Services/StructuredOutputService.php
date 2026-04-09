<?php

namespace App\AI\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Schema\ObjectSchema;

class StructuredOutputService
{
    public function extract(string $content, ObjectSchema $schema): array
    {
        $response = Prism::structured()
            ->using(Provider::Ollama, 'llama3.2:1b')
            ->withSchema($schema)
            ->withPrompt($content)
            ->asStructured();

        return $response->structured;
    }
}
