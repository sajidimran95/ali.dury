<?php

namespace Modules\AiAssistance\Services;

use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\AiAssistance\Entities\AiAdminAlert;
use Modules\AiAssistance\Entities\AiAssistanceSetting;

class AdminAlertService
{
    public const TYPE_PO_COST = 'po_cost';

    public const TYPE_INVOICE = 'invoice';

    public const TYPE_PRICE_EDIT = 'price_edit';

    /** @var array<int, array<string, array{type: string, meta: array, rows: list<array>}>> */
    protected static array $pending = [];

    protected static bool $flushRegistered = false;

    public static function tableReady(): bool
    {
        static $ready = null;
        if ($ready === null) {
            try {
                $ready = Schema::hasTable('ai_admin_alerts');
            } catch (\Throwable $e) {
                $ready = false;
            }
        }

        return $ready;
    }

    public static function canView($user, int $business_id): bool
    {
        if (! $user || $business_id < 1) {
            return false;
        }

        return $user->can('superadmin')
            || $user->hasRole('Admin#'.$business_id)
            || $user->can('ai.settings');
    }

    /**
     * Buffer a change row; rows sharing a group key become one alert when the request ends.
     */
    public function queue(int $business_id, string $type, string $group, array $row, array $meta = []): void
    {
        if ($business_id < 1 || ! self::tableReady()) {
            return;
        }

        if (! isset(self::$pending[$business_id][$group])) {
            self::$pending[$business_id][$group] = ['type' => $type, 'meta' => $meta, 'rows' => []];
        }
        self::$pending[$business_id][$group]['rows'][] = $row;

        if (! self::$flushRegistered) {
            self::$flushRegistered = true;
            app()->terminating(function () {
                app(self::class)->flush();
            });
        }
    }

    public function flush(): void
    {
        $pending = self::$pending;
        self::$pending = [];
        self::$flushRegistered = false;

        foreach ($pending as $business_id => $groups) {
            foreach ($groups as $group) {
                try {
                    if ($group['type'] === self::TYPE_PO_COST) {
                        $this->flushPoCost((int) $business_id, $group['meta'], $group['rows']);
                    } elseif ($group['type'] === self::TYPE_PRICE_EDIT) {
                        $this->flushPriceEdits((int) $business_id, $group['meta'], $group['rows']);
                    }
                } catch (\Throwable $e) {
                    Log::warning('JapsAI admin alert flush failed: '.$e->getMessage());
                }
            }
        }
    }

    public function record(
        int $business_id,
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        array $meta = [],
        bool $email = false
    ): ?AiAdminAlert {
        if ($business_id < 1 || ! self::tableReady()) {
            return null;
        }

        $alert = AiAdminAlert::create([
            'business_id' => $business_id,
            'type' => $type,
            'title' => mb_substr($title, 0, 255),
            'message' => $message,
            'url' => $url ? mb_substr($url, 0, 500) : null,
            'meta' => $meta,
            'created_by' => auth()->id(),
        ]);

        if ($email && $this->emailEnabled($business_id)) {
            $alert->emailed = $this->emailAdmins($business_id, $alert);
            $alert->save();
        }

        return $alert;
    }

    /**
     * Invoice upload produced lines that need a person: no catalog match, missing price, or out-of-tolerance.
     */
    public function recordInvoiceResult(int $business_id, array $result): void
    {
        $unmatched = array_values(array_filter((array) ($result['unmatched'] ?? [])));
        $missingPrice = array_values(array_filter((array) ($result['missing_price'] ?? [])));
        $exceptions = (array) ($result['exceptions'] ?? []);

        if ($unmatched === [] && $missingPrice === [] && $exceptions === []) {
            return;
        }

        $ref = (string) ($result['ref_no'] ?? '');
        $lines = [];
        if ($unmatched !== []) {
            $lines[] = count($unmatched).' line(s) not in catalog: '.implode(', ', array_slice($unmatched, 0, 10))
                .(count($unmatched) > 10 ? ' …' : '');
        }
        if ($missingPrice !== []) {
            $lines[] = count($missingPrice).' line(s) missing invoice price: '.implode(', ', array_slice($missingPrice, 0, 10));
        }
        if ($exceptions !== []) {
            $labels = [];
            foreach (array_slice($exceptions, 0, 10) as $ex) {
                $labels[] = is_array($ex)
                    ? trim(($ex['label'] ?? $ex['name'] ?? 'Item').' '.($ex['msg'] ?? ''))
                    : (string) $ex;
            }
            $lines[] = count($exceptions).' exception(s) outside tolerance: '.implode('; ', $labels);
        }
        $lines[] = 'Sell prices were not changed. PO was not sent to the vendor.';

        $title = $ref !== ''
            ? 'Invoice upload on PO '.$ref.' needs review'
            : 'Invoice upload needs review';

        $this->record(
            $business_id,
            self::TYPE_INVOICE,
            $title,
            implode("\n", $lines),
            $result['edit_url'] ?? null,
            ['po_id' => $result['po_id'] ?? null, 'ref_no' => $ref],
            true
        );
    }

    public function latest(int $business_id, int $limit = 25): array
    {
        if (! self::tableReady()) {
            return [];
        }

        return AiAdminAlert::where('business_id', $business_id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (AiAdminAlert $a) => $a->toPayload())
            ->all();
    }

    public function unreadCount(int $business_id): int
    {
        if (! self::tableReady()) {
            return 0;
        }

        return AiAdminAlert::where('business_id', $business_id)->whereNull('read_at')->count();
    }

    public function markAllRead(int $business_id): int
    {
        if (! self::tableReady()) {
            return 0;
        }

        return AiAdminAlert::where('business_id', $business_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sinceText(int $business_id, \DateTimeInterface $since, int $limit = 10): string
    {
        if (! self::tableReady()) {
            return '';
        }

        $rows = AiAdminAlert::where('business_id', $business_id)
            ->where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
        if ($rows->isEmpty()) {
            return '';
        }

        $out = ['Admin alerts (last 24 hours): '.$rows->count()];
        foreach ($rows as $i => $a) {
            $out[] = ($i + 1).'. '.$a->title;
        }

        return implode("\n", $out);
    }

    public function spokenSummary(int $business_id, int $limit = 3): string
    {
        if (! self::tableReady()) {
            return '';
        }

        $rows = AiAdminAlert::where('business_id', $business_id)
            ->whereNull('read_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('title');
        if ($rows->isEmpty()) {
            return '';
        }

        $total = $this->unreadCount($business_id);
        $parts = ['Admin alerts: '.$total.' unread.'];
        foreach ($rows as $i => $title) {
            $parts[] = ($i + 1).'. '.$title.'.';
        }

        return implode(' ', $parts);
    }

    protected function flushPoCost(int $business_id, array $meta, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $ref = (string) ($meta['po_ref'] ?? '');
        $lines = [];
        foreach (array_slice($rows, 0, 15) as $r) {
            $lines[] = $r['product'].($r['sku'] ? ' ('.$r['sku'].')' : '').': '
                .$this->money($r['old_cost']).' → '.$this->money($r['new_cost']);
        }
        if (count($rows) > 15) {
            $lines[] = '… and '.(count($rows) - 15).' more';
        }
        $lines[] = 'Catalog purchase cost updated to match the PO. Sell prices were not changed.';

        $this->record(
            $business_id,
            self::TYPE_PO_COST,
            'PO '.$ref.': '.count($rows).' item cost(s) changed',
            implode("\n", $lines),
            $meta['url'] ?? null,
            ['po_id' => $meta['po_id'] ?? null, 'po_ref' => $ref, 'count' => count($rows)],
            true
        );
    }

    protected function flushPriceEdits(int $business_id, array $meta, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $who = $meta['user'] ?? null;
        $lines = [];
        foreach (array_slice($rows, 0, 15) as $r) {
            $bits = [];
            if (isset($r['old_sell'])) {
                $bits[] = 'sell '.$this->money($r['old_sell']).' → '.$this->money($r['new_sell']);
            }
            if (isset($r['old_cost'])) {
                $bits[] = 'cost '.$this->money($r['old_cost']).' → '.$this->money($r['new_cost']);
            }
            $lines[] = $r['product'].($r['sku'] ? ' ('.$r['sku'].')' : '').': '.implode(', ', $bits);
        }
        if (count($rows) > 15) {
            $lines[] = '… and '.(count($rows) - 15).' more';
        }
        if (! empty($meta['source'])) {
            $lines[] = 'Source: '.$meta['source'].($who ? ' by '.$who : '');
        }

        $title = count($rows) === 1
            ? 'Price changed: '.$rows[0]['product']
            : count($rows).' product prices changed';

        $url = null;
        if (count($rows) === 1 && ! empty($rows[0]['product_id'])) {
            $url = action([\App\Http\Controllers\ProductController::class, 'edit'], [$rows[0]['product_id']]);
        }

        $this->record(
            $business_id,
            self::TYPE_PRICE_EDIT,
            $title,
            implode("\n", $lines),
            $url,
            ['count' => count($rows), 'source' => $meta['source'] ?? null],
            false
        );
    }

    protected function emailEnabled(int $business_id): bool
    {
        try {
            $settings = AiAssistanceSetting::forBusiness($business_id);

            return ! Schema::hasColumn('ai_assistance_settings', 'alert_email_enabled')
                || (bool) $settings->alert_email_enabled;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function emailAdmins(int $business_id, AiAdminAlert $alert): bool
    {
        try {
            $to = [];
            $digest = trim((string) (AiAssistanceSetting::forBusiness($business_id)->digest_email ?? ''));
            if ($digest !== '' && filter_var($digest, FILTER_VALIDATE_EMAIL)) {
                $to[] = $digest;
            }

            $users = User::where('business_id', $business_id)
                ->where('status', 'active')
                ->whereNotNull('email')
                ->get();
            foreach ($users as $u) {
                $email = trim((string) $u->email);
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $u->hasRole('Admin#'.$business_id)) {
                    $to[] = $email;
                }
            }

            $to = array_values(array_unique($to));
            if ($to === []) {
                return false;
            }

            $body = $alert->title."\n\n".$alert->message
                .($alert->url ? "\n\nOpen: ".$alert->url : '')
                ."\n\n— JapsAI admin alert";

            Mail::raw($body, function ($m) use ($to, $alert) {
                $m->to($to)->subject('JapsAI alert: '.$alert->title);
            });

            return true;
        } catch (\Throwable $e) {
            Log::warning('JapsAI admin alert email failed: '.$e->getMessage());

            return false;
        }
    }

    protected function money($n): string
    {
        return number_format((float) $n, 2);
    }
}
