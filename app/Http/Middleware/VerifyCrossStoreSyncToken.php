<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyCrossStoreSyncToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('cross_store_sync.token');
        $provided = (string) $request->header('X-Cross-Store-Token', '');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized cross-store sync request.',
            ], 401);
        }

        return $next($request);
    }
}
