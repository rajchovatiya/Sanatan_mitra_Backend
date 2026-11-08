<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Records each successful admin change: who, which action, on what, from where. Page views are not logged. */
class AuditAdminChanges
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $admin = $request->user('admin');
        // Failed validation redirects with errors flashed by *this* request; errors left over
        // from the previous attempt must not hide a successful retry.
        $failedValidation = in_array('errors', $request->session()->get('_flash.new', []), true);
        $succeeded = $response->getStatusCode() < 400 && ! $failedValidation;

        if ($admin && ! $request->isMethodSafe() && $succeeded) {
            AdminAuditLog::create([
                'admin_id' => $admin->id,
                'action' => (string) $request->route()?->getName(),
                'subject' => $this->subject($request),
                'ip' => $request->ip(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }

    /** e.g. "poster #12" from the route's bound model, or the first text field submitted. */
    private function subject(Request $request): ?string
    {
        foreach ($request->route()?->parameters() ?? [] as $name => $value) {
            if ($value instanceof Model) {
                return $name.' #'.$value->getKey();
            }
        }

        $title = $request->input('title.en') ?? $request->input('name.en') ?? $request->input('email');

        return is_string($title) ? mb_substr($title, 0, 120) : null;
    }
}
