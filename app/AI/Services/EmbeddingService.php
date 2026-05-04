<?php

namespace App\AI\Services;

use App\Models\Document;
use Illuminate\Support\Collection;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class EmbeddingService
{
    public function generateAndStore(string $content): Document
    {
        $embedding = $this->generateEmbedding($content);

        return Document::create([
            'content' => $content,
            'embedding' => $embedding,
        ]);
    }

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

    private function generateEmbedding(string $text): array
    {
        return Prism::embeddings()
            ->using(Provider::from(config('ai.providers.default')), config('ai.models.embeddings'))
            ->fromInput($text)
            ->asEmbeddings()
            ->embeddings[0]->embedding;
    }

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
