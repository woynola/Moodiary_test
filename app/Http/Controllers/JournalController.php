<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Notebook;
use App\Models\JournalMedia;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class JournalController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
        $this->middleware('auth');
    }

    public function index(): View
    {
        $user = auth()->user();
        $journals = $user->journals()
            ->with('notebook')
            ->latest('entry_date')
            ->paginate(15);

        return view('journals.index', compact('journals'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $notebooks = $user->notebooks()->ordered()->get();

        return view('journals.create', compact('notebooks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notebook_id' => 'required|exists:notebooks,id',
            'entry_date' => 'required|date',
            'is_private' => 'boolean',
            'mood_score' => 'nullable|integer|between:1,10',
            'weather' => 'nullable|string',
        ]);

        $user = auth()->user();
        $validated['user_id'] = $user->id;
        $validated['published_at'] = now();
        $validated['reading_time'] = $this->calculateReadingTime($validated['content']);

        $journal = Journal::create($validated);

        // Award points
        $this->gamificationService->awardPoints(
            $user,
            50,
            'journal_created',
            'Created a new journal entry',
            'Journal',
            $journal->id
        );

        return redirect()->route('journals.show', $journal)->with('success', 'Journal entry created successfully!');
    }

    public function show(Journal $journal): View
    {
        $this->authorize('view', $journal);
        $journal->increment('views');

        return view('journals.show', compact('journal'));
    }

    public function edit(Journal $journal): View
    {
        $this->authorize('update', $journal);
        $user = auth()->user();
        $notebooks = $user->notebooks()->ordered()->get();

        return view('journals.edit', compact('journal', 'notebooks'));
    }

    public function update(Request $request, Journal $journal): RedirectResponse
    {
        $this->authorize('update', $journal);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notebook_id' => 'required|exists:notebooks,id',
            'entry_date' => 'required|date',
            'is_private' => 'boolean',
            'mood_score' => 'nullable|integer|between:1,10',
            'weather' => 'nullable|string',
        ]);

        $validated['reading_time'] = $this->calculateReadingTime($validated['content']);
        $journal->update($validated);

        return redirect()->route('journals.show', $journal)->with('success', 'Journal entry updated successfully!');
    }

    public function destroy(Journal $journal): RedirectResponse
    {
        $this->authorize('delete', $journal);
        $journal->delete();

        return redirect()->route('journals.index')->with('success', 'Journal entry deleted successfully!');
    }

    public function calendar($year, $month): View
    {
        $user = auth()->user();
        $journals = $user->journals()
            ->whereYear('entry_date', $year)
            ->whereMonth('entry_date', $month)
            ->get()
            ->keyBy(fn($j) => $j->entry_date->format('d'));

        return view('journals.calendar', compact('journals', 'year', 'month'));
    }

    public function notebooks(): View
    {
        $user = auth()->user();
        $notebooks = $user->notebooks()->ordered()->get();

        return view('journals.notebooks', compact('notebooks'));
    }

    public function storeNotebook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $user = auth()->user();
        $validated['user_id'] = $user->id;
        $validated['order'] = $user->notebooks()->count();

        Notebook::create($validated);

        return redirect()->route('journals.notebooks')->with('success', 'Notebook created successfully!');
    }

    public function updateNotebook(Request $request, Notebook $notebook): RedirectResponse
    {
        $this->authorize('update', $notebook);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $notebook->update($validated);

        return redirect()->route('journals.notebooks')->with('success', 'Notebook updated successfully!');
    }

    public function destroyNotebook(Notebook $notebook): RedirectResponse
    {
        $this->authorize('delete', $notebook);
        $notebook->delete();

        return redirect()->route('journals.notebooks')->with('success', 'Notebook deleted successfully!');
    }

    public function setPin(Request $request, Journal $journal): RedirectResponse
    {
        $this->authorize('update', $journal);

        $validated = $request->validate([
            'pin_code' => 'required|string|size:4|regex:/^\d+$/',
        ]);

        $journal->setPin($validated['pin_code']);

        return back()->with('success', 'PIN set successfully!');
    }

    public function verifyPin(Request $request, Journal $journal)
    {
        $this->authorize('view', $journal);

        $validated = $request->validate([
            'pin_code' => 'required|string',
        ]);

        if ($journal->verifyPin($validated['pin_code'])) {
            session()->put("journal_pin_{$journal->id}", true);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 401);
    }

    public function exportPdf(Journal $journal)
    {
        $this->authorize('view', $journal);

        $pdf = Pdf::loadView('journals.pdf', compact('journal'));
        return $pdf->download("journal_{$journal->id}.pdf");
    }

    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / 200));
    }
}
