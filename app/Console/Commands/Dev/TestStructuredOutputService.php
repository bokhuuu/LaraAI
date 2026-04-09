<?php

namespace App\Console\Commands\Dev;

use App\AI\Services\StructuredOutputService;
use Illuminate\Console\Command;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;

class TestStructuredOutputService extends Command
{
    protected $signature = 'ai:test-structured-service';
    protected $description = 'Test StructuredOutputService';

    public function __construct(private StructuredOutputService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $content = "Red BMW X5 2019, SUV, 45000 km, selling for 35000 dollars, located in Tbilisi";

        $schema = new ObjectSchema(
            name: 'car',
            description: 'A car listing',
            properties: [
                new StringSchema('brand', 'The car brand'),
                new StringSchema('model', 'The car model'),
                new NumberSchema('year', 'The year of the car'),
                new NumberSchema('price', 'The price in USD'),
                new StringSchema('city', 'The city where the car is located'),
            ],
            requiredFields: ['brand', 'model', 'year', 'price', 'city']
        );

        $result = $this->service->extract($content, $schema);

        $this->info('Brand: ' . $result['brand']);
        $this->info('Model: ' . $result['model']);
        $this->info('Year: ' . $result['year']);
        $this->info('Price: $' . $result['price']);
        $this->info('City: ' . $result['city']);
    }
}
