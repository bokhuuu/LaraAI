<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records which variant a session was assigned and the feedback vote outcome.
 * vote is nullable - filled when the user submits feedback after the AI response.
 */
class AbPromptResult extends Model
{
    protected $fillable = [
        'ab_prompt_test_id',
        'session_id',
        'variant',
        'prompt_key',
        'vote',
    ];

    protected $casts = [
        'vote' => 'integer',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(AbPromptTest::class);
    }
}
