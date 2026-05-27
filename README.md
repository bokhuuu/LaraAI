# LaraAI

A reusable Laravel AI integration template covering every major AI pattern - text generation, structured output, tool calling, RAG, agents, streaming and more. Built to be cloned and adapted for any domain by swapping domain-specific classes while keeping the entire infrastructure intact.

> **Example domain:** car dealership. Replace it with real estate, medical, e-commerce - the AI layer stays the same.

---

## Why this exists

This project shows you how to build the full layer: services, agents, cost tracking, rate limiting, caching, fallback providers, queues and tests. Production-ready from day one.

---

## Tech Stack

| Tool                  | Purpose                                                                   |
| --------------------- | ------------------------------------------------------------------------- |
| **Laravel 12**        | Application framework                                                     |
| **Prism PHP**         | Universal AI provider interface - swap providers without changing code    |
| **LarAgent**          | Agent framework built on Prism - tool loops, memory, conversation history |
| **Ollama**            | Local model execution for development (free, private, offline)            |
| **OpenRouter**        | Production AI provider - access to GPT-4, Claude, Gemini and more         |
| **Redis**             | Cache driver + queue backend                                              |
| **Laravel Horizon**   | Queue monitoring dashboard                                                |
| **Laravel Telescope** | Development debugging dashboard                                           |
| **Pest**              | Testing framework                                                         |
| **Docker**            | Containerized development environment - one command setup                 |

---

## Architecture

app/AI/
├── Services/
│ ├── TextGenerationService # Text generation with caching + rate limiting
│ ├── EmbeddingService # Generate vectors, store, semantic search
│ ├── ConversationService # Stateful conversation management
│ ├── StructuredOutputService # Extract structured data from text
│ ├── ToolService # Prism tool calling without LarAgent
│ ├── PromptService # Versioned system prompts in DB
│ ├── UsageTrackingService # Token usage + cost per AI call
│ ├── RateLimitingService # Per-user per-feature call limits
│ └── AIFallbackService # Automatic provider fallback with retry
└── Agents/
└── CarAssistantAgent # LarAgent agent with tools, RAG, MCP

**Provider strategy:**

Development → Ollama (local, free, private)
Production → OpenRouter (paid, fast, powerful)
Fallback → OpenRouter → Ollama (automatic)

---

## Features

### AI Patterns

- Text generation with prompt/response flow
- Structured output - force JSON schema, decode to typed PHP array
- Tool calling - AI decides which PHP function to call and when
- Stateful conversations - database-backed history on top of stateless LLMs
- Embeddings + semantic search - cosine similarity over stored vectors
- RAG (Retrieval-Augmented Generation) - AI answers from your own data
- LarAgent - full agent loop with tools, memory and MCP support
- Streaming - SSE responses from AI to browser in real time
- Multi-modal - image input via OpenRouter + Gemini
- Prompt versioning - store, activate and roll back system prompts from DB

### Production Infrastructure

- Cost tracking - log token usage and estimated cost per AI call
- Rate limiting - per-user, per-feature call limits backed by Redis
- Response caching - skip duplicate AI calls with hashed cache keys
- Automatic fallback - if primary provider fails, retry with secondary
- Async AI jobs - `ShouldQueue` jobs with retry, batching, failure handling
- Health check endpoint - verify all AI services are reachable
- Config-driven - zero hardcoded values, everything via `config/ai.php` + `.env`
- Horizon dashboard - real-time queue monitoring at `/horizon`
- Telescope integration - full request/job/query debugging at `/telescope`
- Docker - one command setup with MySQL, Redis, Ollama containers
- GitHub Actions CI - tests run automatically on every push and pull request
- Postman collection - import `postman_collection.json` to test all endpoints
- Event-driven side effects - decoupled listeners for usage tracking and Slack cost alerts
- Model observers - automatic Redis cost aggregation on every AI log entry

### Code Quality

- 28 Pest tests passing - services, jobs, mocked AI responses
- Clean service architecture - one responsibility per class
- Docblocks on every class and method
- Laravel Pint formatting enforced

---

## API Endpoints

| Method | Endpoint         | Description                      |
| ------ | ---------------- | -------------------------------- |
| GET    | `/api/ai/health` | Health check for all AI services |
| GET    | `/stream`        | SSE streaming AI response        |
| GET    | `/chat`          | Browser chat interface           |
| GET    | `/horizon`       | Queue monitoring dashboard       |
| GET    | `/telescope`     | Development debugging dashboard  |

Import `postman_collection.json` into Postman to test all endpoints immediately.

---

## Adapting to a New Domain

To use this template for a new domain (e.g. real estate):

| Component           | What to Change                                   |
| ------------------- | ------------------------------------------------ |
| `CarAssistantAgent` | Rename to `PropertyAssistantAgent`, update tools |
| `searchByBrand()`   | Replace with `searchByDistrict()`                |
| `AnalyzeCarJob`     | Rename to `AnalyzePropertyJob`, update schema    |
| `prompt_versions`   | Create new prompts for your domain               |
| `config/ai.php`     | Update models/providers as needed                |

Everything else - services, infrastructure, config - stays identical.

---

## Roadmap

- Events/Listeners - decoupled side effects with Laravel event system
- DB Indexes + N+1 prevention - query optimization for production scale
- Security hardening - OWASP top 10, input sanitization, rate abuse prevention
- Sentry integration - error tracking and performance monitoring
- Demo seeder - 50+ realistic car listings for meaningful RAG and semantic search demo
- Demo GIF - end-to-end chat UI showing real AI answers in the README
