<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** App users: search, view, block and unblock. */
class AppUserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:50']])['q'] ?? ''));

        $users = User::query()
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s->where('mobile', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")))
            ->latest('id')
            ->paginate(30, ['id', 'mobile', 'name', 'city', 'language', 'status', 'last_login_at', 'created_at'])
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function show(User $user): View
    {
        $user->loadCount(['kundlis', 'favorites', 'devices']);

        return view('admin.users.show', compact('user'));
    }

    /** Blocking also logs the user out on every phone. */
    public function toggleBlock(User $user): RedirectResponse
    {
        DB::transaction(function () use ($user) {
            $block = ! $user->isBlocked();
            $user->forceFill(['status' => $block ? User::STATUS_BLOCKED : User::STATUS_ACTIVE])->save();
            if ($block) {
                $user->tokens()->delete();
            }
        });

        return back()->with('status', $user->isBlocked() ? 'User blocked and logged out.' : 'User unblocked.');
    }
}
