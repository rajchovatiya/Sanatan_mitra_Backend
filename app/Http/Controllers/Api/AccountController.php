<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AccountDeleter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccountController extends Controller
{
    /** Permanently deletes the account and its data (required by the app stores). */
    public function destroy(Request $request): Response
    {
        AccountDeleter::delete($request->user());

        return response()->noContent();
    }
}
