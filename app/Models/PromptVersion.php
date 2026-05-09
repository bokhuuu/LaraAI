<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Versioned system prompt stored in database for dynamic prompt management. */
class PromptVersion extends Model
{
    protected $fillable = [
        'key',
        'content',
        'version',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Get active prompt content for key. Returns null if no active version exists. */
    public static function getActive(string $key): ?string
    {
        $prompt = static::where('key', $key)
            ->where('is_active', true)
            ->latest('version')
            ->first();

        return $prompt?->content;
    }
}
