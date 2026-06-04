<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsInstalled
{
    /**
     * Ensure the application is configured (.env present).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! file_exists(base_path('.env'))) {
            abort(503, 'Application is not configured. Create a .env file from .env.example.');
        }

        return $next($request);
    }
}
