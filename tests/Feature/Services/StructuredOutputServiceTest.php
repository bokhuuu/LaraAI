<?php

use App\AI\Services\StructuredOutputService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\ValueObjects\Usage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new StructuredOutputService();
});

test('extract returns structured array matching schema', function () {
    Prism::fake([
        StructuredResponseFake::make()
            ->withStructured([
                'brand' => 'BMW',
                'model' => 'X5',
                'year'  => 2019,
                'price' => 35000,
            ])
            ->withUsage(new Usage(10, 20))
    ]);

    $schema = new ObjectSchema(
        name: 'car',
        description: 'A car listing',
        properties: [
            new StringSchema('brand', 'The car brand'),
            new StringSchema('model', 'The car model'),
            new NumberSchema('year', 'The year'),
            new NumberSchema('price', 'The price'),
        ],
        requiredFields: ['brand', 'model', 'year', 'price']
    );

    $result = $this->service->extract('BMW X5 2019 for $35000', $schema);

    expect($result)->toBeArray();
    expect($result['brand'])->toBe('BMW');
    expect($result['model'])->toBe('X5');
    expect($result['year'])->toBe(2019);
    expect($result['price'])->toBe(35000);
});
