<?php

namespace App\AI\Services;

use Spatie\PdfToImage\Pdf;

/**
 * Extracts structured data from PDF files using vision AI.
 * Converts each page to an image, sends to MultiModalService,
 * merges results from all pages into a single array.
 *
 * Schema is passed at call time - no domain assumptions.
 * Works with any PDF: catalogues, invoices, brochures, reports.
 *
 * TEMPLATE USAGE: Pass any schema array and any PDF path.
 * Example schemas: product catalogue, property listing, invoice.
 * Requires Ghostscript and Imagick installed in the environment.
 */
class PdfExtractionService
{
    public function __construct(
        private MultiModalService $multiModal
    ) {}

    /**
     * Extract structured data from all pages of a PDF.
     */
    public function extract(string $pdfPath, array $schema): array
    {
        if (! file_exists($pdfPath)) {
            throw new \RuntimeException("PDF file not found: {$pdfPath}");
        }

        $pdf = new Pdf($pdfPath);
        $pageCount = $pdf->pageCount();
        $results = [];

        for ($page = 1; $page <= $pageCount; $page++) {
            $imagePath = $this->convertPageToImage($pdf, $page);

            try {
                $pageData = $this->extractFromImage($imagePath, $schema);

                if (! empty($pageData)) {
                    $results = array_merge($results, $pageData);
                }
            } finally {
                $this->cleanupImage($imagePath);
            }
        }

        return $results;
    }

    /**
     * Convert a single PDF page to a temporary image file.
     * Returns the absolute path to the saved image.
     */
    private function convertPageToImage(Pdf $pdf, int $page): string
    {
        $imagePath = sys_get_temp_dir().'/pdf_page_'.$page.'_'.uniqid().'.jpg';

        $pdf->selectPage($page)->save($imagePath);

        return $imagePath;
    }

    /**
     * Send a page image to MultiModalService with a schema-driven prompt.
     * Returns decoded array, or empty array if response is unparseable.
     */
    private function extractFromImage(string $imagePath, array $schema): array
    {
        $prompt = $this->buildPrompt($schema);

        $response = $this->multiModal->analyze($imagePath, $prompt);

        return $this->parseResponse($response);
    }

    /**
     * Build extraction prompt dynamically from schema fields.
     * Example: ['name', 'price'] → "Extract the following fields as JSON: name, price."
     */
    private function buildPrompt(array $schema): string
    {
        $fields = implode(', ', $schema);

        return 'Extract the following fields from this image as a JSON array of objects. '
            ."Each object should contain: {$fields}. "
            .'Return only valid JSON array, no explanation, no markdown, no code blocks. '
            .'If the page contains no relevant data, return an empty array [].';
    }

    /**
     * Decode JSON response from vision model.
     * Returns empty array if response is empty or unparseable - never crashes.
     */
    private function parseResponse(string $response): array
    {
        $response = trim($response);

        if (empty($response)) {
            return [];
        }

        $decoded = json_decode($response, true);

        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * Delete temporary image file after processing.
     * Silently skips if file no longer exists.
     */
    private function cleanupImage(string $imagePath): void
    {
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
