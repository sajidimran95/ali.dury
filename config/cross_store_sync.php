<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-store product + stock sync (Dury Wholesale ↔ ali.dury)
    |--------------------------------------------------------------------------
    | Product create is pushed to the peer. Sale prices are copied only on first
    | create; later edits stay local. Stock deltas sync both ways by SKU.
    */

    'enabled' => env('CROSS_STORE_SYNC_ENABLED', false),

    'peer_url' => rtrim((string) env('CROSS_STORE_SYNC_PEER_URL', ''), '/'),

    'token' => env('CROSS_STORE_SYNC_TOKEN', ''),

    'business_id' => (int) env('CROSS_STORE_SYNC_BUSINESS_ID', 1),

    'location_id' => (int) env('CROSS_STORE_SYNC_LOCATION_ID', 1),

    'timeout' => (int) env('CROSS_STORE_SYNC_TIMEOUT', 8),

    /*
    | Wholesale should be true. Child (ali.dury) false.
    | Reconcile uses master stock as source of truth for mismatches.
    */
    'is_master' => filter_var(env('CROSS_STORE_SYNC_IS_MASTER', false), FILTER_VALIDATE_BOOLEAN),

    'reconcile_enabled' => filter_var(env('CROSS_STORE_SYNC_RECONCILE', true), FILTER_VALIDATE_BOOLEAN),

    // Shown in stock history: "Stock sync (from …)"
    'site_name' => env('CROSS_STORE_SYNC_SITE_NAME', env('APP_NAME', 'Store')),

    'peer_site_name' => env('CROSS_STORE_SYNC_PEER_SITE_NAME', 'Peer store'),

];
