<?php

namespace App\AI\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Schema\ObjectSchema;

/**
 * StructuredOutputService
 *
 * Extracts structured data from unstructured text using AI.
 * Returns PHP array matching the provided ObjectSchema.
 *
 * TEMPLATE USAGE: Define your schema, pass any text,
 * get back clean structured data ready for DB insertion.
 * Example: extract product details from a description.
 */
class StructuredOutputService
{
    /**
     * Extract structured data from text matching the provided schema.
     *
     * @param string $content - Raw text to extract data from
     * @param ObjectSchema - $schema Defines the shape of expected output
     * @return array - PHP array matching schema structure
     */
    public function extract(string $content, ObjectSchema $schema): array
    {
        $response = Prism::structured()
            ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
            ->withSchema($schema)
            ->withPrompt($content)
            ->asStructured();

        return $response->structured;
    }
}
