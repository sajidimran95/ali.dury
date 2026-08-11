<?php
/**
 * Ecommerce PWA icons from brand logo mark (orange "J" tile).
 * Run: php public/pwa/generate_ecom_icons.php
 */

$outDir = __DIR__;

if (! function_exists('imagecreatetruecolor')) {
    fwrite(STDERR, "GD extension required\n");
    exit(1);
}

function makeEcomLogoIcon(int $size, string $path): void
{
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);

    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    // Match store logo mark: #e85d04 rounded tile + white J
    $orange = imagecolorallocate($im, 232, 93, 4);
    $white = imagecolorallocate($im, 255, 255, 255);

    $pad = (int) round($size * 0.04);
    // solid brand fill (PWA prefers solid; maskable works better with edge padding later)
    imagefilledrectangle($im, 0, 0, $size - 1, $size - 1, $orange);

    // Soft rounded corners by painting transparent corners... skip for solid maskable feel

    // Draw "J" with large built-in font scaled
    $font = 5;
    $text = 'J';
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);
    $tmp = imagecreatetruecolor($tw + 4, $th + 4);
    imagesavealpha($tmp, true);
    $tClear = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
    imagefill($tmp, 0, 0, $tClear);
    $tWhite = imagecolorallocate($tmp, 255, 255, 255);
    imagestring($tmp, $font, 2, 2, $text, $tWhite);

    $targetW = (int) round($size * 0.42);
    $targetH = (int) round($targetW * (($th + 4) / ($tw + 4)));
    $dstX = (int) (($size - $targetW) / 2);
    $dstY = (int) (($size - $targetH) / 2);
    imagecopyresampled($im, $tmp, $dstX, $dstY, 0, 0, $targetW, $targetH, $tw + 4, $th + 4);
    imagedestroy($tmp);

    // Optional white ring for depth (logo-like)
    $border = (int) max(2, round($size * 0.0));
    if ($border > 0) {
        // no-op
    }

    imagepng($im, $path, 6);
    imagedestroy($im);
}

makeEcomLogoIcon(192, $outDir.'/ecom-icon-192.png');
makeEcomLogoIcon(512, $outDir.'/ecom-icon-512.png');
makeEcomLogoIcon(180, $outDir.'/ecom-apple-touch-icon.png');

echo "Generated ecommerce logo icons in {$outDir}\n";
