<?php

namespace App\AI\Services;

use App\Events\AiCallCompleted;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;

/**
 * Extracts structured data from unstructured text using AI.
 *
 * Instead of getting a plain text response, you define a schema
 * and the AI is forced to return data matching that exact shape.
 * Result is a clean PHP array ready for DB insertion or further processing.
 *
 * Fires AiCallCompleted after every call for usage tracking.
 */
class StructuredOutputService
{
    /**
     * Parse raw text and return a PHP array matching the given schema.
     */
    public function extract(string $content, ObjectSchema $schema): array
    {
        $response = Prism::structured()
            ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
            ->withSchema($schema)
            ->withPrompt($content)
            ->asStructured();

        AiCallCompleted::dispatch(
            feature: 'structured_output',
            provider: config('ai.providers.default'),
            model: config('ai.models.text'),
            promptTokens: $response->usage->promptTokens,
            completionTokens: $response->usage->completionTokens,
        );

        return $response->structured;
    }
}
