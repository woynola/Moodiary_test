<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Challenge;
use App\Models\Badge;
use App\Models\Journal;
use App\Models\ForumPost;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function dashboard(): View
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalJournals = Journal::count();
        $totalPosts = ForumPost::count();
        $totalChallenges = Challenge::count();

        $recentUsers = User::latest('created_at')->limit(5)->get();
        $recentJournals = Journal::latest('created_at')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'totalJournals',
            'totalPosts',
            'totalChallenges',
            'recentUsers',
            'recentJournals'
        ));
    }

    public function users(): View
    {
        $users = User::paginate(20);

        return view('admin.users', compact('users'));
    }

    public function analytics(): View
    {
        $journalStats = Journal::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();

        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();

        return view('admin.analytics', compact('journalStats', 'userGrowth'));
    }

    // Challenge Management
    public function index(): View
    {
        $challenges = Challenge::paginate(15);

        return view('admin.challenges.index', compact('challenges'));
    }

    public function create(): View
    {
        return view('admin.challenges.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:challenges',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'rules' => 'nullable|string',
            'is_template' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        Challenge::create($validated);

        return redirect()->route('admin.challenges.index')->with('success', 'Challenge created successfully!');
    }

    public function edit(Challenge $challenge): View
    {
        return view('admin.challenges.edit', compact('challenge'));
    }

    public function update(Request $request, Challenge $challenge): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'rules' => 'nullable|string',
            'is_template' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $challenge->update($validated);

        return redirect()->route('admin.challenges.index')->with('success', 'Challenge updated successfully!');
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $challenge->delete();

        return redirect()->route('admin.challenges.index')->with('success', 'Challenge deleted successfully!');
    }
}
