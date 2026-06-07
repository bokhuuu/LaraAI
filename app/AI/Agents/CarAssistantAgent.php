<?php

namespace App\AI\Agents;

use App\AI\Services\EmbeddingService;
use App\AI\Services\PromptService;
use LarAgent\Agent;
use LarAgent\Attributes\Tool;

/**
 * Example domain agent - replace this for your own project.
 *
 * Extends LarAgent which handles the agent loop automatically:
 * read message → decide which tool to call → call it → observe result → answer.
 * Conversation history, tool discovery and MCP are all handled by LarAgent.
 *
 * What this class adds on top:
 * → Domain tools (searchByBrand, getAllCars, searchListings)
 * → RAG via searchListings using EmbeddingService
 * → System prompt pulled from DB via PromptService
 * → Model and provider wired from config/ai.php
 *
 * To adapt for a new domain: rename this class, replace the tool
 * methods with your domain logic. All infrastructure stays identical.
 */
class CarAssistantAgent extends Agent
{
    protected $name = 'CarAssistantAgent';

    protected $history = 'database';

    protected $tools = [];

    /**
     * Set provider from config before LarAgent initializes.
     * Required because LarAgent reads provider in the parent constructor.
     */
    public function __construct(string $sessionId)
    {
        $this->provider = config('ai.providers.default', 'ollama');
        parent::__construct($sessionId);
    }

    /**
     * MCP servers this agent connects to.
     * 'mcp_server_memory' gives the agent a cross-session knowledge graph -
     * it can remember facts about users across separate conversations.
     */
    protected $mcpServers = ['mcp_server_memory'];

    /**
     * Load the system prompt from the database via PromptService.
     * Falls back to a hardcoded string if no DB version exists yet.
     * Update the prompt in DB to change agent behavior without redeploying.
     */
    public function instructions()
    {
        return app(PromptService::class)->get(
            'car_assistant',
            'You are a helpful car dealership assistant.'
        );
    }

    /** Return the model name from config. Set AI_AGENT_MODEL in .env to change it. */
    public function model()
    {
        return config('ai.models.agent', 'llama3.1:8b');
    }

    /** Pre-process the user message before it reaches the agent. Override to inject context. */
    public function prompt($message)
    {
        return $message;
    }

    /**
     * Tool: find cars by brand name.
     * Currently uses a hardcoded array - replace with a real DB query for production.
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
     * Tool: return all available cars.
     * Currently uses a hardcoded array - replace with a real DB query for production.
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
     * Tool: find semantically similar listings using RAG.
     * Converts the query to a vector and finds the closest matches in the documents table.
     * Works for any domain - store content via EmbeddingService::generateAndStore() first.
     */
    #[Tool('Search car listings by description or requirements')]
    public function searchListings(string $query): string
    {
        $service = app(EmbeddingService::class);
        $results = $service->search($query, 3);

        $listings = $results->map(fn($result) => $result['document']->content)->toArray();

        return json_encode($listings);
    }
}
