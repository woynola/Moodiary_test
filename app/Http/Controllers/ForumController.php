<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumCategory;
use App\Models\ForumReaction;
use App\Services\ForumModerationService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ForumController extends Controller
{
    protected ForumModerationService $moderationService;
    protected GamificationService $gamificationService;

    public function __construct(ForumModerationService $moderationService, GamificationService $gamificationService)
    {
        $this->moderationService = $moderationService;
        $this->gamificationService = $gamificationService;
        $this->middleware('auth');
    }

    public function index(): View
    {
        $categories = ForumCategory::active()->ordered()->get();
        $posts = ForumPost::with('user', 'category')
            ->latest('created_at')
            ->paginate(15);

        return view('forum.index', compact('categories', 'posts'));
    }

    public function category(ForumCategory $category): View
    {
        $posts = $category->posts()
            ->with('user')
            ->latest('created_at')
            ->paginate(15);

        $categories = ForumCategory::active()->ordered()->get();

        return view('forum.category', compact('category', 'posts', 'categories'));
    }

    public function create(): View
    {
        $categories = ForumCategory::active()->get();

        return view('forum.posts.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
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

        // Award points
        $this->gamificationService->awardPoints(
            auth()->user(),
            75,
            'post_created',
            'Created a forum post',
            'ForumPost',
            $post->id
        );

        return redirect()->route('forum.posts.show', $post)->with('success', 'Post created successfully!');
    }

    public function show(ForumPost $post): View
    {
        $post->incrementViews();
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with('replies.user')
            ->latest('created_at')
            ->paginate(10);

        return view('forum.posts.show', compact('post', 'comments'));
    }

    public function edit(ForumPost $post): View
    {
        $this->authorize('update', $post);
        $categories = ForumCategory::active()->get();

        return view('forum.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, ForumPost $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:forum_categories,id',
        ]);

        $validated['excerpt'] = substr(strip_tags($validated['content']), 0, 150);
        $post->update($validated);

        return redirect()->route('forum.posts.show', $post)->with('success', 'Post updated successfully!');
    }

    public function destroy(ForumPost $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $post->delete();

        return redirect()->route('forum.index')->with('success', 'Post deleted successfully!');
    }

    public function storeComment(Request $request, ForumPost $post): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:forum_comments,id',
            'is_anonymous' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['post_id'] = $post->id;

        $comment = ForumComment::create($validated);

        // Award points
        $this->gamificationService->awardPoints(
            auth()->user(),
            25,
            'comment_created',
            'Posted a forum comment',
            'ForumComment',
            $comment->id
        );

        $post->increment('replies_count');
        $post->update(['last_reply_at' => now()]);

        return back()->with('success', 'Comment posted successfully!');
    }

    public function react(Request $request, ForumPost $post)
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
            $count = $post->reactions()->where('type', $validated['type'])->count();
        } else {
            ForumReaction::create([
                'user_id' => $user->id,
                'reactable_type' => 'App\\Models\\ForumPost',
                'reactable_id' => $post->id,
                'type' => $validated['type'],
            ]);
            $count = $post->reactions()->where('type', $validated['type'])->count();
        }

        return response()->json(['count' => $count]);
    }

    public function reactComment(Request $request, ForumComment $comment)
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
            $count = $comment->reactions()->where('type', $validated['type'])->count();
        } else {
            ForumReaction::create([
                'user_id' => $user->id,
                'reactable_type' => 'App\\Models\\ForumComment',
                'reactable_id' => $comment->id,
                'type' => $validated['type'],
            ]);
            $count = $comment->reactions()->where('type', $validated['type'])->count();
        }

        return response()->json(['count' => $count]);
    }

    public function report(Request $request, $reportable): RedirectResponse
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

        return back()->with('success', 'Report submitted successfully. Our team will review it shortly.');
    }
}
