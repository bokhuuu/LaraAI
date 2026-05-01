<?php

use App\Jobs\AnalyzeCarJob;
use App\AI\Services\StructuredOutputService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Prism;
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
            ->withUsage(new Usage(10, 20))
    ]);

    $job = new AnalyzeCarJob('Ferrari F40 1992, sports car, price $500000');
    $job->handle(new StructuredOutputService());

    $this->assertDatabaseHas('documents', [
        'content' => 'Ferrari F40 1992, sports car, price $500000',
    ]);
});

test('failed logs error message', function () {
    Log::shouldReceive('error')
        ->once()
        ->with('AnalyzeCarJob failed', \Mockery::on(
            fn($data) =>
            $data['car'] === 'Ferrari F40 1992' &&
                isset($data['error'])
        ));

    $job = new AnalyzeCarJob('Ferrari F40 1992');
    $job->failed(new \Exception('AI service unavailable'));
});
