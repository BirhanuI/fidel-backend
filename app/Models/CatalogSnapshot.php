<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CatalogSnapshot extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'version',
        'payload',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
