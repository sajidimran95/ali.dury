<?php

namespace App\Services;

use App\Business;
use App\Contact;
use App\Notifications\CustomerNotification;
use App\NotificationTemplate;
use App\Transaction;
use App\User;
use App\Utils\NotificationUtil;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CreditControlService
{
    /** Named individuals authorized to settle credit/overdue outstanding balances. */
    public const AUTHORIZED_SETTLER_NAMES = [
        'john jr',
        'john jr.',
        'john junior',
        'john',
        'sanjay',
    ];

    public const SETTLE_PERMISSION = 'customer.settle_credit_balance';

    /** Shipping statuses that move goods toward the customer. */
    public const BLOCKED_SHIPPING_STATUSES = ['packed', 'shipped', 'delivered'];

    protected Util $util;

    protected NotificationUtil $notificationUtil;

    public function __construct(Util $util, NotificationUtil $notificationUtil)
    {
        $this->util = $util;
        $this->notificationUtil = $notificationUtil;
    }

    /**
     * Customer sell outstanding (invoice due), rounded to 2 decimal places.
     */
    public function getOutstandingBalance(?int $contactId): float
    {
        if (empty($contactId)) {
            return 0.0;
        }

        $due = (float) $this->util->getContactDue($contactId);

        // getContactDue mixes purchase dues; for credit control use sell-side due only.
        $row = Contact::where('contacts.id', $contactId)
            ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->select(
                DB::raw("SUM(IF(t.status = 'final' AND t.type = 'sell', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.status = 'final' AND t.type = 'sell', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_paid"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid")
            )
            ->first();

        if (! $row) {
            return round(max(0, $due), 2);
        }

        $outstanding = (float) $row->total_invoice
            - (float) $row->total_paid
            + (float) $row->opening_balance
            - (float) $row->opening_balance_paid
            - (float) $row->total_sell_return
            + (float) $row->sell_return_paid;

        return round(max(0, $outstanding), 2);
    }

    public function hasOverdueOutstanding(?int $contactId): bool
    {
        if (empty($contactId)) {
            return false;
        }

        return Transaction::where('contact_id', $contactId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->OverDue()
            ->exists();
    }

    public function isCreditLimitExceeded(?int $contactId, float $additionalDue = 0.0): bool
    {
        if (empty($contactId)) {
            return false;
        }

        $contact = Contact::find($contactId);
        if (empty($contact) || ! empty($contact->is_default)) {
            return false;
        }

        $creditLimit = $contact->credit_limit;
        if ($creditLimit === null || $creditLimit === '') {
            return false;
        }

        $outstanding = $this->getOutstandingBalance($contactId) + round((float) $additionalDue, 2);

        return $outstanding > round((float) $creditLimit, 2);
    }

    /**
     * Credit/overdue control is active when limit exceeded or overdue balance exists.
     */
    public function isCreditControlActive(?int $contactId, float $additionalDue = 0.0): bool
    {
        if (empty($contactId)) {
            return false;
        }

        if ($this->hasOverdueOutstanding($contactId)) {
            return true;
        }

        return $this->isCreditLimitExceeded($contactId, $additionalDue);
    }

    /**
     * Shipment of new orders is blocked until outstanding is fully cleared
     * while credit control is (or was) active via hold flag / live status.
     */
    public function shouldHoldNewOrderShipments(?int $contactId, float $additionalDue = 0.0): bool
    {
        if (empty($contactId)) {
            return false;
        }

        if ($this->getOutstandingBalance($contactId) + round($additionalDue, 2) <= 0) {
            return false;
        }

        return $this->isCreditControlActive($contactId, $additionalDue);
    }

    public function assertCanShipTransaction(Transaction $transaction): void
    {
        $status = $transaction->shipping_status;
        // Evaluated by callers before applying a new status; this checks current hold rules.
        if (! $this->isShipmentBlockedForContact((int) $transaction->contact_id, (int) ($transaction->credit_shipment_hold ?? 0))) {
            return;
        }

        throw new \App\Exceptions\CreditShipmentHoldException(
            __('lang_v1.credit_shipment_hold_blocked')
        );
    }

    public function assertCanAdvanceShippingStatus(Transaction $transaction, ?string $newStatus): void
    {
        if (empty($newStatus) || $newStatus === 'cancelled' || $newStatus === 'ordered') {
            return;
        }

        if (! in_array($newStatus, self::BLOCKED_SHIPPING_STATUSES, true)) {
            return;
        }

        if (! $this->isShipmentBlockedForContact((int) $transaction->contact_id, (int) ($transaction->credit_shipment_hold ?? 0))) {
            return;
        }

        throw new \App\Exceptions\CreditShipmentHoldException(
            __('lang_v1.credit_shipment_hold_blocked')
        );
    }

    public function isShipmentBlockedForContact(?int $contactId, int $orderHoldFlag = 0): bool
    {
        if (empty($contactId)) {
            return false;
        }

        $outstanding = $this->getOutstandingBalance($contactId);
        if ($outstanding <= 0) {
            return false;
        }

        // Held new orders stay blocked until outstanding is fully cleared.
        if ($orderHoldFlag) {
            return true;
        }

        return $this->isCreditControlActive($contactId);
    }

    public function userCanSettleCreditBalance(?User $user): bool
    {
        if (empty($user)) {
            return false;
        }

        if ($user->can(self::SETTLE_PERMISSION)) {
            return true;
        }

        return $this->userMatchesAuthorizedSettlerName($user);
    }

    public function userMatchesAuthorizedSettlerName(User $user): bool
    {
        $full = Str::lower(trim(preg_replace(
            '/\s+/',
            ' ',
            trim(($user->surname ?? '').' '.($user->first_name ?? '').' '.($user->last_name ?? ''))
        )));

        $firstLast = Str::lower(trim(preg_replace(
            '/\s+/',
            ' ',
            trim(($user->first_name ?? '').' '.($user->last_name ?? ''))
        )));

        $first = Str::lower(trim((string) ($user->first_name ?? '')));

        foreach (self::AUTHORIZED_SETTLER_NAMES as $allowed) {
            if ($full === $allowed || $firstLast === $allowed || $first === $allowed) {
                // "john" must not match "john jr" via first-name-only when surname/last is jr
                if ($allowed === 'john') {
                    $blob = $full.' '.$firstLast;
                    if (Str::contains($blob, 'jr') || Str::contains($blob, 'junior')) {
                        continue;
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Block sell-side settlement while credit control is active, unless authorized.
     */
    public function assertCanSettleOutstanding(?int $contactId, ?User $user): void
    {
        if (empty($contactId) || ! $this->isCreditControlActive($contactId)) {
            return;
        }

        if ($this->getOutstandingBalance($contactId) <= 0) {
            return;
        }

        if ($this->userCanSettleCreditBalance($user)) {
            return;
        }

        throw new \App\Exceptions\CreditSettlementUnauthorizedException(
            __('lang_v1.credit_settlement_unauthorized')
        );
    }

    public function applyHoldToTransaction(Transaction $transaction, float $additionalDue = 0.0): void
    {
        if ($this->shouldHoldNewOrderShipments((int) $transaction->contact_id, $additionalDue)) {
            $transaction->credit_shipment_hold = 1;
            $transaction->save();
        }
    }

    public function releaseHoldsIfCleared(?int $contactId): void
    {
        if (empty($contactId)) {
            return;
        }

        if ($this->getOutstandingBalance($contactId) > 0) {
            return;
        }

        Transaction::where('contact_id', $contactId)
            ->where('credit_shipment_hold', 1)
            ->update(['credit_shipment_hold' => 0]);
    }

    /**
     * Notify customer when credit limit exceeded and/or overdue (deduped per day per reason).
     */
    public function notifyCustomerCreditIssues(int $businessId, ?int $contactId, ?Transaction $transaction = null): void
    {
        if (empty($contactId)) {
            return;
        }

        $contact = Contact::find($contactId);
        if (empty($contact) || ! empty($contact->is_default)) {
            return;
        }

        $reasons = [];
        if ($this->isCreditLimitExceeded($contactId)) {
            $reasons[] = 'credit_limit_exceeded';
        }
        if ($this->hasOverdueOutstanding($contactId)) {
            $reasons[] = 'overdue_balance';
        }

        if (empty($reasons)) {
            return;
        }

        $business = Business::with('currency')->find($businessId);
        if (empty($business)) {
            return;
        }

        foreach ($reasons as $reason) {
            if ($this->alreadyNotifiedToday($contactId, $reason)) {
                continue;
            }

            $this->sendCreditNotification($business, $contact, $reason, $transaction);
            $this->markNotified($contactId, $reason, $transaction);
        }
    }

    protected function alreadyNotifiedToday(int $contactId, string $reason): bool
    {
        return DB::table('activity_log')
            ->where('subject_type', Contact::class)
            ->where('subject_id', $contactId)
            ->where('description', 'credit_control_'.$reason)
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }

    protected function markNotified(int $contactId, string $reason, ?Transaction $transaction): void
    {
        activity()
            ->performedOn(Contact::find($contactId))
            ->withProperties([
                'reason' => $reason,
                'transaction_id' => $transaction->id ?? null,
            ])
            ->log('credit_control_'.$reason);
    }

    protected function sendCreditNotification(Business $business, Contact $contact, string $reason, ?Transaction $transaction): void
    {
        $template = NotificationTemplate::where('business_id', $business->id)
            ->where('template_for', $reason)
            ->first();

        $outstanding = $this->util->num_f($this->getOutstandingBalance($contact->id), true, $business);
        $creditLimit = $this->util->num_f((float) ($contact->credit_limit ?? 0), true, $business);

        $defaults = $this->defaultNotificationCopy($reason, $contact->name, $outstanding, $creditLimit, $business->name);

        $data = [
            'subject' => $template->subject ?? $defaults['subject'],
            'email_body' => $template->email_body ?? $defaults['email_body'],
            'sms_body' => $template->sms_body ?? $defaults['sms_body'],
            'whatsapp_text' => $template->whatsapp_text ?? $defaults['sms_body'],
            'template_for' => $reason,
            'email_settings' => $business->email_settings ?? [],
            'sms_settings' => $business->sms_settings ?? [],
        ];

        if ($transaction) {
            try {
                $replaced = $this->notificationUtil->replaceTags($business, [
                    'email_body' => $data['email_body'],
                    'sms_body' => $data['sms_body'],
                    'subject' => $data['subject'],
                    'whatsapp_text' => $data['whatsapp_text'],
                ], $transaction);
                $data = array_merge($data, $replaced);
            } catch (\Exception $e) {
                // Keep defaults when tag replacement fails.
            }
        }

        $data['email_body'] = str_replace(
            ['{contact_name}', '{due_amount}', '{credit_limit}', '{business_name}', '{cumulative_due_amount}'],
            [$contact->name, $outstanding, $creditLimit, $business->name, $outstanding],
            $data['email_body']
        );
        $data['sms_body'] = str_replace(
            ['{contact_name}', '{due_amount}', '{credit_limit}', '{business_name}', '{cumulative_due_amount}'],
            [$contact->name, $outstanding, $creditLimit, $business->name, $outstanding],
            $data['sms_body']
        );
        $data['subject'] = str_replace(
            ['{contact_name}', '{due_amount}', '{credit_limit}', '{business_name}', '{cumulative_due_amount}'],
            [$contact->name, $outstanding, $creditLimit, $business->name, $outstanding],
            $data['subject']
        );

        $autoEmail = empty($template) || ! empty($template->auto_send);
        $autoSms = ! empty($template) && ! empty($template->auto_send_sms);

        if ($autoEmail && ! empty($contact->email)) {
            try {
                Notification::route('mail', [$contact->email])
                    ->notify(new CustomerNotification($data));
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            }
        }

        if (($autoSms || (empty($template) && empty($contact->email))) && ! empty($contact->mobile)) {
            try {
                $this->notificationUtil->sendSms([
                    'mobile_number' => $contact->mobile,
                    'sms_body' => $data['sms_body'],
                    'sms_settings' => $data['sms_settings'],
                ]);
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            }
        }
    }

    protected function defaultNotificationCopy(string $reason, string $name, string $outstanding, string $creditLimit, string $businessName): array
    {
        if ($reason === 'credit_limit_exceeded') {
            return [
                'subject' => 'Credit limit exceeded — '.$businessName,
                'email_body' => '<p>Dear '.$name.',</p><p>Your account has exceeded the prescribed credit limit of '.$creditLimit.'. Your outstanding balance is '.$outstanding.'. New orders may be placed, but shipment is on hold until the outstanding balance is fully cleared.</p>',
                'sms_body' => 'Dear '.$name.', your credit limit ('.$creditLimit.') is exceeded. Outstanding: '.$outstanding.'. Orders allowed; shipment held until balance is cleared. '.$businessName,
            ];
        }

        return [
            'subject' => 'Overdue balance — '.$businessName,
            'email_body' => '<p>Dear '.$name.',</p><p>You have an overdue outstanding balance of '.$outstanding.'. New orders may be placed, but shipment is on hold until the outstanding balance is fully cleared.</p>',
            'sms_body' => 'Dear '.$name.', you have an overdue balance of '.$outstanding.'. Orders allowed; shipment held until balance is cleared. '.$businessName,
        ];
    }
}
