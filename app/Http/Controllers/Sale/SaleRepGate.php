<?php

namespace App\Http\Controllers\Sale;

/**
 * Who may use the Sales App (/sale) — sales reps only, not admin / others.
 */
class SaleRepGate
{
    public static function isAdmin($user): bool
    {
        if (! $user || empty($user->business_id)) {
            return false;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('Admin#'.$user->business_id);
    }

    public static function hasSellAccess($user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('sell.create')
            || $user->can('direct_sell.access')
            || $user->can('direct_sell.view')
            || $user->can('view_own_sell_only');
    }

    /**
     * Sales App login allowed: active staff user, sell permission, not Admin.
     */
    public static function allows($user): bool
    {
        if (! $user) {
            return false;
        }

        if (($user->user_type ?? null) !== 'user') {
            return false;
        }

        if (empty($user->allow_login) || ($user->status ?? null) !== 'active') {
            return false;
        }

        if (self::isAdmin($user)) {
            return false;
        }

        return self::hasSellAccess($user);
    }

    public static function denyMessage($user): string
    {
        if (! $user) {
            return 'Please sign in.';
        }

        if (self::isAdmin($user)) {
            return 'Admin cannot use Sales App login. Use the main admin login instead. This portal is for sales representatives only.';
        }

        if (($user->user_type ?? null) !== 'user' || empty($user->allow_login) || ($user->status ?? null) !== 'active') {
            return __('lang_v1.login_not_allowed');
        }

        if (! self::hasSellAccess($user)) {
            return 'Your account does not have sales access. Ask admin to enable sell permissions on your role.';
        }

        return 'Access denied.';
    }
}
