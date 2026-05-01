<?php

use App\AI\Services\EmbeddingService;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\EmbeddingsResponseFake;
use Prism\Prism\ValueObjects\Embedding;
use Prism\Prism\ValueObjects\EmbeddingsUsage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new EmbeddingService();
});

test('generateAndStore creates document in database', function () {
    Prism::fake([
        EmbeddingsResponseFake::make()
            ->withEmbeddings([
                new Embedding(embedding: [0.1, 0.2, 0.3])
            ])
            ->withUsage(new EmbeddingsUsage(10))
    ]);

    $document = $this->service->generateAndStore('BMW X5 luxury SUV');

    expect($document)->toBeInstanceOf(Document::class);

    $this->assertDatabaseHas('documents', [
        'content' => 'BMW X5 luxury SUV',
    ]);
});

test('search returns collection of results', function () {
    Prism::fake([
        EmbeddingsResponseFake::make()
            ->withEmbeddings([new Embedding(embedding: [0.1, 0.2, 0.3])]),
        EmbeddingsResponseFake::make()
            ->withEmbeddings([new Embedding(embedding: [0.2, 0.3, 0.4])]),
        EmbeddingsResponseFake::make()
            ->withEmbeddings([new Embedding(embedding: [0.9, 0.8, 0.7])]),
        EmbeddingsResponseFake::make()
            ->withEmbeddings([new Embedding(embedding: [0.1, 0.2, 0.3])]),
    ]);

    $this->service->generateAndStore('BMW X5 luxury SUV');
    $this->service->generateAndStore('Toyota Camry sedan');
    $this->service->generateAndStore('Ford F150 truck');

    $results = $this->service->search('family sedan', 2);

    expect($results)->toHaveCount(2);
    expect($results->first())->toHaveKey('document');
    expect($results->first())->toHaveKey('score');
});
