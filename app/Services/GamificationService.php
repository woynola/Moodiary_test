<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\UserLevel;
use App\Models\UserBadge;
use App\Models\Badge;

class GamificationService
{
    public function awardPoints(User $user, int $points, string $activityType, string $description = null, $relatedType = null, $relatedId = null): void
    {
        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'points_earned' => $points,
            'description' => $description,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);

        // Update user level
        $level = $user->level ?? UserLevel::create(['user_id' => $user->id]);
        $level->addPoints($points);

        // Check for badge unlocks
        $this->checkBadgeUnlocks($user);

        // Update user points
        $user->update(['points' => $user->points + $points]);
    }

    public function checkBadgeUnlocks(User $user): void
    {
        $badges = Badge::active()->get();

        foreach ($badges as $badge) {
            // Check if user already has badge
            if ($user->badges()->where('badge_id', $badge->id)->exists()) {
                continue;
            }

            // Check unlock condition
            if ($this->checkUnlockCondition($user, $badge)) {
                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'unlocked_at' => now(),
                ]);
            }
        }
    }

    private function checkUnlockCondition(User $user, Badge $badge): bool
    {
        if ($user->points >= $badge->required_points) {
            return true;
        }

        // Add more custom unlock conditions based on badge slug
        return match ($badge->slug) {
            'first_journal' => $user->journals()->count() >= 1,
            'journal_streak_7' => $this->hasJournalStreak($user, 7),
            'mood_tracker_master' => $user->moods()->count() >= 30,
            'forum_contributor' => $user->forumPosts()->count() >= 5,
            'challenge_completer' => $user->challenges()->where('status', 'completed')->count() >= 1,
            'level_10' => $user->level->current_level >= 10,
            default => false,
        };
    }

    private function hasJournalStreak(User $user, int $days): bool
    {
        $journals = $user->journals()
            ->orderBy('entry_date', 'desc')
            ->limit($days)
            ->pluck('entry_date')
            ->toArray();

        if (count($journals) < $days) {
            return false;
        }

        for ($i = 0; $i < $days - 1; $i++) {
            $diff = $journals[$i]->diffInDays($journals[$i + 1]);
            if ($diff !== 1) {
                return false;
            }
        }

        return true;
    }

    public function getLeaderboard(int $limit = 10)
    {
        return User::active()
            ->orderBy('points', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getUserRank(User $user): int
    {
        return User::active()
            ->where('points', '>', $user->points)
            ->count() + 1;
    }
}
