<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Groups related messages in a conversation session. */
class Conversation extends Model
{
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
