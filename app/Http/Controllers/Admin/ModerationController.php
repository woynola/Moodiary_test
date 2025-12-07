<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumReport;
use App\Models\ForumPost;
use App\Services\ForumModerationService;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ModerationController extends Controller
{
    protected ForumModerationService $moderationService;

    public function __construct(ForumModerationService $moderationService)
    {
        $this->middleware('moderator');
        $this->moderationService = $moderationService;
    }

    public function index(): View
    {
        $stats = $this->moderationService->getReportStats();
        $recentReports = $this->moderationService->getPendingReports()->limit(10);

        return view('admin.moderation.index', compact('stats', 'recentReports'));
    }

    public function reports(): View
    {
        $reports = ForumReport::with('user', 'reviewedBy')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.moderation.reports', compact('reports'));
    }

    public function reviewReport(Request $request, ForumReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'review_note' => 'nullable|string',
        ]);

        $this->moderationService->reviewReport(
            $report,
            auth()->user(),
            $validated['status'],
            $validated['review_note'] ?? null
        );

        return back()->with('success', 'Report reviewed successfully!');
    }

    public function lockPost(ForumPost $post): RedirectResponse
    {
        $this->moderationService->lockPost($post);

        return back()->with('success', 'Post locked successfully!');
    }

    public function pinPost(ForumPost $post): RedirectResponse
    {
        $this->moderationService->pinPost($post);

        return back()->with('success', 'Post pinned successfully!');
    }
}
