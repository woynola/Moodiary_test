<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ChallengeReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'day_milestone',
        'reward_name',
        'points_reward',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }
}
