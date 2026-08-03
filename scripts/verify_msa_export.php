<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$b = App\Business::find(1);
$loc = App\BusinessLocation::where('business_id', 1)->orderBy('id')->first();
$settings = $b->common_settings;
if (is_string($settings)) {
    $settings = json_decode($settings, true);
}
$cat = $settings['msa_tobacco_category_id'] ?? 1;

$exp = new App\Services\Msa\MsaTobReportExporter($b, $loc, [
    'week_ending' => '2026-08-01',
    'distributor_id' => '17033752',
    'state_tax' => 'TN',
    'category_id' => $cat,
    'is_test' => true,
]);

$out = $exp->export();
$path = __DIR__ . '/../requirements/NASHVILLE_GOODS_DISTRIBUTION_TOB_17033752_20260801.txt';
file_put_contents($path, $out);

$lines = preg_split("/\r\n/", trim($out));
echo 'lines=' . count($lines) . PHP_EOL;
$types = [];
foreach ($lines as $l) {
    $t = substr($l, 0, 3);
    $types[$t] = ($types[$t] ?? 0) + 1;
}
print_r($types);

echo 'location=' . $loc->name . PHP_EOL;
echo 'sample BID len=' . (isset($lines[1]) ? strlen($lines[1]) : 0) . PHP_EOL;

foreach ($lines as $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    if (stripos($l, 'LIGHTER') === false && stripos($l, 'TORCH') === false
        && stripos($l, 'ESCO') === false && stripos($l, 'MARLBORO') === false
        && stripos($l, 'ZIPPO') === false && stripos($l, 'CIGARETTE') === false) {
        continue;
    }
    // BID + UPC14 + SKU14 + desc50 + promoDesc50 → items_per_unit at 131
    $desc = substr($l, 31, 50);
    $units = substr($l, 131, 6);
    $catc = substr($l, 144, 6);
    echo rtrim($desc) . ' | units=' . $units . ' | cat=' . $catc . PHP_EOL;
}

echo "SID stores:\n";
foreach ($lines as $l) {
    if (str_starts_with($l, 'SID')) {
        echo substr($l, 0, 90) . PHP_EOL;
    }
}

if (preg_match('/\b(THC|KRATOM|KANNA)\b/i', $out)) {
    echo "WARN: excluded keywords still present\n";
} else {
    echo "OK: no THC/Kratom/Kanna keywords\n";
}

// Check empty descriptions
$empty = 0;
foreach ($lines as $i => $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    $desc = trim(substr($l, 31, 50));
    if ($desc === '') {
        $empty++;
        echo 'EMPTY DESC line ' . ($i + 1) . ' sku=' . substr($l, 17, 14) . PHP_EOL;
    }
}
echo "empty_descriptions={$empty}\n";
echo "wrote {$path}\n";
