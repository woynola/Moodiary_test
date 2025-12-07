<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MoodInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period',
        'period_date',
        'dominant_mood',
        'mood_distribution',
        'top_triggers',
        'insight_text',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'mood_distribution' => 'array',
            'top_triggers' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
