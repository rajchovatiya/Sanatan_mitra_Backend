<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    /** Permanently deletes the account and its data (required by the app stores). */
    public function destroy(Request $request): Response
    {
        $user = $request->user();
        $files = array_filter([$user->photo_path, $user->business_logo_path]);

        // Devices and saved kundlis go with the user through their foreign keys.
        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        Storage::disk('public')->delete($files);

        return response()->noContent();
    }
}
