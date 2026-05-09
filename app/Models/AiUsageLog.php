<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Stores token usage and cost per AI call. */
class AiUsageLog extends Model
{
    protected $fillable = [
        'feature',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
    ];
}
