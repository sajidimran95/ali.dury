<?php

if (! function_exists('uploadAsset')) {
    function uploadAsset($path, $directory = 'uploads')
    {
        $file = public_path($directory.'/'.$path);

        if (! is_string($path) || $path === '' || ! file_exists($file)) {
            return null;
        }

        return asset($directory.'/'.$path);
    }
}

if (! function_exists('humanFilesize')) {
    function humanFilesize($size, $precision = 2)
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;

        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }

        return round($size, $precision).$units[$i];
    }
}

/**
 * Checks if the uploaded document is an image
 */
if (! function_exists('isFileImage')) {
    function isFileImage($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $array = ['png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF'];
        $output = in_array($ext, $array) ? true : false;

        return $output;
    }
}

function isAppInstalled()
{
    $envPath = base_path('.env');

    return file_exists($envPath);
}

/**
 * Cast DB/session values for Carbon add/sub unit methods (Carbon 3 requires int|float).
 */
function carbon_numeric($value): float
{
    return (float) ($value ?? 0);
}

/**
 * Checks if pusher has credential or not
 *
 * and return boolean
 */
function isPusherEnabled()
{
    $is_pusher_enabled = false;

    if (! empty(config('broadcasting.connections.pusher.key')) && ! empty(config('broadcasting.connections.pusher.secret')) && ! empty(config('broadcasting.connections.pusher.app_id')) && ! empty(config('broadcasting.connections.pusher.options.cluster')) && (config('broadcasting.connections.pusher.driver') == 'pusher')) {
        $is_pusher_enabled = true;
    }

    return $is_pusher_enabled;
}

/**
 * Checks if user agent is mobile or not
 *
 * @return bool
 */
if (! function_exists('isMobile')) {
    function isMobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
            return true;
        } else {
            return false;
        }
    }
}

if (! function_exists('str_ordinal')) {
    /**
     * Append an ordinal indicator to a numeric value.
     *
     * @param  string|int  $value
     * @param  bool  $superscript
     * @return string
     */
    function str_ordinal($value, $superscript = false)
    {
        $number = abs($value);

        $indicators = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        $suffix = $superscript ? '<sup>'.$indicators[$number % 10].'</sup>' : $indicators[$number % 10];
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $suffix = $superscript ? '<sup>th</sup>' : 'th';
        }

        return number_format($number).$suffix;
    }
}

/**
 * Login / auth page logo.
 * Uses the business logo from System Settings when uploaded;
 * otherwise keeps the default app logo.
 */
if (! function_exists('getAuthLogoUrl')) {
    function getAuthLogoUrl(): string
    {
        $default = asset('img/logo-small.png');

        try {
            if (! isAppInstalled() || ! \Illuminate\Support\Facades\Schema::hasTable('business')) {
                return $default;
            }

            $logo = null;

            if (session()->has('user.business_id')) {
                $logo = \App\Business::where('id', session('user.business_id'))->value('logo');
            }

            if (empty($logo)) {
                $logo = \App\Business::whereNotNull('logo')
                    ->where('logo', '!=', '')
                    ->orderBy('id')
                    ->value('logo');
            }

            if (! empty($logo) && file_exists(public_path('uploads/business_logos/'.$logo))) {
                return asset('uploads/business_logos/'.$logo);
            }
        } catch (\Throwable $e) {
            // Fall back to default if DB is unavailable during install/migration.
        }

        return $default;
    }
}

/**
 * Icon artwork for each app surface.
 *
 * Every app owns a distinct colour and letter, so no two browser tabs or
 * home-screen installs ever look the same:
 *
 *   pos        #0f0f0f  "POS"   — main till
 *   sale       #e53935  "S"     — sales rep app
 *   delivery   #0f766e  "D"     — delivery driver app
 *   customer   #e11d48  "C"     — customer portal
 *   ecom       #e85d04  "J"     — storefront
 */
if (! function_exists('pwa_icon_manifest')) {
    function pwa_icon_manifest(): array
    {
        return [
            'pos' => [
                'theme' => '#0f0f0f',
                'title' => 'POS',
                'icon192' => 'icon-192.png',
                'icon512' => 'icon-512.png',
                'maskable' => 'icon-512.png',
                'apple' => 'apple-touch-icon.png',
            ],
            'sale' => [
                'theme' => '#e53935',
                'title' => 'Sales',
                'icon192' => 'sale-icon-192.png',
                'icon512' => 'sale-icon-512.png',
                'maskable' => 'sale-icon-maskable-512.png',
                'apple' => 'sale-icon-apple-touch-icon.png',
            ],
            'delivery' => [
                'theme' => '#0f766e',
                'title' => 'Delivery',
                'icon192' => 'delivery-icon-192.png',
                'icon512' => 'delivery-icon-512.png',
                'maskable' => 'delivery-icon-maskable-512.png',
                'apple' => 'delivery-icon-apple-touch-icon.png',
            ],
            'customer' => [
                'theme' => '#e11d48',
                'title' => 'Customer',
                'icon192' => 'customer-icon-192.png',
                'icon512' => 'customer-icon-512.png',
                'maskable' => 'customer-icon-maskable-512.png',
                'apple' => 'customer-icon-apple-touch-icon.png',
            ],
            'ecom' => [
                'theme' => '#e85d04',
                'title' => 'Store',
                'icon192' => 'ecom-icon-192.png',
                'icon512' => 'ecom-icon-512.png',
                'maskable' => 'ecom-icon-512.png',
                'apple' => 'ecom-apple-touch-icon.png',
            ],
        ];
    }
}

/**
 * Which app a request belongs to, inferred from the URL path.
 */
if (! function_exists('pwa_icon_app_for_request')) {
    function pwa_icon_app_for_request(): string
    {
        $segment = request()->segment(1);
        $apps = pwa_icon_manifest();

        if ($segment === 'sale') {
            return 'sale';
        }
        if ($segment === 'delivery') {
            return 'delivery';
        }
        if ($segment === 'customer') {
            return 'customer';
        }
        if ($segment === 'ecommerce' || $segment === config('ecommerce.route_prefix', 'shop')) {
            return 'ecom';
        }

        return 'pos';
    }
}

/**
 * Icon URLs for an app surface, with a cache-busting version so browsers
 * actually pick up regenerated artwork.
 *
 * @param  string|null  $app  Force a key; inferred from the request when null.
 */
if (! function_exists('app_pwa_icons')) {
    function app_pwa_icons(?string $app = null): array
    {
        $apps = pwa_icon_manifest();
        $app = $app !== null && isset($apps[$app]) ? $app : pwa_icon_app_for_request();
        $meta = $apps[$app];

        $v = config('app.asset_version', config('constants.asset_version', '1'));
        $url = function (string $file) use ($v) {
            return asset('pwa/'.$file).'?v='.$v;
        };

        return [
            'app' => $app,
            'theme' => $meta['theme'],
            'title' => $meta['title'],
            'icon192' => $url($meta['icon192']),
            'icon512' => $url($meta['icon512']),
            'maskable' => $url($meta['maskable']),
            'apple' => $url($meta['apple']),
        ];
    }
}
