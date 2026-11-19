<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Permanently deletes an app user and their data (used by the app and the admin panel). */
class AccountDeleter
{
    public static function delete(User $user): void
    {
        $files = array_filter([$user->photo_path, $user->business_logo_path]);

        // Devices, saved kundlis and favourites go with the user through their foreign keys.
        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        Media::delete(...$files);
    }
}
