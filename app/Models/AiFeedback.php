<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores user feedback votes on AI responses.
 *
 * Tracks which responses users found helpful or unhelpful.
 * user_id is nullable - populated when auth is added, anonymous until then.
 * vote: 1 = positive (thumbs up), -1 = negative (thumbs down).
 */
class AiFeedback extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'response_text',
        'vote',
        'comment',
    ];

    protected $casts = [
        'vote' => 'integer',
    ];
}
