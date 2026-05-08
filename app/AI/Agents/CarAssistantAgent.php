<?php

namespace App\AI\Agents;

use App\AI\Services\EmbeddingService;
use LarAgent\Agent;
use LarAgent\Attributes\Tool;
use App\AI\Services\PromptService;

class CarAssistantAgent extends Agent
{
    protected $name = 'CarAssistantAgent';
    protected $model = 'llama3.1:8b';
    protected $history = 'database';
    protected $provider = 'ollama';
    protected $tools = [];
    protected $mcpServers = ['mcp_server_memory'];

    public function instructions()
    {
        return app(PromptService::class)->get(
            'car_assistant',
            'You are a helpful car dealership assistant.'
        );
    }

    public function model()
    {
        return config('ai.models.agent', 'llama3.1:8b');
    }

    public function getProviderName(): string
    {
        return config('ai.providers.default', 'ollama');
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

    #[Tool('Search car listings by description or requirements')]
    public function searchListings(string $query): string
    {
        $service = new EmbeddingService();
        $results = $service->search($query, 3);

        $listings = $results->map(fn($result) => $result['document']->content)->toArray();

        return json_encode($listings);
    }
}
