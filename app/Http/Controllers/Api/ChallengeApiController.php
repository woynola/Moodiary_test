<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Services\ChallengeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChallengeApiController extends Controller
{
    protected ChallengeService $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
    }

    public function index(): JsonResponse
    {
        $challenges = Challenge::where('is_template', true)
            ->where('is_active', true)
            ->paginate(12);

        return response()->json($challenges);
    }

    public function show(Challenge $challenge): JsonResponse
    {
        $user = auth()->user();
        $userChallenge = $user->challenges()
            ->where('challenge_id', $challenge->id)
            ->first();

        return response()->json([
            'challenge' => $challenge,
            'userChallenge' => $userChallenge,
        ]);
    }

    public function join(Request $request, Challenge $challenge): JsonResponse
    {
        $user = auth()->user();

        if ($user->challenges()->where('challenge_id', $challenge->id)->exists()) {
            return response()->json(['message' => 'Already participating'], 422);
        }

        $userChallenge = $this->challengeService->startChallenge($user, $challenge);

        return response()->json($userChallenge, 201);
    }

    public function myChallenges(): JsonResponse
    {
        $user = auth()->user();
        $activeChallenges = $this->challengeService->getActiveChallenges($user);
        $completedChallenges = $this->challengeService->getCompletedChallenges($user);

        return response()->json([
            'active' => $activeChallenges,
            'completed' => $completedChallenges,
        ]);
    }

    public function completeCheckpoint(Request $request, UserChallenge $userChallenge): JsonResponse
    {
        $this->authorize('update', $userChallenge);

        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        if ($this->challengeService->completeCheckpoint($userChallenge, $validated['date'])) {
            return response()->json(['message' => 'Checkpoint completed']);
        }

        return response()->json(['message' => 'Unable to complete checkpoint'], 422);
    }

    public function abandon(UserChallenge $userChallenge): JsonResponse
    {
        $this->authorize('update', $userChallenge);

        $this->challengeService->abandonChallenge($userChallenge);

        return response()->json(['message' => 'Challenge abandoned']);
    }
}
