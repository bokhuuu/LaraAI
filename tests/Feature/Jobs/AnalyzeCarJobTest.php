<?php

use App\AI\Services\StructuredOutputService;
use App\Jobs\AnalyzeCarJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

test('handle extracts structured data and creates document', function () {
    Prism::fake([
        StructuredResponseFake::make()
            ->withStructured([
                'brand' => 'Ferrari',
                'model' => 'F40',
                'year' => 1992,
                'price' => 500000,
            ])
            ->withUsage(new Usage(10, 20)),
    ]);

    $schema = new ObjectSchema(
        name: 'car',
        description: 'A car listing',
        properties: [
            new StringSchema('brand', 'The car brand'),
            new StringSchema('model', 'The car model'),
            new NumberSchema('year', 'The year of the car'),
            new NumberSchema('price', 'The price in USD'),
        ],
        requiredFields: ['brand', 'model', 'year', 'price']
    );

    $job = new AnalyzeCarJob('Ferrari F40 1992, sports car, price $500000', $schema);
    $job->handle(new StructuredOutputService);

    $this->assertDatabaseHas('documents', [
        'content' => 'Ferrari F40 1992, sports car, price $500000',
    ]);
});

test('failed logs error message', function () {
    Log::shouldReceive('error')
        ->once()
        ->with('AnalyzeCarJob failed', Mockery::on(
            fn ($data) => $data['content'] === 'Ferrari F40 1992, sports car, price $500000' &&
                isset($data['error'])
        ));

    $schema = new ObjectSchema(
        name: 'car',
        description: 'A car listing',
        properties: [
            new StringSchema('brand', 'The car brand'),
            new StringSchema('model', 'The car model'),
            new NumberSchema('year', 'The year of the car'),
            new NumberSchema('price', 'The price in USD'),
        ],
        requiredFields: ['brand', 'model', 'year', 'price']
    );

    $job = new AnalyzeCarJob('Ferrari F40 1992, sports car, price $500000', $schema);
    $job->failed(new Exception('AI service unavailable'));
});
