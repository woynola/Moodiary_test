<?php

namespace App\Services;

use App\Models\ForumReport;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\User;

class ForumModerationService
{
    public function reportContent($reportableType, $reportableId, User $reporter, string $reason, string $description = null): ForumReport
    {
        return ForumReport::create([
            'user_id' => $reporter->id,
            'reportable_type' => $reportableType,
            'reportable_id' => $reportableId,
            'reason' => $reason,
            'description' => $description,
            'status' => 'pending',
        ]);
    }

    public function reviewReport(ForumReport $report, User $moderator, string $status, string $note = null): void
    {
        $report->update([
            'status' => $status,
            'reviewed_by' => $moderator->id,
            'review_note' => $note,
            'reviewed_at' => now(),
        ]);

        if ($status === 'resolved') {
            $this->handleResolvedReport($report);
        }
    }

    private function handleResolvedReport(ForumReport $report): void
    {
        if ($report->reportable_type === 'App\\Models\\ForumPost') {
            $post = ForumPost::find($report->reportable_id);
            if ($post) {
                $post->delete();
            }
        } elseif ($report->reportable_type === 'App\\Models\\ForumComment') {
            $comment = ForumComment::find($report->reportable_id);
            if ($comment) {
                $comment->delete();
            }
        }
    }

    public function lockPost(ForumPost $post): void
    {
        $post->update(['is_locked' => true]);
    }

    public function unlockPost(ForumPost $post): void
    {
        $post->update(['is_locked' => false]);
    }

    public function pinPost(ForumPost $post): void
    {
        $post->update(['is_pinned' => true]);
    }

    public function unpinPost(ForumPost $post): void
    {
        $post->update(['is_pinned' => false]);
    }

    public function getPendingReports()
    {
        return ForumReport::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getReportStats()
    {
        return [
            'pending' => ForumReport::where('status', 'pending')->count(),
            'reviewed' => ForumReport::where('status', 'reviewed')->count(),
            'resolved' => ForumReport::where('status', 'resolved')->count(),
            'dismissed' => ForumReport::where('status', 'dismissed')->count(),
        ];
    }

    public function getTopReporters()
    {
        return User::withCount('forumReports')
            ->orderBy('forum_reports_count', 'desc')
            ->limit(10)
            ->get();
    }

    public function getTopReportedContent()
    {
        return ForumReport::select('reportable_type', 'reportable_id')
            ->groupBy('reportable_type', 'reportable_id')
            ->selectRaw('count(*) as report_count')
            ->orderBy('report_count', 'desc')
            ->limit(10)
            ->get();
    }
}
