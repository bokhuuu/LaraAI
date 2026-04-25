<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public static function getActive(string $key): ?string
    {
        $prompt = static::where('key', $key)
            ->where('is_active', true)
            ->latest('version')
            ->first();

        return $prompt?->content;
    }
}
