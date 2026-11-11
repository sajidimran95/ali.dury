<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CrossStoreSyncService;
use Illuminate\Http\Request;

class CrossStoreSyncController extends Controller
{
    public function product(Request $request, CrossStoreSyncService $sync)
    {
        $result = $sync->receiveProduct($request->all());

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    public function stock(Request $request, CrossStoreSyncService $sync)
    {
        $result = $sync->receiveStock($request->all());

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    public function stockMap(CrossStoreSyncService $sync)
    {
        $map = [];
        foreach ($sync->localStockMap() as $key => $row) {
            $map[$key] = $row['qty'];
        }

        return response()->json([
            'success' => true,
            'stocks' => $map,
        ]);
    }

    public function stockAbsolute(Request $request, CrossStoreSyncService $sync)
    {
        $result = $sync->receiveStockAbsolute($request->all());

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }

    public function stockAbsoluteBatch(Request $request, CrossStoreSyncService $sync)
    {
        $items = $request->input('items', []);
        if (! is_array($items)) {
            $items = [];
        }
        $source = trim((string) $request->input('source_site', ''));
        if ($source !== '') {
            foreach ($items as $i => $item) {
                if (is_array($item) && empty($item['source_site'])) {
                    $items[$i]['source_site'] = $source;
                }
            }
        }
        $result = $sync->receiveStockAbsoluteBatch($items);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 422);
    }
}
