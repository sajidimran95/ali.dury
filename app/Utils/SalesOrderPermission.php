<?php

namespace App\Utils;

use App\Transaction;
use App\User;

/**
 * Sales order edit/delete permission helpers (all vs own).
 */
class SalesOrderPermission
{
    public static function canUpdate(?User $user, Transaction $transaction): bool
    {
        if (! $user || $transaction->type !== 'sales_order') {
            return false;
        }

        if ($user->can('so.update')) {
            return true;
        }

        return $user->can('so.update_own')
            && (int) $transaction->created_by === (int) $user->id;
    }

    public static function canDelete(?User $user, Transaction $transaction): bool
    {
        if (! $user || $transaction->type !== 'sales_order') {
            return false;
        }

        if ($user->can('so.delete')) {
            return true;
        }

        return $user->can('so.delete_own')
            && (int) $transaction->created_by === (int) $user->id;
    }

    public static function canUpdateAny(?User $user): bool
    {
        return $user && ($user->can('so.update') || $user->can('so.update_own'));
    }

    public static function canDeleteAny(?User $user): bool
    {
        return $user && ($user->can('so.delete') || $user->can('so.delete_own'));
    }
}
