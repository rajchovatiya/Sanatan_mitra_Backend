<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Admin accounts and the audit log (super admin only). */
class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.admins.index', ['admins' => Admin::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.admins.form', ['admin' => new Admin]);
    }

    public function store(Request $request): RedirectResponse
    {
        Admin::create($this->validated($request, null));

        return redirect()->route('admin.admins.index')->with('status', 'Admin added.');
    }

    public function edit(Admin $admin): View
    {
        return view('admin.admins.form', compact('admin'));
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $data = $this->validated($request, $admin);

        // Nobody can lock themselves out by mistake.
        if ($admin->is($request->user('admin')) && (! $data['is_active'] || $data['role'] !== Admin::ROLE_SUPER)) {
            return back()->withErrors(['role' => 'You cannot remove your own super admin access.']);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('status', 'Admin saved.');
    }

    public function audit(Request $request): View
    {
        $logs = AdminAuditLog::with('admin:id,name')
            ->when($request->integer('admin'), fn ($q, $id) => $q->where('admin_id', $id))
            ->latest('id')->paginate(50)->withQueryString();

        return view('admin.admins.audit', ['logs' => $logs, 'admins' => Admin::orderBy('name')->get(['id', 'name'])]);
    }

    private function validated(Request $request, ?Admin $admin): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin)],
            'role' => ['required', Rule::in(Admin::ROLES)],
            'password' => [$admin ? 'nullable' : 'required', Password::min(10)],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
