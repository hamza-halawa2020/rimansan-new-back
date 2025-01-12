<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    // public function handle($request, Closure $next)
    // {
    //     return $next($request)
    //         ->header('Access-Control-Allow-Origin', '*') // or specify your frontend URL
    //         ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
    //         ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    // }

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Ensure you check if the response object is valid for header modification
        if ($response instanceof Response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        return $response;
    }



}
