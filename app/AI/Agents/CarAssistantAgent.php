<?php

namespace App\AI\Agents;

use LarAgent\Agent;
use LarAgent\Attributes\Tool;

class CarAssistantAgent extends Agent
{
    protected $name = 'CarAssistantAgent';
    protected $model = 'llama3.1:8b';
    protected $history = 'in_memory';
    protected $provider = 'ollama';
    protected $tools = [];

    public function instructions()
    {
        return 'You are a helpful car dealership assistant. Use available tools to answer questions accurately.';
    }

    public function prompt($message)
    {
        return $message;
    }

    #[Tool('Search cars by brand name')]
    public function searchByBrand(string $brand): string
    {
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
    }

    #[Tool('Get all available cars')]
    public function getAllCars(): string
    {
        $cars = [
            ['brand' => 'BMW', 'model' => 'X5', 'price' => 35000],
            ['brand' => 'Toyota', 'model' => 'Camry', 'price' => 8000],
            ['brand' => 'Mercedes', 'model' => 'C200', 'price' => 42000],
        ];

        return json_encode($cars);
    }
}
