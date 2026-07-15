<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$b = App\Business::find(1);
echo json_encode([
    'name' => $b->name,
    'currency_id' => $b->currency_id,
    'currency' => optional(App\Currency::find($b->currency_id))->only(['id','country','code','symbol']),
    'tax_label_1' => $b->tax_label_1,
    'sell_price_tax' => $b->sell_price_tax,
    'enable_inline_tax' => $b->enable_inline_tax,
    'enable_price_tax' => $b->enable_price_tax,
    'msa_state' => ($b->common_settings ?? [])['msa_state_tax'] ?? null,
    'location' => App\BusinessLocation::find(1)?->only(['name','state','country','city']),
], JSON_PRETTY_PRINT);
