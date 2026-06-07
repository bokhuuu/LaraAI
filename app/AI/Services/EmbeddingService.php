<?php

namespace App\AI\Services;

use App\Models\Document;
use Illuminate\Support\Collection;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

/**
 * Converts text into vectors (embeddings) and searches them by meaning.
 *
 * How it works:
 * - Store phase: text goes in, a list of numbers (vector) comes out, both saved to DB
 * - Search phase: convert the query to a vector, compare against all stored vectors
 * - Closest vectors = most similar meaning, returned ranked by score
 *
 * This is the foundation of RAG - find relevant documents by meaning,
 * inject them into the AI prompt as context before asking the question.
 *
 * NOTE: loads all documents into memory for comparison.
 * For 1000+ documents switch to pgvector for DB-level vector search.
 */
class EmbeddingService
{
    /**
     * Convert text to a vector and save it to the documents table.
     * Run this once per document to make it searchable by meaning.
     */
    public function generateAndStore(string $content): Document
    {
        $embedding = $this->generateEmbedding($content);

        return Document::create([
            'content' => $content,
            'embedding' => $embedding,
        ]);
    }

    /**
     * Find documents whose meaning is closest to the query.
     * Returns documents ranked by similarity score: 1.0 = identical meaning, 0.0 = unrelated.
     */
    public function search(string $query, ?int $limit = null): Collection
    {
        $limit ??= config('ai.embeddings.search_limit', 5);

        $queryEmbedding = $this->generateEmbedding($query);
        $documents = Document::all();

        return $documents
            ->map(function (Document $document) use ($queryEmbedding) {
                return [
                    'document' => $document,
                    'score' => $this->cosineSimilarity($queryEmbedding, $document->embedding),
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /** Ask the AI model to convert text into a numeric vector. */
    private function generateEmbedding(string $text): array
    {
        return Prism::embeddings()
            ->using(Provider::from(config('ai.providers.default')), config('ai.models.embeddings'))
            ->fromInput($text)
            ->asEmbeddings()
            ->embeddings[0]->embedding;
    }

    /**
     * Compare two vectors and return how similar they are.
     * Measures the angle between them - small angle means similar meaning.
     * Returns 0.0 (completely unrelated) to 1.0 (identical meaning).
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b) || count($a) === 0) {
            return 0.0;
        }

        $dot = 0;
        $normA = 0;
        $normB = 0;

        foreach ($a as $i => $val) {
            $dot += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator === 0.0) {
            return 0.0;
        }

        return $dot / $denominator;
    }
}
