<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mood;
use App\Models\MoodTrigger;
use App\Services\MoodInsightService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MoodApiController extends Controller
{
    protected MoodInsightService $insightService;

    public function __construct(MoodInsightService $insightService)
    {
        $this->insightService = $insightService;
    }

    public function index(): JsonResponse
    {
        $moods = auth()->user()->moods()
            ->with('triggers')
            ->latest('recorded_at')
            ->paginate(20);

        return response()->json($moods);
    }

    public function store(Request $request): JsonResponse
    {
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

        $validated['user_id'] = auth()->id();
        $validated['recorded_at'] = now();

        $mood = Mood::create($validated);

        foreach ($triggers as $trigger) {
            MoodTrigger::create([
                'mood_id' => $mood->id,
                'category' => $trigger['category'],
                'trigger' => $trigger['trigger'],
            ]);
        }

        return response()->json($mood->load('triggers'), 201);
    }

    public function show(Mood $mood): JsonResponse
    {
        $this->authorize('view', $mood);

        return response()->json($mood->load('triggers'));
    }

    public function update(Request $request, Mood $mood): JsonResponse
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
        $mood->triggers()->delete();

        foreach ($triggers as $trigger) {
            MoodTrigger::create([
                'mood_id' => $mood->id,
                'category' => $trigger['category'],
                'trigger' => $trigger['trigger'],
            ]);
        }

        return response()->json($mood->load('triggers'));
    }

    public function destroy(Mood $mood): JsonResponse
    {
        $this->authorize('delete', $mood);
        $mood->delete();

        return response()->json(['message' => 'Mood deleted successfully']);
    }

    public function weeklyStats(): JsonResponse
    {
        $moods = auth()->user()->moods()
            ->whereBetween('recorded_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();

        $distribution = $moods->groupBy('emoji')->map->count();
        $avgIntensity = $moods->avg('intensity') ?? 0;

        return response()->json([
            'distribution' => $distribution,
            'avgIntensity' => round($avgIntensity, 1),
            'totalMoods' => $moods->count(),
        ]);
    }

    public function monthlyStats(): JsonResponse
    {
        $moods = auth()->user()->moods()
            ->whereMonth('recorded_at', now()->month)
            ->whereYear('recorded_at', now()->year)
            ->get();

        $distribution = $moods->groupBy('emoji')->map->count();
        $avgIntensity = $moods->avg('intensity') ?? 0;

        return response()->json([
            'distribution' => $distribution,
            'avgIntensity' => round($avgIntensity, 1),
            'totalMoods' => $moods->count(),
        ]);
    }

    public function insights(): JsonResponse
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

        return response()->json([
            'daily' => $dailyInsight,
            'weekly' => $weeklyInsight,
            'monthly' => $monthlyInsight,
        ]);
    }

    public function trend(): JsonResponse
    {
        $trend = $this->insightService->getMoodTrend(auth()->user(), 30);

        return response()->json($trend);
    }
}
