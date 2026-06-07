<?php

namespace App\AI\Services;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Image;

/**
 * Sends images to a vision-capable AI model and returns a text response.
 *
 * Always routes to OpenRouter regardless of the default provider setting,
 * because local Ollama models do not support image input.
 *
 * Used internally by PdfExtractionService to analyze PDF pages as images.
 * Can also be used directly anywhere image understanding is needed.
 */
class MultiModalService
{
    /**
     * Send an image and a prompt to the vision model and return the response.
     * The prompt tells the AI what to look for or extract from the image.
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
