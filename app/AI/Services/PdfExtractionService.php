<?php

namespace App\AI\Services;

use Spatie\PdfToImage\Pdf;

/**
 * Extracts structured data from PDF files by converting each page to an image
 * and sending it to a vision AI model for analysis.
 *
 * Pages are processed one by one, results merged into a single array.
 * Temporary image files are created per page and deleted immediately after processing.
 * Schema is passed at call time - no domain logic lives here.
 * 
 * Callers are responsible for filtering out incomplete results
 * (e.g. products with null or empty required fields) after extraction.
 *
 * Requires Ghostscript and Imagick installed in the environment.
 */
class PdfExtractionService
{
    public function __construct(
        private MultiModalService $multiModal
    ) {}

    /**
     * Convert each PDF page to an image, extract data via vision AI, return merged results.
     * Temporary images are always cleaned up even if extraction fails on a page.
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
            try {
                $imagePath = $this->convertPageToImage($pdf, $page);
            } catch (\Exception $e) {
                continue;
            }

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
     * Render a single PDF page as a temporary JPG image.
     * Returns the path to the saved image for processing.
     */
    private function convertPageToImage(Pdf $pdf, int $page): string
    {
        $imagePath = sys_get_temp_dir() . '/pdf_page_' . $page . '_' . uniqid() . '.jpg';

        $pdf->selectPage($page)->save($imagePath);

        return $imagePath;
    }

    /**
     * Send a page image to the vision AI with a schema-driven prompt.
     * Returns decoded array of extracted objects, or empty array if unparseable.
     */
    private function extractFromImage(string $imagePath, array $schema): array
    {
        $prompt = $this->buildPrompt($schema);

        $response = $this->multiModal->analyze($imagePath, $prompt);

        return $this->parseResponse($response);
    }

    /**
     * Build the extraction prompt from the schema fields.
     * Instructs the AI to return only a valid JSON array with no extra text.
     */
    private function buildPrompt(array $schema): string
    {
        $fields = implode(', ', $schema);

        return "You are an automated Data Extraction Agent. Your task is to process catalog images and extract every single product listed.\n\n"
            . "### SCANNING PROTOCOL (CRITICAL):\n"
            . "1. SYSTEMATIC SCAN: Scan the image from top to bottom, left to right.\n"
            . "2. EXHAUSTIVE EXTRACTION: Do not skip any products. If 10 items are visible, extract 10 objects.\n"
            . "3. SIDE-BY-SIDE PAGES: If the image shows two pages, process the left side then the right side.\n"
            . "4. VERIFICATION: Before finalizing, perform a mental count of products identified and ensure the JSON array length matches.\n\n"
            . "### DATA SCHEMA:\n"
            . "Extract the following fields for every product: {$fields}.\n"
            . "- All field values MUST be strings. Never use arrays or objects within fields.\n"
            . "- If a value is missing for a product, return null.\n\n"
            . "### OUTPUT REQUIREMENTS:\n"
            . "- Return ONLY a raw JSON array.\n"
            . "- Do NOT include any explanations, preambles, or post-scripts.\n"
            . "- Do NOT use markdown formatting.\n"
            . "- If no products are found on this page, return an empty array [].";
    }

    /**
     * Decode the vision AI's JSON response into a PHP array.
     * Returns empty array on empty or invalid responses - never throws.
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

    /** Delete the temporary page image after processing. Silently skips if already gone. */
    private function cleanupImage(string $imagePath): void
    {
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
