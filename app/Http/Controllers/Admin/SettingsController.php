<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AppConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** App settings returned by GET /app/config (super admin only). */
class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['config' => AppConfig::all()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $version = ['required', 'regex:/^\d+\.\d+\.\d+$/'];
        $data = $request->validate([
            'android_min_version' => $version,
            'ios_min_version' => $version,
            'latest_version' => $version,
            'maintenance_message' => ['nullable', 'string', 'max:300'],
            'support_email' => ['nullable', 'email', 'max:100'],
            'support_whatsapp' => ['nullable', 'regex:/^\+?\d{10,15}$/'],
            'features' => ['array'],
        ], ['regex' => 'Use a version like 1.2.0 (or a phone number with digits only).']);

        $data['maintenance'] = $request->boolean('maintenance');
        // Every known feature is saved; an unticked box means off.
        $data['features'] = collect(config('sanatan.app.features'))
            ->mapWithKeys(fn ($on, $key) => [$key => (bool) ($request->input("features.{$key}") ?? false)])
            ->all();

        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });

        return back()->with('status', 'Settings saved. The app picks them up on next start.');
    }
}
