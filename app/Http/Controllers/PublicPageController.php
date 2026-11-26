<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public web pages the app stores ask for: privacy policy, terms and the
 * account-deletion page. Text edited in Admin → Pages wins; while a page is
 * empty there, the built-in default text is shown.
 */
class PublicPageController extends Controller
{
    public function privacy(Request $request): View
    {
        return $this->page($request, 'privacy', 'Privacy Policy');
    }

    public function terms(Request $request): View
    {
        return $this->page($request, 'terms', 'Terms & Conditions');
    }

    public function deleteAccount(): View
    {
        return view('public.delete-account');
    }

    /** A deletion request from someone who no longer has the app; lands in Admin → Feedback. */
    public function requestDeletion(Request $request): RedirectResponse
    {
        // Accounts sign in with Google, so the request names the email address.
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:191'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $email = strtolower(trim($data['email']));

        Feedback::create([
            'message' => "ACCOUNT DELETION REQUEST for {$email}."
                .(filled($data['note'] ?? null) ? "\nNote: {$data['note']}" : ''),
            'contact' => $email,
            'platform' => 'web',
        ]);

        return redirect()->route('public.delete-account')->with('sent', true);
    }

    private function page(Request $request, string $slug, string $fallbackTitle): View
    {
        $lang = in_array($request->query('lang'), ['en', 'hi', 'gu'], true) ? $request->query('lang') : 'en';
        app()->setLocale($lang);

        $page = Page::find($slug);
        $body = trim((string) $page?->tr('body'));

        return view('public.page', [
            'slug' => $slug,
            'title' => $page?->tr('title') ?: $fallbackTitle,
            'body' => $body,
            'lang' => $lang,
        ]);
    }
}
