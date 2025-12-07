<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MoodTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'mood_id',
        'category',
        'trigger',
    ];

    public function mood(): BelongsTo
    {
        return $this->belongsTo(Mood::class);
    }
}
