<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class TestEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test embeddings and semantic search';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $documents = [
            'BMW X5 2019, SUV, excellent condition, 45000 km',
            'Toyota Camry 2020, sedan, fuel efficient, 30000 km',
            'Mercedes C200 2018, luxury sedan, leather seats',
            'Ford F150 2021, pickup truck, towing package',
            'Honda Civic 2022, compact sedan, low mileage',
        ];

        $this->info('Generating embeddings...');

        foreach ($documents as $text) {
            $response = Prism::embeddings()
                ->using(Provider::Ollama, 'nomic-embed-text')
                ->fromInput($text)
                ->asEmbeddings();


            Document::create([
                'content' => $text,
                'embedding' => $response->embeddings[0]->embedding,
            ]);
        }

        $this->info('Documents stored. Now searching...');

        $query = 'I need a family sedan';

        $queryEmbedding = Prism::embeddings()
            ->using(Provider::Ollama, 'nomic-embed-text')
            ->fromInput($query)
            ->asEmbeddings()
            ->embeddings[0]->embedding;


        $documents = Document::all();
        $best = null;
        $bestScore = -1;

        foreach ($documents as $document) {
            $score = $this->cosineSimilarity($queryEmbedding, $document->embedding);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $document;
            }
        }

        $this->info("Query: $query");
        $this->info("Best match: {$best->content}");
        $this->line("Similarity score: $bestScore");
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
