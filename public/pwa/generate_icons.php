<?php
/**
 * Generate default POS PWA icons (192 & 512 PNG).
 * Run: php public/pwa/generate_icons.php
 */

$outDir = __DIR__;
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

if (! function_exists('imagecreatetruecolor')) {
    fwrite(STDERR, "GD extension required\n");
    exit(1);
}

function makePosIcon(int $size, string $path): void
{
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);

    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    $bg = imagecolorallocate($im, 15, 15, 15);
    $accent = imagecolorallocate($im, 59, 141, 221);
    $white = imagecolorallocate($im, 255, 255, 255);
    $muted = imagecolorallocate($im, 42, 42, 42);

    $pad = (int) round($size * 0.06);
    $radius = (int) round($size * 0.18);
    imagefilledrectangle($im, $pad, $pad, $size - $pad, $size - $pad, $bg);

    // Soft inner panel
    $inner = (int) round($size * 0.14);
    imagefilledrectangle($im, $inner, $inner, $size - $inner, $size - $inner, $muted);

    // Accent bar
    $barH = (int) max(4, round($size * 0.08));
    imagefilledrectangle($im, $inner, $inner, $size - $inner, $inner + $barH, $accent);

    // "POS" text
    $font = 5; // built-in GD font
    $text = 'POS';
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);
    // Scale by drawing multiple times for larger sizes is limited; use imagestring scaled via copy
    $tmpW = $tw + 4;
    $tmpH = $th + 4;
    $tmp = imagecreatetruecolor($tmpW, $tmpH);
    imagesavealpha($tmp, true);
    $tClear = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
    imagefill($tmp, 0, 0, $tClear);
    $tWhite = imagecolorallocate($tmp, 255, 255, 255);
    imagestring($tmp, $font, 2, 2, $text, $tWhite);

    $targetW = (int) round($size * 0.45);
    $targetH = (int) round($targetW * ($tmpH / $tmpW));
    $dstX = (int) (($size - $targetW) / 2);
    $dstY = (int) (($size - $targetH) / 2 + $size * 0.04);
    imagecopyresampled($im, $tmp, $dstX, $dstY, 0, 0, $targetW, $targetH, $tmpW, $tmpH);
    imagedestroy($tmp);

    // Corner accent dots for maskable safe zone feel
    $dot = (int) max(3, round($size * 0.035));
    imagefilledellipse($im, (int) ($size * 0.22), (int) ($size * 0.78), $dot, $dot, $accent);
    imagefilledellipse($im, (int) ($size * 0.78), (int) ($size * 0.78), $dot, $dot, $white);

    imagepng($im, $path);
    imagedestroy($im);
}

makePosIcon(192, $outDir.'/icon-192.png');
makePosIcon(512, $outDir.'/icon-512.png');
makePosIcon(180, $outDir.'/apple-touch-icon.png');

echo "Generated icons in {$outDir}\n";
