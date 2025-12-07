<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserApiController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function profile(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'user' => $user,
            'level' => $user->level,
            'badges' => $user->badges()->with('badge')->get(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
            'bio' => 'nullable|string|max:500',
            'theme' => 'in:light,dark',
        ]);

        auth()->user()->update($validated);

        return response()->json(auth()->user());
    }

    public function badges(): JsonResponse
    {
        $badges = auth()->user()->badges()
            ->with('badge')
            ->latest('unlocked_at')
            ->get();

        return response()->json($badges);
    }

    public function activity(): JsonResponse
    {
        $activities = auth()->user()->activityLogs()
            ->latest('created_at')
            ->paginate(20);

        return response()->json($activities);
    }

    public function leaderboard(): JsonResponse
    {
        $leaderboard = $this->gamificationService->getLeaderboard(50);

        return response()->json($leaderboard);
    }

    public function rank(): JsonResponse
    {
        $rank = $this->gamificationService->getUserRank(auth()->user());

        return response()->json([
            'rank' => $rank,
            'points' => auth()->user()->points,
        ]);
    }
}
