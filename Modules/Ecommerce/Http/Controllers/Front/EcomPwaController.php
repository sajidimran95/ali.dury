<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Business;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Ecommerce\Entities\EcommerceSetting;

/**
 * Storefront-only PWA (customers). Admin / POS panel is unchanged.
 */
class EcomPwaController extends Controller
{
    protected function resolveContext(): array
    {
        $settings = EcommerceSetting::query()->orderBy('id')->first();
        $business = null;

        if ($settings && ! empty($settings->business_id)) {
            $business = Business::find($settings->business_id);
        }

        if (! $business) {
            $business = Business::query()->orderBy('id')->first();
        }

        $storeName = trim((string) ($settings->store_name ?? ''))
            ?: ($business->name ?? config('app.name', 'Store'));

        return [
            'settings' => $settings,
            'business' => $business,
            'app_name' => $storeName,
            'short_name' => mb_substr($storeName, 0, 12),
            'description' => trim((string) ($settings->seo_description ?? ''))
                ?: ('Shop '.$storeName),
            'theme_color' => '#e85d04',
            'background_color' => '#ffffff',
        ];
    }

    protected function iconUrls($settings, $business): array
    {
        $ecom192 = public_path('pwa/ecom-icon-192.png');
        $ecom512 = public_path('pwa/ecom-icon-512.png');
        $ecomApple = public_path('pwa/ecom-apple-touch-icon.png');

        // Prefer brand logo PNGs generated for ecommerce PWA
        if (file_exists($ecom192) && file_exists($ecom512)) {
            $icons = [
                [
                    'src' => asset('pwa/ecom-icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/ecom-icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/ecom-icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ];
            if (file_exists($ecomApple)) {
                $icons[] = [
                    'src' => asset('pwa/ecom-apple-touch-icon.png'),
                    'sizes' => '180x180',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ];
            }

            return $icons;
        }

        $candidates = [];

        if ($settings && ! empty($settings->store_logo)) {
            $candidates[] = 'uploads/'.ltrim(str_replace('\\', '/', $settings->store_logo), '/');
        }

        if ($business && ! empty($business->logo)) {
            $candidates[] = 'uploads/business_logos/'.$business->logo;
        }

        $candidates[] = 'uploads/ecommerce/japs-wholesale-logo.svg';

        foreach ($candidates as $rel) {
            if ($rel !== '' && file_exists(public_path($rel))) {
                $url = asset($rel);
                $type = str_ends_with(strtolower($rel), '.svg') ? 'image/svg+xml' : 'image/png';

                return [
                    ['src' => $url, 'sizes' => 'any', 'type' => $type, 'purpose' => 'any'],
                    ['src' => asset('pwa/ecom-icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => asset('pwa/ecom-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => asset('pwa/ecom-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
                ];
            }
        }

        return [
            [
                'src' => asset('pwa/ecom-icon-192.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('pwa/ecom-icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('pwa/ecom-icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ];
    }

    /**
     * Public URL for apple-touch / favicon (logo icon).
     */
    public function appleTouchIconUrl($settings = null, $business = null): string
    {
        if (file_exists(public_path('pwa/ecom-apple-touch-icon.png'))) {
            return asset('pwa/ecom-apple-touch-icon.png');
        }

        return asset('pwa/ecom-icon-192.png');
    }

    public function manifest(Request $request)
    {
        $ctx = $this->resolveContext();
        $start = url('/').'?source=pwa';
        $icons = $this->iconUrls($ctx['settings'], $ctx['business']);

        $manifest = [
            'id' => url('/').'#ecom-pwa',
            'name' => $ctx['app_name'],
            'short_name' => $ctx['short_name'],
            'description' => $ctx['description'],
            'start_url' => $start,
            'scope' => url('/').'/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation' => 'portrait-primary',
            'background_color' => $ctx['background_color'],
            'theme_color' => $ctx['theme_color'],
            'lang' => app()->getLocale() ?: 'en',
            'dir' => 'ltr',
            'categories' => ['shopping', 'business'],
            'icons' => $icons,
            'shortcuts' => [
                [
                    'name' => 'Shop',
                    'short_name' => 'Shop',
                    'description' => 'Browse the catalog',
                    'url' => url('/shop').'?source=pwa',
                    'icons' => array_slice($icons, 0, 1),
                ],
                [
                    'name' => 'Cart',
                    'short_name' => 'Cart',
                    'description' => 'View your cart',
                    'url' => url('/shop/cart').'?source=pwa',
                    'icons' => array_slice($icons, 0, 1),
                ],
            ],
        ];

        return response()
            ->json($manifest, 200, [
                'Content-Type' => 'application/manifest+json',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
    }

    public function serviceWorker()
    {
        $version = config('app.asset_version', config('constants.asset_version', '1'));
        $offline_url = url('/ecom-pwa/offline');
        $cache_name = 'japspos-ecom-pwa-v'.preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $version);

        $shell = [
            url('/'),
            url('/shop'),
            $offline_url,
            asset('pwa/ecom-icon-192.png'),
            asset('pwa/ecom-icon-512.png'),
            asset('pwa/ecom-apple-touch-icon.png'),
        ];

        $shell_json = json_encode(array_values(array_unique($shell)));

        $js = <<<JS
/* Japs ecommerce storefront PWA service worker (customers only) */
const CACHE_NAME = '{$cache_name}';
const OFFLINE_URL = '{$offline_url}';
const SHELL = {$shell_json};

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((k) => k.startsWith('japspos-ecom-pwa-') && k !== CACHE_NAME)
          .map((k) => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Do not control admin / POS paths
  if (
    url.pathname.startsWith('/pos') ||
    url.pathname.startsWith('/login') ||
    url.pathname.startsWith('/home') ||
    url.pathname.startsWith('/ecommerce') ||
    url.pathname.startsWith('/pos-pwa')
  ) {
    return;
  }

  const isDoc = req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html');
  const isStatic = /\\.(?:css|js|woff2?|ttf|png|jpe?g|gif|svg|webp|ico)(?:\\?|$)/i.test(url.pathname);

  if (isDoc) {
    event.respondWith(
      fetch(req)
        .then((res) => {
          const copy = res.clone();
          caches.open(CACHE_NAME).then((c) => c.put(req, copy)).catch(() => {});
          return res;
        })
        .catch(() => caches.match(req).then((r) => r || caches.match(OFFLINE_URL)))
    );
    return;
  }

  if (isStatic) {
    event.respondWith(
      caches.match(req).then((cached) => {
        const network = fetch(req).then((res) => {
          if (res && res.ok) {
            const copy = res.clone();
            caches.open(CACHE_NAME).then((c) => c.put(req, copy)).catch(() => {});
          }
          return res;
        }).catch(() => cached);
        return cached || network;
      })
    );
  }
});
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    public function offline()
    {
        $ctx = $this->resolveContext();

        return response()
            ->view('ecommerce::front.pwa_offline', [
                'app_name' => $ctx['app_name'],
                'theme_color' => $ctx['theme_color'],
                'background_color' => $ctx['background_color'],
            ])
            ->header('Cache-Control', 'no-cache');
    }
}
