<?php

namespace App\Services;

use App\Models\User;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\ChallengeCheckpoint;
use Carbon\Carbon;

class ChallengeService
{
    public function startChallenge(User $user, Challenge $challenge): UserChallenge
    {
        $userChallenge = UserChallenge::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'started_at' => now(),
            'status' => 'active',
        ]);

        // Create checkpoints for each day
        for ($i = 0; $i < $challenge->duration_days; $i++) {
            ChallengeCheckpoint::create([
                'user_challenge_id' => $userChallenge->id,
                'checkpoint_date' => now()->addDays($i)->toDateString(),
                'is_completed' => false,
            ]);
        }

        return $userChallenge;
    }

    public function completeCheckpoint(UserChallenge $userChallenge, $date): bool
    {
        $checkpoint = $userChallenge->checkpoints()
            ->where('checkpoint_date', $date)
            ->first();

        if (!$checkpoint || $checkpoint->is_completed) {
            return false;
        }

        $checkpoint->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Update streak and completed days
        $this->updateStreak($userChallenge);
        $userChallenge->increment('total_completed_days');

        // Check if challenge is completed
        if ($userChallenge->total_completed_days >= $userChallenge->challenge->duration_days) {
            $this->completeChallenge($userChallenge);
        }

        return true;
    }

    private function updateStreak(UserChallenge $userChallenge): void
    {
        $checkpoints = $userChallenge->checkpoints()
            ->where('is_completed', true)
            ->orderBy('checkpoint_date', 'desc')
            ->get();

        $streak = 0;
        $today = now()->toDateString();

        foreach ($checkpoints as $checkpoint) {
            if ($checkpoint->checkpoint_date === $today || 
                $checkpoint->checkpoint_date === now()->subDays($streak)->toDateString()) {
                $streak++;
            } else {
                break;
            }
        }

        $userChallenge->update([
            'current_streak' => $streak,
            'longest_streak' => max($streak, $userChallenge->longest_streak),
        ]);
    }

    public function completeChallenge(UserChallenge $userChallenge): void
    {
        $userChallenge->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Award points
        $gamificationService = app(GamificationService::class);
        $points = 500 + ($userChallenge->longest_streak * 10);
        $gamificationService->awardPoints(
            $userChallenge->user,
            $points,
            'challenge_completed',
            "Completed challenge: {$userChallenge->challenge->name}",
            'Challenge',
            $userChallenge->challenge->id
        );
    }

    public function abandonChallenge(UserChallenge $userChallenge): void
    {
        $userChallenge->update(['status' => 'abandoned']);
    }

    public function getActiveChallenges(User $user)
    {
        return $user->challenges()
            ->where('status', 'active')
            ->with('challenge')
            ->get();
    }

    public function getCompletedChallenges(User $user)
    {
        return $user->challenges()
            ->where('status', 'completed')
            ->with('challenge')
            ->get();
    }

    public function getTodayCheckpoint(UserChallenge $userChallenge): ?ChallengeCheckpoint
    {
        return $userChallenge->checkpoints()
            ->where('checkpoint_date', today())
            ->first();
    }

    public function isTodayCompleted(UserChallenge $userChallenge): bool
    {
        $checkpoint = $this->getTodayCheckpoint($userChallenge);
        return $checkpoint && $checkpoint->is_completed;
    }
}
