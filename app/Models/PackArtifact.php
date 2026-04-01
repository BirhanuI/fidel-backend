<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackArtifact extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'content_pack_id',
        'file_url',
        'bytes',
        'sha256_hex',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bytes' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function contentPack(): BelongsTo
    {
        return $this->belongsTo(ContentPack::class);
    }
}
