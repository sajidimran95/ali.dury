<?php

/**
 * Pre-upload gate for Altria/MSA feedback checklist.
 */
$path = __DIR__ . '/../requirements/NASHVILLE_GOODS_DISTRIBUTION_TOB_17033752_20260801.txt';
$raw = file_get_contents($path);
$lines = preg_split("/\r\n|\n|\r/", $raw);
if (end($lines) === '') {
    array_pop($lines);
}

echo "=== FILE UNDER REVIEW ===\n";
echo basename($path) . ' (' . strlen($raw) . " bytes, " . count($lines) . " lines)\n";
echo "requirements/ TOB files: 1 (only this file)\n\n";

$checks = [];
$fail = 0;

// 1) One file / one report structure
$types = [];
foreach ($lines as $l) {
    $t = substr($l, 0, 3);
    $types[$t] = ($types[$t] ?? 0) + 1;
}
$oneReport = ($types['HID'] ?? 0) === 1
    && ($types['TOT'] ?? 0) === 1
    && substr($lines[0], 0, 3) === 'HID'
    && substr(end($lines), 0, 3) === 'TOT'
    && ! str_contains($raw, "\r\n\r\n");
$checks[] = [
    'q' => 'Two files / BIDs split across reports',
    'ok' => $oneReport,
    'detail' => $oneReport
        ? 'Single TOB: HID='.($types['HID'] ?? 0).' BID='.($types['BID'] ?? 0).' SID='.($types['SID'] ?? 0).' PUR='.($types['PUR'] ?? 0).' TOT='.($types['TOT'] ?? 0)
        : 'Structure broken: '.json_encode($types),
];

// 2) Stores / sales
$stores = [];
foreach ($lines as $l) {
    if (str_starts_with($l, 'SID')) {
        $stores[] = trim(substr($l, 27, 32));
    }
}
$hasStores = count($stores) >= 1 && ($types['PUR'] ?? 0) >= 1;
$checks[] = [
    'q' => 'No stores / no sales (SID/PUR)',
    'ok' => $hasStores,
    'detail' => $hasStores
        ? count($stores).' stores, '.($types['PUR'] ?? 0).' PUR sales lines: '.implode('; ', $stores)
        : 'NO SID/PUR found — do not upload',
];

// 3) THC/Hemp/Delta/Kratom/Kanna excluded
$excludedHits = [];
foreach ($lines as $i => $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    $desc = substr($l, 31, 50);
    if (preg_match('/\b(THC|HEMP|KRATOM|KANNA|DELTA[\s-]?[89]|DELTA[\s-]?10|HH[CP]|THCA?)\b/i', $desc, $m)) {
        $excludedHits[] = 'L'.($i + 1).' '.$m[0].': '.trim($desc);
    }
}
$checks[] = [
    'q' => 'Altria/Liggett do not monitor THC/Hemp/Delta/Kratom/Kanna',
    'ok' => count($excludedHits) === 0,
    'detail' => count($excludedHits) === 0
        ? 'None of those keywords found in BID descriptions'
        : implode(' | ', array_slice($excludedHits, 0, 8)),
];

// 4) Other categories: cigarettes, cigars, moist snuff
$cats = [];
foreach ($lines as $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    $cat = substr($l, 144, 6);
    $cats[$cat] = ($cats[$cat] ?? 0) + 1;
}
$label = [
    '003231' => 'Cigarettes',
    '003211' => 'Moist Snuff',
    '003251' => 'Cigars',
    '003252' => 'Little Filtered Cigars',
    '003292' => 'E-Cig / Vapor',
    '003263' => 'Lighters',
    '003221' => 'RYO',
    '003241' => 'Pipe',
    '003261' => 'Wraps/Papers',
];
$other = [];
foreach (['003231', '003211', '003251', '003252'] as $c) {
    if (! empty($cats[$c])) {
        $other[] = ($label[$c] ?? $c).'='.$cats[$c];
    }
}
$checks[] = [
    'q' => 'Carry Cigarettes / Cigars / Moist Snuff (not only vapor)',
    'ok' => count($other) >= 2, // at least two traditional families present
    'detail' => 'Category mix: '.implode(', ', array_map(
        fn ($c, $n) => ($label[$c] ?? $c).'='.$n,
        array_keys($cats),
        array_values($cats)
    )).(count($other) ? ' | traditional: '.implode(', ', $other) : ' | WARNING few traditional tobacco SKUs'),
];

// 5) Missing description / off-shift
$emptyDesc = 0;
$badLen = 0;
$shiftSuspect = 0;
foreach ($lines as $i => $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    if (strlen($l) !== 275) {
        $badLen++;
    }
    $desc = trim(substr($l, 31, 50));
    if ($desc === '') {
        $emptyDesc++;
    }
    // promo indicator should be Y/N at fixed offset 137
    $ind = substr($l, 137, 1);
    $cat = substr($l, 144, 6);
    if (! in_array($ind, ['Y', 'N'], true) || ! preg_match('/^003\d{3}$/', $cat)) {
        $shiftSuspect++;
    }
}
$alignOk = $emptyDesc === 0 && $badLen === 0 && $shiftSuspect === 0;
$checks[] = [
    'q' => 'BID missing description / off-shifted units & category',
    'ok' => $alignOk,
    'detail' => "empty_desc={$emptyDesc}, wrong_BID_length={$badLen}, shift_suspects={$shiftSuspect} (all BID len should be 275)",
];

// 6) Lighters category 3263 and torch box of 20
$lighterWrong = [];
$torchOk = false;
$torchDetail = 'Torch lighter box of 20 not found in file';
foreach ($lines as $i => $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    $desc = substr($l, 31, 50);
    $units = substr($l, 131, 6);
    $cat = substr($l, 144, 6);
    if (preg_match('/lighter|zippo|torch/i', $desc)) {
        if ($cat !== '003263') {
            $lighterWrong[] = 'L'.($i + 1).' cat='.$cat.' '.trim($desc);
        }
    }
    if (preg_match('/torch.*lighter|lighter.*torch|xu-?49m/i', $desc) && preg_match('/box of 20|20\s*ct/i', $desc)) {
        $torchOk = ($units === '000020' && $cat === '003263');
        $torchDetail = 'found "'.trim($desc)."\" units={$units} cat={$cat}";
    }
}
$lighterOk = count($lighterWrong) === 0 && $torchOk;
$checks[] = [
    'q' => 'Lighters category 3263 + Torch Lighter box of 20 units',
    'ok' => $lighterOk,
    'detail' => $torchDetail.(count($lighterWrong) ? ' | wrong: '.implode('; ', $lighterWrong) : ' | all lighters=003263'),
];

// 7) Vapor pack units
$vaporBad = [];
$vaporOk = 0;
foreach ($lines as $i => $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    $desc = substr($l, 31, 50);
    $units = substr($l, 131, 6);
    $cat = substr($l, 144, 6);
    if ($cat !== '003292' && ! preg_match('/vape|disposable|e-?cig|pod/i', $desc)) {
        continue;
    }
    if (preg_match('/box of (\d+)/i', $desc, $m) || preg_match('/\b(\d+)\s*(pc|pcs|pk|pack|ct)\b/i', $desc, $m)) {
        $expect = str_pad((string) (int) $m[1], 6, '0', STR_PAD_LEFT);
        if ($units === $expect) {
            $vaporOk++;
        } else {
            $vaporBad[] = 'L'.($i + 1)." units={$units} expect={$expect} ".trim($desc);
        }
    }
}
$checks[] = [
    'q' => 'Vapor units = pack counts (not all 1)',
    'ok' => count($vaporBad) === 0 && $vaporOk > 0,
    'detail' => "pack-pattern OK rows={$vaporOk}, mismatches=".count($vaporBad)
        .(count($vaporBad) ? ' | '.implode('; ', array_slice($vaporBad, 0, 5)) : ''),
];

// Spacing / encoding
$checks[] = [
    'q' => 'Spacing / encoding (CRLF, no BOM, no blank rows)',
    'ok' => ! str_starts_with($raw, "\xEF\xBB\xBF") && str_contains($raw, "\r\n") && ! preg_match("/\r\n\r\n/", $raw),
    'detail' => 'CRLF + no BOM + no blank records',
];

echo "=== MSA EMAIL CHECKLIST (pre-upload) ===\n\n";
foreach ($checks as $c) {
    $mark = $c['ok'] ? 'PASS' : 'FAIL';
    if (! $c['ok']) {
        $fail++;
    }
    echo "[{$mark}] {$c['q']}\n";
    echo "       {$c['detail']}\n\n";
}

// Sample key lines
echo "=== KEY ROWS TO SPOT-CHECK ===\n";
foreach ($lines as $i => $l) {
    if (! str_starts_with($l, 'BID')) {
        continue;
    }
    $desc = trim(substr($l, 31, 50));
    $units = substr($l, 131, 6);
    $cat = substr($l, 144, 6);
    if (preg_match('/TORCH LIGHTER BOX OF 20|ESCO BARS.*BOX OF 10|MARLBORO|COPENHAGEN|SWISHER|LUX CIGARETTE/i', $desc)) {
        echo 'L'.($i + 1).": {$desc} | units={$units} | cat={$cat}\n";
    }
}

echo "\n=== UPLOAD DECISION ===\n";
if ($fail === 0) {
    echo "READY TO UPLOAD — use this SINGLE file only:\n";
    echo "  " . basename($path) . "\n";
    echo "  Distributor: 17033752 | Week ending: 2026-08-01 | Product type: TOB | Test flag: T (keep until approved)\n";
    echo "  Do NOT also upload any older (1).txt or split BID-only files.\n";
    exit(0);
}
echo "NOT READY — fix {$fail} failed check(s) first.\n";
exit(1);
