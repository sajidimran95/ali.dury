<?php

namespace Modules\AiAssistance\Observers;

use App\PurchaseLine;
use Modules\AiAssistance\Services\PoCostSyncService;

class PurchaseLinePoCostObserver
{
    public function saved(PurchaseLine $line): void
    {
        try {
            app(PoCostSyncService::class)->syncFromPurchaseLine($line);
        } catch (\Throwable $e) {
            \Log::warning('PO cost sync skipped: '.$e->getMessage());
        }
    }
}
