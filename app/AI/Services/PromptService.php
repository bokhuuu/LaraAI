<?php

namespace App\AI\Services;

use App\Models\PromptVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromptService
{
    public function get(string $key, string $fallback = ''): string
    {
        return PromptVersion::getActive($key) ?? $fallback;
    }

    public function create(string $key, string $content, string $description = ''): PromptVersion
    {
        $latestVersion = PromptVersion::where('key', $key)->max('version') ?? 0;

        return DB::transaction(function () use ($key, $content, $description, $latestVersion) {
            PromptVersion::where('key', $key)->update(['is_active' => false]);

            return PromptVersion::create([
                'key'         => $key,
                'content'     => $content,
                'version'     => $latestVersion + 1,
                'is_active'   => true,
                'description' => $description,
            ]);
        });
    }

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

    public function getHistory(string $key): Collection
    {
        return PromptVersion::where('key', $key)
            ->orderBy('version', 'desc')
            ->get();
    }
}
