<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'scheduled_time',
        'frequency',
        'days_of_week',
        'is_active',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_time' => 'datetime',
            'last_sent_at' => 'datetime',
            'is_active' => 'boolean',
            'days_of_week' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
