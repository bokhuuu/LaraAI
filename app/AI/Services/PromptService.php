<?php

namespace App\AI\Services;

use App\Models\PromptVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PromptService
 *
 * Manages versioned system prompts stored in database.
 * - get(): returns active prompt content or fallback
 * - create(): creates new version, deactivates previous
 * - rollback(): restores previous version
 * - getHistory(): returns all versions for a key
 *
 * TEMPLATE USAGE: Store all system prompts in DB.
 * Change prompts without redeployment.
 * Use prompt keys like 'assistant', 'analyzer', 'extractor'.
 */
class PromptService
{
    /** Get active prompt content for key. Returns fallback if no active version exists. */
    public function get(string $key, string $fallback = ''): string
    {
        return PromptVersion::getActive($key) ?? $fallback;
    }

    /**
     * Create new prompt version, deactivating all previous versions.
     * Wrapped in transaction - either both operations succeed or neither does.
     */
    public function create(string $key, string $content, string $description = ''): PromptVersion
    {
        $latestVersion = PromptVersion::where('key', $key)->max('version') ?? 0;

        return DB::transaction(function () use ($key, $content, $description, $latestVersion) {
            PromptVersion::where('key', $key)->update(['is_active' => false]);

            return PromptVersion::create([
                'key' => $key,
                'content' => $content,
                'version' => $latestVersion + 1,
                'is_active' => true,
                'description' => $description,
            ]);
        });
    }

    /**
     * Restore previous prompt version.
     * Returns null if already at version 1 or no active version exists.
     */
    public function rollback(string $key): ?PromptVersion
    {
        $current = PromptVersion::where('key', $key)
            ->where('is_active', true)
            ->first();

        if (!$current || $current->version <= 1) {
            return null;
        }

        return DB::transaction(function () use ($key, $current) {
            $current->update(['is_active' => false]);

            $previous = PromptVersion::where('key', $key)
                ->where('version', $current->version - 1)
                ->first();

            $previous->update(['is_active' => true]);

            return $previous;
        });
    }

    /** Get all versions for a key, newest first. */
    public function getHistory(string $key): Collection
    {
        return PromptVersion::where('key', $key)
            ->orderBy('version', 'desc')
            ->get();
    }
}
