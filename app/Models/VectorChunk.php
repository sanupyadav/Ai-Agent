<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VectorChunk extends Model
{
    protected $fillable = ['text', 'embedding'];
    protected $casts = [
        'embedding' => 'array',
    ];
}
