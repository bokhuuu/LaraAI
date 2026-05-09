<?php

namespace App\AI\Services;

use App\Models\Document;
use Illuminate\Support\Collection;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

/**
 * EmbeddingService
 *
 * Handles text embeddings and semantic search:
 * - generateAndStore(): converts text to vector, saves to documents table
 * - search(): finds semantically similar documents using cosine similarity
 *
 * Used for RAG (Retrieval Augmented Generation).
 * TEMPLATE USAGE: Store your domain content as documents,
 * then search by meaning instead of keywords.
 *
 * NOTE: search() loads all documents into memory for comparison.
 * For large datasets (1000+ documents), consider pgvector extension.
 */
class EmbeddingService
{
    /**
     * Convert text to embedding vector and store in documents table.
     * Used to index content for later semantic search.
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
     * Find documents most semantically similar to query.
     * Returns collection of ['document' => Document, 'score' => float].
     * Score range: 0.0 (unrelated) to 1.0 (identical meaning).
     */
    public function search(string $query, int $limit = 5): Collection
    {
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

    /** Convert text to numeric vector using embedding model. */
    private function generateEmbedding(string $text): array
    {
        return Prism::embeddings()
            ->using(Provider::from(config('ai.providers.default')), config('ai.models.embeddings'))
            ->fromInput($text)
            ->asEmbeddings()
            ->embeddings[0]->embedding;
    }

    /**
     * Measure similarity between two vectors.
     * Returns 0.0 (unrelated) to 1.0 (identical meaning).
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;

        foreach ($a as $i => $val) {
            $dot += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
