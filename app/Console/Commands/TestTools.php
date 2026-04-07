<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Tool;

class TestTools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:tools';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tool calling with Prism';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $searchTool = (new Tool())
            ->as('search_cars')
            ->for('Search cars by maximum price')
            ->withNumberParameter('max_price', 'The maximum price in USD')
            ->using(function (int $max_price): string {
                $this->warn("TOOL CALLED with max_price: $max_price");

                $cars = [
                    ['brand' => 'BMW', 'model' => 'X5', 'price' => 35000],
                    ['brand' => 'Toyota', 'model' => 'Camry', 'price' => 8000],
                    ['brand' => 'Mercedes', 'model' => 'C200', 'price' => 42000],
                ];

                $results = array_filter(
                    $cars,
                    fn($car) => $car['price'] <= $max_price
                );

                return json_encode(array_values($results));
            });

        $response = Prism::text()
            ->using(Provider::Ollama, 'llama3.1:8b')
            ->withTools([$searchTool])
            ->withMaxSteps(3)
            ->withPrompt('Which cars do we have under $15,000?')
            ->asText();

        $this->line('Steps taken: ' . count($response->steps));
        $this->line('Tool calls: ' . count($response->toolCalls));

        $this->info($response->text);
    }
}
