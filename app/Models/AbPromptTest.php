<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents an active A/B test between two system prompt variants.
 * traffic_split defines what percentage of sessions get variant A.
 * The remainder get variant B.
 */
class AbPromptTest extends Model
{
    protected $fillable = [
        'name',
        'prompt_key_a',
        'prompt_key_b',
        'traffic_split',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'traffic_split' => 'integer',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(AbPromptResult::class);
    }
}
