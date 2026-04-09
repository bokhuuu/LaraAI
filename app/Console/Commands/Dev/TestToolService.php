<?php

namespace App\Console\Commands\Dev;

use App\AI\Services\ToolService;
use Illuminate\Console\Command;
use Prism\Prism\Tool;

class TestToolService extends Command
{
    protected $signature = 'ai:test-tool-service';
    protected $description = 'Test ToolService';

    public function __construct(private ToolService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        $searchTool = (new Tool())
            ->as('search_cars')
            ->for('Search cars by brand name')
            ->withStringParameter('brand', 'The car brand to search for')
            ->using(function (string $brand): string {
                $cars = [
                    ['brand' => 'BMW', 'model' => 'X5', 'price' => 35000],
                    ['brand' => 'Toyota', 'model' => 'Camry', 'price' => 8000],
                    ['brand' => 'Mercedes', 'model' => 'C200', 'price' => 42000],
                ];

                $results = array_filter(
                    $cars,
                    fn($car) => strtolower($car['brand']) === strtolower($brand)
                );

                return json_encode(array_values($results));
            });

        $this->service->registerTool($searchTool);

        $result = $this->service->run('What Toyota cars do we have available?');

        $this->info($result);
    }
}
