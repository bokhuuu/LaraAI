<?php

namespace App\AI\Services;

use App\Models\PromptVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Stores and versions system prompts in the database.
 *
 * Instead of hardcoding prompts in PHP files, prompts live in the DB.
 * This means AI behavior can be changed instantly without redeployment.
 * Every change creates a new version - nothing is ever overwritten or lost.
 * If a new prompt performs worse, rollback restores the previous one instantly.
 */
class PromptService
{
    /** Return the currently active prompt for this key, or fallback if none exists. */
    public function get(string $key, string $fallback = ''): string
    {
        return PromptVersion::getActive($key) ?? $fallback;
    }

    /**
     * Save a new prompt version and mark it as active.
     * Previous versions are deactivated but kept in DB for rollback.
     * Wrapped in a transaction so active state is always consistent.
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
     * Reactivate the previous prompt version.
     * Use when a new prompt performs worse and you need to revert instantly.
     * Returns null if already at version 1 or no active version exists.
     */
    public function rollback(string $key): ?PromptVersion
    {
        $current = PromptVersion::where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $current || $current->version <= 1) {
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

    /** Return all prompt versions for this key, newest first. */
    public function getHistory(string $key): Collection
    {
        return PromptVersion::where('key', $key)
            ->orderBy('version', 'desc')
            ->get();
    }
}
