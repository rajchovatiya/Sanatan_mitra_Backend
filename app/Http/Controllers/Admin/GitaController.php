<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GitaRequest;
use App\Models\GitaChapter;
use App\Models\GitaVerse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The 18 chapters are fixed (edit only); verses are added per chapter. */
class GitaController extends Controller
{
    public function chapters(): View
    {
        $chapters = GitaChapter::withCount('verses')->orderBy('number')->get();

        return view('admin.gita.chapters', compact('chapters'));
    }

    public function editChapter(GitaChapter $chapter): View
    {
        return view('admin.gita.chapter-form', compact('chapter'));
    }

    public function updateChapter(GitaRequest $request, GitaChapter $chapter): RedirectResponse
    {
        $chapter->update($request->validated());

        return redirect()->route('admin.gita.chapters')->with('status', 'Chapter saved.');
    }

    public function index(Request $request): View
    {
        $chapter = GitaChapter::findOrFail($request->integer('chapter') ?: 1);
        $verses = $chapter->verses()->orderBy('verse')->paginate(50)->withQueryString();

        return view('admin.gita.verses', compact('chapter', 'verses'));
    }

    public function create(Request $request): View
    {
        return $this->form(new GitaVerse(['chapter' => $request->integer('chapter') ?: 1]));
    }

    public function store(GitaRequest $request): RedirectResponse
    {
        $verse = GitaVerse::create($request->validated());

        return $this->backToChapter($verse, 'Verse added.');
    }

    public function edit(GitaVerse $gitaVerse): View
    {
        return $this->form($gitaVerse);
    }

    public function update(GitaRequest $request, GitaVerse $gitaVerse): RedirectResponse
    {
        $gitaVerse->update($request->validated());

        return $this->backToChapter($gitaVerse, 'Verse saved.');
    }

    public function destroy(GitaVerse $gitaVerse): RedirectResponse
    {
        $gitaVerse->delete();

        return $this->backToChapter($gitaVerse, 'Verse deleted.');
    }

    private function form(GitaVerse $verse): View
    {
        return view('admin.gita.verse-form', ['verse' => $verse, 'chapters' => GitaChapter::orderBy('number')->get(['number', 'name', 'verse_count'])]);
    }

    private function backToChapter(GitaVerse $verse, string $message): RedirectResponse
    {
        return redirect()->route('admin.gita-verses.index', ['chapter' => $verse->chapter])->with('status', $message);
    }
}
