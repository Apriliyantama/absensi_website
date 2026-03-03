<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    protected $fillable = [
        'user_id',
        'embedding',
        'embedding_version',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];
}
