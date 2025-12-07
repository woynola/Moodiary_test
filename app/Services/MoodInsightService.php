<?php

namespace App\Services;

use App\Models\User;
use App\Models\Mood;
use App\Models\MoodInsight;
use Carbon\Carbon;

class MoodInsightService
{
    public function generateDailyInsight(User $user, $date = null): ?MoodInsight
    {
        $date = $date ? Carbon::parse($date) : today();

        $moods = $user->moods()
            ->whereDate('recorded_at', $date)
            ->get();

        if ($moods->isEmpty()) {
            return null;
        }

        $moodDistribution = $this->calculateMoodDistribution($moods);
        $dominantMood = $this->getDominantMood($moodDistribution);
        $topTriggers = $this->getTopTriggers($moods);

        return MoodInsight::updateOrCreate(
            [
                'user_id' => $user->id,
                'period' => 'daily',
                'period_date' => $date,
            ],
            [
                'dominant_mood' => $dominantMood,
                'mood_distribution' => $moodDistribution,
                'top_triggers' => $topTriggers,
                'insight_text' => $this->generateInsightText($dominantMood, $topTriggers),
            ]
        );
    }

    public function generateWeeklyInsight(User $user, $week = null): ?MoodInsight
    {
        $date = $week ? Carbon::parse($week) : now();
        $startOfWeek = $date->startOfWeek();
        $endOfWeek = $date->endOfWeek();

        $moods = $user->moods()
            ->whereBetween('recorded_at', [$startOfWeek, $endOfWeek])
            ->get();

        if ($moods->isEmpty()) {
            return null;
        }

        $moodDistribution = $this->calculateMoodDistribution($moods);
        $dominantMood = $this->getDominantMood($moodDistribution);
        $topTriggers = $this->getTopTriggers($moods);

        return MoodInsight::updateOrCreate(
            [
                'user_id' => $user->id,
                'period' => 'weekly',
                'period_date' => $startOfWeek,
            ],
            [
                'dominant_mood' => $dominantMood,
                'mood_distribution' => $moodDistribution,
                'top_triggers' => $topTriggers,
                'insight_text' => $this->generateInsightText($dominantMood, $topTriggers),
            ]
        );
    }

    public function generateMonthlyInsight(User $user, $month = null): ?MoodInsight
    {
        $date = $month ? Carbon::parse($month) : now();
        $startOfMonth = $date->startOfMonth();
        $endOfMonth = $date->endOfMonth();

        $moods = $user->moods()
            ->whereBetween('recorded_at', [$startOfMonth, $endOfMonth])
            ->get();

        if ($moods->isEmpty()) {
            return null;
        }

        $moodDistribution = $this->calculateMoodDistribution($moods);
        $dominantMood = $this->getDominantMood($moodDistribution);
        $topTriggers = $this->getTopTriggers($moods);

        return MoodInsight::updateOrCreate(
            [
                'user_id' => $user->id,
                'period' => 'monthly',
                'period_date' => $startOfMonth,
            ],
            [
                'dominant_mood' => $dominantMood,
                'mood_distribution' => $moodDistribution,
                'top_triggers' => $topTriggers,
                'insight_text' => $this->generateInsightText($dominantMood, $topTriggers),
            ]
        );
    }

    private function calculateMoodDistribution($moods): array
    {
        $distribution = [];
        foreach ($moods as $mood) {
            $emoji = $mood->emoji;
            $distribution[$emoji] = ($distribution[$emoji] ?? 0) + 1;
        }
        return $distribution;
    }

    private function getDominantMood($distribution): string
    {
        return array_key_first($distribution) ?? 'neutral';
    }

    private function getTopTriggers($moods): array
    {
        $triggers = [];
        foreach ($moods as $mood) {
            foreach ($mood->triggers as $trigger) {
                $key = $trigger->category . ':' . $trigger->trigger;
                $triggers[$key] = ($triggers[$key] ?? 0) + 1;
            }
        }

        arsort($triggers);
        return array_slice($triggers, 0, 5, true);
    }

    private function generateInsightText($dominantMood, $topTriggers): string
    {
        $moodEmojis = [
            'happy' => '😊',
            'neutral' => '😐',
            'sad' => '😢',
            'angry' => '😠',
            'stressed' => '😰',
            'anxious' => '😟',
            'excited' => '🤩',
            'calm' => '😌',
        ];

        $emoji = $moodEmojis[$dominantMood] ?? '😐';
        $text = "Your dominant mood this period was {$emoji}. ";

        if (!empty($topTriggers)) {
            $triggerList = implode(', ', array_keys(array_slice($topTriggers, 0, 3)));
            $text .= "Key factors affecting your mood: {$triggerList}.";
        }

        return $text;
    }

    public function getMoodTrend(User $user, int $days = 30): array
    {
        $moods = $user->moods()
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(fn($mood) => $mood->recorded_at->format('Y-m-d'));

        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayMoods = $moods->get($date, collect());
            $trend[$date] = $dayMoods->avg('intensity') ?? 0;
        }

        return $trend;
    }
}
