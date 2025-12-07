<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalMedia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JournalApiController extends Controller
{
    public function index(): JsonResponse
    {
        $journals = auth()->user()->journals()
            ->with('notebook', 'media')
            ->latest('entry_date')
            ->paginate(15);

        return response()->json($journals);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notebook_id' => 'required|exists:notebooks,id',
            'entry_date' => 'required|date',
            'is_private' => 'boolean',
            'mood_score' => 'nullable|integer|between:1,10',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['published_at'] = now();

        $journal = Journal::create($validated);

        return response()->json($journal, 201);
    }

    public function show(Journal $journal): JsonResponse
    {
        $this->authorize('view', $journal);

        return response()->json($journal->load('notebook', 'media', 'tags'));
    }

    public function update(Request $request, Journal $journal): JsonResponse
    {
        $this->authorize('update', $journal);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notebook_id' => 'required|exists:notebooks,id',
            'entry_date' => 'required|date',
            'is_private' => 'boolean',
            'mood_score' => 'nullable|integer|between:1,10',
        ]);

        $journal->update($validated);

        return response()->json($journal);
    }

    public function destroy(Journal $journal): JsonResponse
    {
        $this->authorize('delete', $journal);
        $journal->delete();

        return response()->json(['message' => 'Journal deleted successfully']);
    }

    public function uploadMedia(Request $request, Journal $journal): JsonResponse
    {
        $this->authorize('update', $journal);

        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'type' => 'required|in:image,video,audio',
        ]);

        $file = $request->file('file');
        $path = $file->store("journals/{$journal->id}", 'public');

        $media = JournalMedia::create([
            'journal_id' => $journal->id,
            'type' => $validated['type'],
            'url' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json($media, 201);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');

        $journals = auth()->user()->journals()
            ->whereFullText(['title', 'content'], $query)
            ->latest('entry_date')
            ->paginate(15);

        return response()->json($journals);
    }

    public function calendar($year, $month): JsonResponse
    {
        $journals = auth()->user()->journals()
            ->whereYear('entry_date', $year)
            ->whereMonth('entry_date', $month)
            ->get()
            ->keyBy(fn($j) => $j->entry_date->format('d'));

        return response()->json($journals);
    }
}
