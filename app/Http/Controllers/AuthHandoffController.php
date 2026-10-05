<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthHandoffController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(403, 'No handoff token provided.');
        }

        $cacheKey = 'auth_handoff_' . $token;
        $userId = Cache::get($cacheKey);

        if (!$userId) {
            abort(403, 'Invalid or expired handoff token.');
        }

        // Token is valid, log the user in
        Auth::loginUsingId($userId);

        // Delete the token so it can't be reused
        Cache::forget($cacheKey);

        return redirect()->intended(config('fortify.home', '/dashboard'));
    }
}
