<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class JournalMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'type',
        'url',
        'original_name',
        'mime_type',
        'size',
        'order',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeAudio($query)
    {
        return $query->where('type', 'audio');
    }
}
