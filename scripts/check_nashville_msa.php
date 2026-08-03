<?php

$path = __DIR__ . '/../requirements/NASHVILLE_GOODS_DISTRIBUTION_TOB_17033752_20260801.txt';
if (! is_file($path)) {
    echo "MISSING: {$path}\n";
    exit(1);
}

$raw = file_get_contents($path);
$hasBom = str_starts_with($raw, "\xEF\xBB\xBF");
$lines = preg_split("/\r\n|\n|\r/", $raw);
if (end($lines) === '') {
    array_pop($lines);
}

echo "file={$path}\n";
echo 'bytes=' . strlen($raw) . "\n";
echo 'lines=' . count($lines) . "\n";
echo 'BOM=' . ($hasBom ? 'YES (bad)' : 'no') . "\n";
echo 'uses_CRLF=' . (str_contains($raw, "\r\n") ? 'yes' : 'no') . "\n";
echo 'blank_lines=' . count(array_filter($lines, fn ($l) => trim($l) === '')) . "\n\n";

$types = [];
$issues = [];
$bidLens = [];
$emptyDesc = 0;
$thc = 0;
$lighterOk = 0;
$lighterBad = 0;
$samples = [];

foreach ($lines as $i => $l) {
    $n = $i + 1;
    $t = substr($l, 0, 3);
    $types[$t] = ($types[$t] ?? 0) + 1;

    if ($t === 'HID') {
        echo 'HID dist=' . substr($l, 3, 8)
            . ' product=' . trim(substr($l, 11, 4))
            . ' test=' . substr($l, 15, 1)
            . ' week=' . substr($l, 16, 8)
            . ' name=' . trim(substr($l, 25, 32)) . "\n";
    }

    if ($t === 'TOT') {
        echo 'TOT bids=' . substr($l, 19, 9)
            . ' sids=' . substr($l, 28, 9)
            . ' purs=' . substr($l, 37, 9) . "\n";
    }

    if ($t === 'BID') {
        $bidLens[] = strlen($l);
        $desc = trim(substr($l, 31, 50));
        $units = substr($l, 131, 6);
        $cat = substr($l, 144, 6);

        if ($desc === '') {
            $emptyDesc++;
            $issues[] = "L{$n} empty BID description (column shift risk)";
        }
        if (preg_match('/\b(THC|KRATOM|KANNA|DELTA\s*[890]|HEMP)\b/i', $desc)) {
            $thc++;
            $issues[] = "L{$n} unmonitored product still present: {$desc}";
        }
        if (preg_match('/lighter|zippo|torch/i', $desc)) {
            if ($cat === '003263') {
                $lighterOk++;
            } else {
                $lighterBad++;
                $issues[] = "L{$n} lighter category={$cat} expected 003263 | {$desc}";
            }
        }
        if (preg_match('/box of (\d+)/i', $desc, $m)) {
            $expect = str_pad($m[1], 6, '0', STR_PAD_LEFT);
            if ($units !== $expect) {
                $issues[] = "L{$n} units={$units} expected {$expect} | {$desc}";
            }
            $samples[] = rtrim(substr($desc, 0, 42)) . " | units={$units} | cat={$cat}";
        }
        if ($cat === '003292' && preg_match('/lighter|zippo/i', $desc)) {
            $issues[] = "L{$n} lighter still tagged vapor 003292";
        }
    }
}

echo "\nRecord counts:\n";
foreach ($types as $k => $v) {
    echo "  {$k} => {$v}\n";
}

$uniqueLens = array_values(array_unique($bidLens));
echo 'BID field widths unique lengths: ' . implode(',', $uniqueLens) . "\n";
echo "empty_desc={$emptyDesc} excluded_keywords={$thc} lighters_ok={$lighterOk} lighters_bad={$lighterBad}\n\n";

echo "Stores (SID):\n";
foreach ($lines as $l) {
    if (str_starts_with($l, 'SID')) {
        echo '  ' . trim(substr($l, 27, 32)) . "\n";
    }
}

echo "\nBOX OF / pack samples:\n";
foreach (array_slice($samples, 0, 10) as $s) {
    echo "  {$s}\n";
}

if (($types['HID'] ?? 0) !== 1) {
    $issues[] = 'Expected exactly 1 HID';
}
if (($types['TOT'] ?? 0) !== 1) {
    $issues[] = 'Expected exactly 1 TOT';
}
if (($types['SID'] ?? 0) < 1 || ($types['PUR'] ?? 0) < 1) {
    $issues[] = 'Missing store sales (SID/PUR)';
}
if (substr($lines[0] ?? '', 0, 3) !== 'HID') {
    $issues[] = 'File does not start with HID';
}
if (substr(end($lines) ?: '', 0, 3) !== 'TOT') {
    $issues[] = 'File does not end with TOT';
}

// Multi-file: only one file present in requirements
echo "\n=== VERDICT ===\n";
if (count($issues) === 0) {
    echo "OK — this Nashville export looks ready to upload as a single TOB test file.\n";
    echo "Use category Tobacco, week ending matching HID week, distributor 17033752.\n";
    exit(0);
}

echo "NOT OK — " . count($issues) . " issue(s):\n";
foreach ($issues as $iss) {
    echo " - {$iss}\n";
}
exit(1);
