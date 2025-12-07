<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_level',
        'total_points',
        'points_to_next_level',
        'lifetime_points',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addPoints($points): void
    {
        $this->total_points += $points;
        $this->lifetime_points += $points;

        // Check for level up
        while ($this->total_points >= $this->points_to_next_level) {
            $this->levelUp();
        }

        $this->save();
    }

    private function levelUp(): void
    {
        $this->total_points -= $this->points_to_next_level;
        $this->current_level++;
        $this->points_to_next_level = $this->calculatePointsToNextLevel();
    }

    private function calculatePointsToNextLevel(): int
    {
        return 100 * $this->current_level;
    }

    public function getProgressPercentage(): int
    {
        $pointsEarned = $this->points_to_next_level - ($this->points_to_next_level - $this->total_points);
        return (int) (($pointsEarned / $this->points_to_next_level) * 100);
    }
}
