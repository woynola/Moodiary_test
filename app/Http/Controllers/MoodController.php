<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\MoodTrigger;
use App\Services\MoodInsightService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MoodController extends Controller
{
    protected MoodInsightService $insightService;
    protected GamificationService $gamificationService;

    public function __construct(MoodInsightService $insightService, GamificationService $gamificationService)
    {
        $this->insightService = $insightService;
        $this->gamificationService = $gamificationService;
        $this->middleware('auth');
    }

    public function index(): View
    {
        $user = auth()->user();
        $moods = $user->moods()
            ->latest('recorded_at')
            ->paginate(20);

        return view('moods.index', compact('moods'));
    }

    public function create(): View
    {
        return view('moods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'emoji' => 'required|string',
            'intensity' => 'required|integer|between:1,10',
            'note' => 'nullable|string',
            'triggers' => 'nullable|array',
            'triggers.*.category' => 'required|string',
            'triggers.*.trigger' => 'required|string',
        ]);

        $user = auth()->user();
        $triggers = $validated['triggers'] ?? [];
        unset($validated['triggers']);

        $validated['user_id'] = $user->id;
        $validated['recorded_at'] = now();

        $mood = Mood::create($validated);

        // Store triggers
        foreach ($triggers as $trigger) {
            MoodTrigger::create([
                'mood_id' => $mood->id,
                'category' => $trigger['category'],
                'trigger' => $trigger['trigger'],
            ]);
        }

        // Award points
        $this->gamificationService->awardPoints(
            $user,
            25,
            'mood_logged',
            'Logged a mood check-in',
            'Mood',
            $mood->id
        );

        // Generate daily insight
        $this->insightService->generateDailyInsight($user, today());

        return redirect()->route('moods.index')->with('success', 'Mood recorded successfully!');
    }

    public function show(Mood $mood): View
    {
        $this->authorize('view', $mood);

        return view('moods.show', compact('mood'));
    }

    public function edit(Mood $mood): View
    {
        $this->authorize('update', $mood);

        return view('moods.edit', compact('mood'));
    }

    public function update(Request $request, Mood $mood): RedirectResponse
    {
        $this->authorize('update', $mood);

        $validated = $request->validate([
            'emoji' => 'required|string',
            'intensity' => 'required|integer|between:1,10',
            'note' => 'nullable|string',
            'triggers' => 'nullable|array',
            'triggers.*.category' => 'required|string',
            'triggers.*.trigger' => 'required|string',
        ]);

        $triggers = $validated['triggers'] ?? [];
        unset($validated['triggers']);

        $mood->update($validated);

        // Update triggers
        $mood->triggers()->delete();
        foreach ($triggers as $trigger) {
            MoodTrigger::create([
                'mood_id' => $mood->id,
                'category' => $trigger['category'],
                'trigger' => $trigger['trigger'],
            ]);
        }

        return redirect()->route('moods.show', $mood)->with('success', 'Mood updated successfully!');
    }

    public function destroy(Mood $mood): RedirectResponse
    {
        $this->authorize('delete', $mood);
        $mood->delete();

        return redirect()->route('moods.index')->with('success', 'Mood deleted successfully!');
    }

    public function weeklyStats(): View
    {
        $user = auth()->user();
        $moods = $user->moods()
            ->whereBetween('recorded_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();

        $stats = $this->calculateStats($moods);

        return view('moods.stats-weekly', compact('stats', 'moods'));
    }

    public function monthlyStats(): View
    {
        $user = auth()->user();
        $moods = $user->moods()
            ->whereMonth('recorded_at', now()->month)
            ->whereYear('recorded_at', now()->year)
            ->get();

        $stats = $this->calculateStats($moods);

        return view('moods.stats-monthly', compact('stats', 'moods'));
    }

    public function insights(): View
    {
        $user = auth()->user();

        $dailyInsight = $user->moodInsights()
            ->where('period', 'daily')
            ->where('period_date', today())
            ->first();

        $weeklyInsight = $user->moodInsights()
            ->where('period', 'weekly')
            ->where('period_date', now()->startOfWeek())
            ->first();

        $monthlyInsight = $user->moodInsights()
            ->where('period', 'monthly')
            ->where('period_date', now()->startOfMonth())
            ->first();

        return view('moods.insights', compact('dailyInsight', 'weeklyInsight', 'monthlyInsight'));
    }

    private function calculateStats($moods)
    {
        $distribution = $moods->groupBy('emoji')->map->count();
        $avgIntensity = $moods->avg('intensity') ?? 0;
        $totalMoods = $moods->count();

        return [
            'distribution' => $distribution,
            'avgIntensity' => round($avgIntensity, 1),
            'totalMoods' => $totalMoods,
        ];
    }
}
