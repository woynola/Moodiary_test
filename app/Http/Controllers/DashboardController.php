<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Mood;
use App\Models\UserChallenge;
use App\Models\ForumPost;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $recentJournals = $user->journals()
            ->latest('entry_date')
            ->limit(5)
            ->get();

        $recentMoods = $user->moods()
            ->latest('recorded_at')
            ->limit(7)
            ->get();

        $activeChallenges = $user->challenges()
            ->where('status', 'active')
            ->with('challenge')
            ->limit(3)
            ->get();

        $todayMood = $user->moods()
            ->whereDate('recorded_at', today())
            ->first();

        $journalCount = $user->journals()->count();
        $moodCount = $user->moods()->count();
        $challengesCompleted = $user->challenges()
            ->where('status', 'completed')
            ->count();

        $weeklyMoods = $user->moods()
            ->whereBetween('recorded_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();

        $moodDistribution = $weeklyMoods->groupBy('emoji')
            ->map->count()
            ->toArray();

        return view('dashboard', [
            'recentJournals' => $recentJournals,
            'recentMoods' => $recentMoods,
            'activeChallenges' => $activeChallenges,
            'todayMood' => $todayMood,
            'journalCount' => $journalCount,
            'moodCount' => $moodCount,
            'challengesCompleted' => $challengesCompleted,
            'moodDistribution' => $moodDistribution,
        ]);
    }
}
