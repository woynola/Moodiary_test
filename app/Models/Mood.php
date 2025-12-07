<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'emoji',
        'intensity',
        'note',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(MoodTrigger::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('recorded_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('recorded_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('recorded_at', now()->month)
                     ->whereYear('recorded_at', now()->year);
    }
}
