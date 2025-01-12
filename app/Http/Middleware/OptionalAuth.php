<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token) {
            // Try to retrieve the token model
            $personalAccessToken = PersonalAccessToken::findToken($token);

            if ($personalAccessToken && $personalAccessToken->tokenable) {
                // Authenticate the user if the token is valid
                Auth::guard('sanctum')->setUser($personalAccessToken->tokenable);
            }
        }

        return $next($request);
    }
}
