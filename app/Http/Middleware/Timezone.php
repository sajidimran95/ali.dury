<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Timezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $timezone = config('app.timezone');

        // Prefer business timezone from session (same source as admin)
        if (session()->has('business.time_zone') && ! empty(session('business.time_zone'))) {
            $timezone = session('business.time_zone');
        } elseif (Auth::check()) {
            $userBusiness = Auth::user()->business ?? null;
            if ($userBusiness && ! empty($userBusiness->time_zone)) {
                $timezone = $userBusiness->time_zone;
            }
        }

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
