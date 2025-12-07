<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumCategory;
use App\Models\ForumReaction;
use App\Services\ForumModerationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForumApiController extends Controller
{
    protected ForumModerationService $moderationService;

    public function __construct(ForumModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    public function index(): JsonResponse
    {
        $posts = ForumPost::with('user', 'category')
            ->latest('created_at')
            ->paginate(15);

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:forum_categories,id',
            'is_anonymous' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['excerpt'] = substr(strip_tags($validated['content']), 0, 150);

        $post = ForumPost::create($validated);

        return response()->json($post, 201);
    }

    public function show(ForumPost $post): JsonResponse
    {
        $post->incrementViews();

        return response()->json($post->load('user', 'category', 'comments.user', 'media'));
    }

    public function update(Request $request, ForumPost $post): JsonResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:forum_categories,id',
        ]);

        $validated['excerpt'] = substr(strip_tags($validated['content']), 0, 150);
        $post->update($validated);

        return response()->json($post);
    }

    public function destroy(ForumPost $post): JsonResponse
    {
        $this->authorize('delete', $post);
        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    public function storeComment(Request $request, ForumPost $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:forum_comments,id',
            'is_anonymous' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['post_id'] = $post->id;

        $comment = ForumComment::create($validated);
        $post->increment('replies_count');
        $post->update(['last_reply_at' => now()]);

        return response()->json($comment, 201);
    }

    public function react(Request $request, ForumPost $post): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:like,support,hug',
        ]);

        $user = auth()->user();
        $existing = ForumReaction::where('user_id', $user->id)
            ->where('reactable_type', 'App\\Models\\ForumPost')
            ->where('reactable_id', $post->id)
            ->where('type', $validated['type'])
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ForumReaction::create([
                'user_id' => $user->id,
                'reactable_type' => 'App\\Models\\ForumPost',
                'reactable_id' => $post->id,
                'type' => $validated['type'],
            ]);
        }

        $count = $post->reactions()->where('type', $validated['type'])->count();

        return response()->json(['count' => $count]);
    }

    public function reactComment(Request $request, ForumComment $comment): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:like,support,hug',
        ]);

        $user = auth()->user();
        $existing = ForumReaction::where('user_id', $user->id)
            ->where('reactable_type', 'App\\Models\\ForumComment')
            ->where('reactable_id', $comment->id)
            ->where('type', $validated['type'])
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ForumReaction::create([
                'user_id' => $user->id,
                'reactable_type' => 'App\\Models\\ForumComment',
                'reactable_id' => $comment->id,
                'type' => $validated['type'],
            ]);
        }

        $count = $comment->reactions()->where('type', $validated['type'])->count();

        return response()->json(['count' => $count]);
    }

    public function report(Request $request, $reportable): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $this->moderationService->reportContent(
            get_class($reportable),
            $reportable->id,
            auth()->user(),
            $validated['reason'],
            $validated['description'] ?? null
        );

        return response()->json(['message' => 'Report submitted'], 201);
    }

    public function categories(): JsonResponse
    {
        $categories = ForumCategory::active()->ordered()->get();

        return response()->json($categories);
    }
}
