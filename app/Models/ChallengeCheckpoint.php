<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ChallengeCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_challenge_id',
        'checkpoint_date',
        'is_completed',
        'note',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'checkpoint_date' => 'date',
            'completed_at' => 'datetime',
            'is_completed' => 'boolean',
        ];
    }

    public function userChallenge(): BelongsTo
    {
        return $this->belongsTo(UserChallenge::class);
    }
}
