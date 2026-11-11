<?php

namespace App\Console\Commands;

use App\Services\CrossStoreSyncService;
use Illuminate\Console\Command;

class CrossStoreReconcileStock extends Command
{
    protected $signature = 'cross-store:reconcile-stock';

    protected $description = 'Match mismatched stock with the peer store (master qty wins)';

    public function handle(CrossStoreSyncService $sync): int
    {
        $result = $sync->reconcileStock();
        $this->info(($result['message'] ?? 'done').' fixed='.($result['fixed'] ?? 0));

        return ($result['success'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
