<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TranslatedRules;
use App\Models\Feedback;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** App users' feedback and the fixed pages (privacy, terms, about). */
class SupportController extends Controller
{
    public function feedback(): View
    {
        $feedback = Feedback::with('user:id,mobile,name')->orderBy('is_seen')->latest('id')->paginate(30);

        return view('admin.support.feedback', compact('feedback'));
    }

    public function markSeen(Feedback $feedback): RedirectResponse
    {
        $feedback->forceFill(['is_seen' => true])->save();

        return back()->with('status', 'Marked as seen.');
    }

    public function destroyFeedback(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return back()->with('status', 'Feedback deleted.');
    }

    public function pages(): View
    {
        return view('admin.support.pages', ['pages' => Page::orderBy('slug')->get()]);
    }

    public function editPage(Page $page): View
    {
        return view('admin.support.page-form', compact('page'));
    }

    public function updatePage(Request $request, Page $page): RedirectResponse
    {
        $page->update($request->validate([
            ...TranslatedRules::for('title', max: 100),
            ...TranslatedRules::for('body', required: false, max: 0),
        ]));

        return redirect()->route('admin.pages.index')->with('status', 'Page saved.');
    }
}
