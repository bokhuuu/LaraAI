<?php

namespace App\AI\Agents;

use App\AI\Services\EmbeddingService;
use LarAgent\Agent;
use LarAgent\Attributes\Tool;
use App\AI\Services\PromptService;

/**
 * CarAssistantAgent - Example Domain AI Agent
 *
 * Built on: LarAgent (loop + history) → Prism (API calls) → Ollama/OpenRouter (AI)
 *
 * LarAgent handles automatically:
 * → agent loop (think → call tool → observe → answer)
 * → conversation history saved to database
 * → tool discovery via #[Tool] PHP attributes
 * → MCP server connections
 *
 * We add on top:
 * → RAG search (searchListings uses EmbeddingService)
 * → DB prompt versioning (instructions() pulls from PromptService)
 * → Config-driven model/provider (model() and getProviderName())
 *
 * TEMPLATE: To adapt for new domain -
 * rename class, replace tool methods with your domain logic.
 * All infrastructure (history, MCP, RAG, config) works unchanged.
 */
class CarAssistantAgent extends Agent
{
    /** Agent identifier used for logging and history storage */
    protected $name = 'CarAssistantAgent';

    /** Fallback model - overridden by model() method. Change AI_AGENT_MODEL in .env */
    protected $model = 'llama3.1:8b';

    /**
     * History storage: 'in_memory' | 'cache' | 'database'
     * Use 'database' for production (persists across sessions)
     */
    protected $history = 'database';

    /** Fallback provider - overridden by getProviderName(). Change AI_DEFAULT_PROVIDER in .env */
    protected $provider = 'ollama';

    /** External tool classes - leave empty when using #[Tool] attribute methods */
    protected $tools = [];

    /**
     * MCP servers for external tool access.
     * 'mcp_server_memory' = cross-session knowledge graph.
     * Configure servers in config/laragent.php
     */
    protected $mcpServers = ['mcp_server_memory'];

    /**
     * System prompt pulled from database via PromptService.
     * Falls back to hardcoded string if no DB version exists.
     */
    public function instructions()
    {
        return app(PromptService::class)->get(
            'car_assistant',
            'You are a helpful car dealership assistant.'
        );
    }

    /** Returns model from config. Overrides $model property. Set AI_AGENT_MODEL in .env */
    public function model()
    {
        return config('ai.models.agent', 'llama3.1:8b');
    }

    /** Returns provider from config. Overrides $provider property. Set AI_DEFAULT_PROVIDER in .env */
    public function getProviderName(): string
    {
        return config('ai.providers.default', 'ollama');
    }

    /** Pre-processes user message. Override to inject context or formatting. */
    public function prompt($message)
    {
        return $message;
    }

    /**
     * EXAMPLE TOOL: Search by brand name.
     * TEMPLATE: Replace hardcoded array with real DB query:
     * return json_encode(Car::where('brand', $brand)->get());
     */
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

    /**
     * EXAMPLE TOOL: Get all available items.
     * TEMPLATE: Replace with real DB query:
     * return json_encode(Car::all());
     */
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

    /**
     * RAG TOOL: Finds semantically similar documents using embeddings.
     * TEMPLATE: Works for any domain - store content via
     * EmbeddingService::generateAndStore(), then this searches it.
     */
    #[Tool('Search car listings by description or requirements')]
    public function searchListings(string $query): string
    {
        $service = new EmbeddingService();
        $results = $service->search($query, 3);

        $listings = $results->map(fn($result) => $result['document']->content)->toArray();

        return json_encode($listings);
    }
}
