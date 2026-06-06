<?php

namespace App\AI\Services;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Image;

/**
 * Handles vision-based AI analysis - accepts an image and a prompt,
 * sends both to a vision-capable model via Prism, returns text response.
 *
 * Always uses the production provider (OpenRouter) because local Ollama
 * models do not support vision. Requires OPENROUTER_API_KEY in .env.
 *
 * TEMPLATE USAGE: Inject wherever you need image understanding -
 * PDF page analysis, product photo description, document scanning.
 * Used internally by PdfExtractionService for page-by-page extraction.
 */
class MultiModalService
{
    /**
     * Analyze an image with the given prompt.
     */
    public function analyze(string $imagePath, string $prompt): string
    {
        if (! file_exists($imagePath)) {
            throw new \RuntimeException("Image file not found: {$imagePath}");
        }

        $response = Prism::text()
            ->using(
                Provider::from(config('ai.providers.production')),
                config('ai.models.vision')
            )
            ->withPrompt($prompt, [
                Image::fromLocalPath($imagePath),
            ])
            ->asText();

        return $response->text;
    }
}
