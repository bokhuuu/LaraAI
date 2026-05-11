# LaraAI

A reusable Laravel AI integration template covering every major AI pattern - text generation, structured output, tool calling, RAG, agents, streaming and more. Built to be cloned and adapted for any domain by swapping domain-specific classes while keeping the entire infrastructure intact.

> **Example domain:** car dealership. Replace it with real estate, medical, e-commerce - the AI layer stays the same.

---

## Why this exists

Most Laravel AI tutorials show you how to make a single API call. This project shows you how to build the full layer: services, agents, cost tracking, rate limiting, caching, fallback providers, queues and tests. Production-ready from day one.

---

## Tech Stack

| Tool | Purpose |
|------|---------|
| **Laravel 12** | Application framework |
| **Prism PHP** | Universal AI provider interface - swap providers without changing code |
| **LarAgent** | Agent framework built on Prism - tool loops, memory, conversation history |
| **Ollama** | Local model execution for development (free, private, offline) |
| **OpenRouter** | Production AI provider - access to GPT-4, Claude, Gemini and more |
| **Redis** | Cache driver + queue backend |
| **Laravel Horizon** | Queue monitoring dashboard |
| **Laravel Telescope** | Development debugging dashboard |
| **Pest** | Testing framework |

---

## Features

### AI Patterns
- ✅ Text generation with prompt/response flow
- ✅ Structured output - force JSON schema, decode to typed PHP array
- ✅ Tool calling - AI decides which PHP function to call and when
- ✅ Stateful conversations - database-backed history on top of stateless LLMs
- ✅ Embeddings + semantic search - cosine similarity over stored vectors
- ✅ RAG (Retrieval-Augmented Generation) - AI answers from your own data
- ✅ LarAgent - full agent loop with tools, memory and MCP support
- ✅ Streaming - SSE responses from AI to browser in real time
- ✅ Multi-modal - image input via OpenRouter + Gemini
- ✅ Prompt versioning - store, activate and roll back system prompts from DB

### Production Infrastructure
- ✅ Cost tracking - log token usage and estimated cost per AI call
- ✅ Rate limiting - per-user, per-feature call limits backed by Redis
- ✅ Response caching - skip duplicate AI calls with hashed cache keys
- ✅ Automatic fallback - if primary provider fails, retry with secondary
- ✅ Async AI jobs - `ShouldQueue` jobs with retry, batching, failure handling
- ✅ Health check endpoint - verify all AI services are reachable
- ✅ Config-driven — zero hardcoded values, everything via `config/ai.php` + `.env`
- ✅ Horizon dashboard — real-time queue monitoring at `/horizon`
- ✅ Telescope integration — full request/job/query debugging at `/telescope`

### Code Quality
- ✅ 26 Pest tests passing - services, jobs, mocked AI responses
- ✅ Clean service architecture - one responsibility per class
- ✅ Docblocks on every class and method
- ✅ Laravel Pint formatting enforced

### Coming Soon
- ⬜ Chat UI - Blade + Alpine.js streaming demo
- ⬜ Docker + `docker-compose.yml`
- ⬜ GitHub Actions CI/CD
- ⬜ Postman collection
- ⬜ Deployment guide
