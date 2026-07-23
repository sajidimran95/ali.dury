<?php

namespace Modules\Ecommerce\Support;

use App\Contact;

class WholesaleAccount
{
    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public static function status(?Contact $contact): string
    {
        if (! $contact) {
            return self::APPROVED; // guests browse freely
        }

        $status = trim((string) ($contact->custom_field6 ?? ''));
        if ($status === '') {
            // Legacy ecommerce accounts without status are treated as approved
            return self::APPROVED;
        }

        return in_array($status, [self::PENDING, self::APPROVED, self::REJECTED], true)
            ? $status
            : self::APPROVED;
    }

    public static function isApproved(?Contact $contact): bool
    {
        return self::status($contact) === self::APPROVED;
    }

    public static function isPending(?Contact $contact): bool
    {
        return self::status($contact) === self::PENDING;
    }

    public static function setStatus(Contact $contact, string $status): void
    {
        $contact->custom_field6 = $status;
        $contact->save();
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::PENDING => 'Pending approval',
            self::REJECTED => 'Rejected',
            default => 'Approved',
        };
    }
}
