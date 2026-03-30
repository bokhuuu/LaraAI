<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\NumberSchema;

class TestStructured extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:structured';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test structured output with Prism';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Prism::structured()
            ->using(Provider::Ollama, 'llama3.2:1b')
            ->withSchema(new ObjectSchema(
                name: 'car',
                description: 'A car listing',
                properties: [
                    new StringSchema('brand', 'The car brand'),
                    new StringSchema('model', 'The car model'),
                    new NumberSchema('year', 'The year of the car'),
                    new NumberSchema('price', 'The price in USD'),
                ],
                requiredFields: ['brand', 'model', 'year', 'price']
            ))
            ->withPrompt('Extract car details: BMW X5 2019, selling for 35000 dollars.')
            ->asStructured();

        dd($response->structured);
    }
}
