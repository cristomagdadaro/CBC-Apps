<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // If login was from local IP, securely handoff to the public domain
        if ($request->getHost() === '192.168.36.10' && !$request->wantsJson()) {
            $token = Str::random(64);
            $userId = auth()->id();
            
            // Store token for 1 minute
            Cache::put('auth_handoff_' . $token, $userId, now()->addMinutes(1));
            
            $url = 'https://onecbc.philrice.gov.ph/auth/handoff?token=' . $token;
            return redirect()->away($url);
        }

        $home = config('fortify.home');
        return $request->wantsJson()
                    ? response()->json(['two_factor' => false])
                    : redirect()->intended($home);
    }
}
