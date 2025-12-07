<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Services\ChallengeService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ChallengeController extends Controller
{
    protected ChallengeService $challengeService;
    protected GamificationService $gamificationService;

    public function __construct(ChallengeService $challengeService, GamificationService $gamificationService)
    {
        $this->challengeService = $challengeService;
        $this->gamificationService = $gamificationService;
        $this->middleware('auth');
    }

    public function index(): View
    {
        $challenges = Challenge::where('is_template', true)
            ->where('is_active', true)
            ->paginate(12);

        $user = auth()->user();
        $userChallengeIds = $user->challenges()->pluck('challenge_id');

        return view('challenges.index', compact('challenges', 'userChallengeIds'));
    }

    public function show(Challenge $challenge): View
    {
        $user = auth()->user();
        $userChallenge = $user->challenges()
            ->where('challenge_id', $challenge->id)
            ->first();

        return view('challenges.show', compact('challenge', 'userChallenge'));
    }

    public function create(): View
    {
        $this->authorize('create', Challenge::class);

        return view('challenges.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Challenge::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:challenges',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'duration_days' => 'required|integer|min:1|max:365',
            'rules' => 'nullable|string',
            'is_template' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        Challenge::create($validated);

        return redirect()->route('challenges.index')->with('success', 'Challenge created successfully!');
    }

    public function edit(Challenge $challenge): View
    {
        $this->authorize('update', $challenge);

        return view('challenges.edit', compact('challenge'));
    }

    public function update(Request $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('update', $challenge);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'duration_days' => 'required|integer|min:1|max:365',
            'rules' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $challenge->update($validated);

        return redirect()->route('challenges.show', $challenge)->with('success', 'Challenge updated successfully!');
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $this->authorize('delete', $challenge);
        $challenge->delete();

        return redirect()->route('challenges.index')->with('success', 'Challenge deleted successfully!');
    }

    public function join(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = auth()->user();

        // Check if already joined
        if ($user->challenges()->where('challenge_id', $challenge->id)->exists()) {
            return back()->with('error', 'You are already participating in this challenge!');
        }

        $userChallenge = $this->challengeService->startChallenge($user, $challenge);

        return redirect()->route('challenges.show', $challenge)->with('success', 'Challenge started successfully!');
    }

    public function completeCheckpoint(Request $request, UserChallenge $userChallenge): RedirectResponse
    {
        $this->authorize('update', $userChallenge);

        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        if ($this->challengeService->completeCheckpoint($userChallenge, $validated['date'])) {
            return back()->with('success', 'Checkpoint completed!');
        }

        return back()->with('error', 'Unable to complete checkpoint.');
    }

    public function abandon(UserChallenge $userChallenge): RedirectResponse
    {
        $this->authorize('update', $userChallenge);

        $this->challengeService->abandonChallenge($userChallenge);

        return redirect()->route('challenges.my')->with('success', 'Challenge abandoned.');
    }

    public function myChallenges(): View
    {
        $user = auth()->user();
        $activeChallenges = $this->challengeService->getActiveChallenges($user);
        $completedChallenges = $this->challengeService->getCompletedChallenges($user);

        return view('challenges.my', compact('activeChallenges', 'completedChallenges'));
    }
}
