<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Stores text content with embedding vectors for semantic search (RAG). */
class Document extends Model
{
    protected $fillable = ['content', 'embedding'];

    protected $casts = ['embedding' => 'array'];
}
