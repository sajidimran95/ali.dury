<?php

namespace App\Http\Controllers\Concerns;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Stops the same POS sale from being saved twice (double click, retry after
 * a lost network response, keyboard shortcut + click, ...).
 *
 * pos.js sends a `pos_submit_token` that stays the same until a submit
 * succeeds. Requests carrying the same token are serialized with a lock, and
 * once one of them succeeds its response is replayed for any repeat of the
 * same cart instead of creating another transaction.
 */
trait PreventsDuplicatePosSubmit
{
    protected function guardDuplicatePosSubmit(Request $request, string $scope, Closure $callback)
    {
        $token = (string) $request->input('pos_submit_token', '');
        if ($token === '' || strlen($token) > 64 || !preg_match('/^[A-Za-z0-9._-]+$/', $token)) {
            return $callback();
        }

        $base = 'pos_submit:' . $request->session()->get('user.business_id') . ':' . auth()->id() . ':' . $scope . ':' . $token;

        // Same token but a different cart means a new sale, not a retry.
        $fingerprint = sha1(json_encode([
            $request->input('products'),
            $request->input('contact_id'),
            $request->input('status'),
            $request->input('is_suspend'),
            $request->input('final_total'),
        ]));
        $resultKey = $base . ':' . $fingerprint;

        $lock = Cache::lock($base . ':lock', 120);
        try {
            // Wait for an in-flight copy of this sale to finish, then replay its result.
            $lock->block(30);
        } catch (LockTimeoutException $e) {
            return [
                'success' => 0,
                'msg' => 'This sale is still being processed. Please check recent transactions before trying again.',
            ];
        }

        try {
            $previous = Cache::get($resultKey);
            if (is_array($previous)) {
                $previous['duplicate_submit'] = true;

                return $previous;
            }

            $response = $callback();

            if (is_array($response) && !empty($response['success'])) {
                Cache::put($resultKey, $response, now()->addHours(12));
            }

            return $response;
        } finally {
            $lock->release();
        }
    }
}
