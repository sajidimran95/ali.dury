<?php

namespace App\Http\Controllers\Sale;

use App\Business;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * PWA for sales-rep app only (scoped to /sale). Does not affect POS or ecommerce PWA.
 */
class SalePwaController extends Controller
{
    protected function context(): array
    {
        $business = null;
        if (session()->has('user.business_id')) {
            $business = Business::find(session('user.business_id'));
        }
        if (! $business) {
            $business = Business::query()->orderBy('id')->first();
        }

        $name = ($business->name ?? config('app.name', 'Sales')).' Sales';

        return [
            'business' => $business,
            'app_name' => $name,
            'short_name' => mb_substr($name, 0, 12),
            'theme_color' => '#0f766e',
            'background_color' => '#0b1220',
        ];
    }

    public function manifest(Request $request)
    {
        $ctx = $this->context();
        $icons = [
            [
                'src' => asset('pwa/sale-icon-192.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('pwa/sale-icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('pwa/sale-icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ];

        // Fallbacks if sale icons missing
        if (! file_exists(public_path('pwa/sale-icon-192.png'))) {
            $icons = [
                ['src' => asset('pwa/ecom-icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('pwa/ecom-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('pwa/ecom-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ];
        }

        $manifest = [
            'id' => url('/sale').'#sale-pwa',
            'name' => $ctx['app_name'],
            'short_name' => $ctx['short_name'],
            'description' => 'Sales representative app',
            'start_url' => url('/sale/login').'?source=pwa',
            'scope' => url('/sale').'/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => $ctx['background_color'],
            'theme_color' => $ctx['theme_color'],
            'categories' => ['business', 'productivity'],
            'icons' => $icons,
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    public function serviceWorker()
    {
        $version = config('app.asset_version', config('constants.asset_version', '1'));
        $offline = url('/sale/pwa/offline');
        $cache = 'japspos-sale-pwa-v'.preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $version);

        $shell = json_encode([
            url('/sale'),
            url('/sale/orders'),
            url('/sale/orders/create'),
            $offline,
            asset('pwa/sale-icon-192.png'),
            asset('pwa/sale-icon-512.png'),
        ]);

        $js = <<<JS
/* Sales rep app service worker — scope /sale only */
const CACHE_NAME = '{$cache}';
const OFFLINE_URL = '{$offline}';
const SHELL = {$shell};

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((c) => c.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k.startsWith('japspos-sale-pwa-') && k !== CACHE_NAME).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (!url.pathname.startsWith('/sale')) return;

  const isDoc = req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html');
  if (isDoc) {
    event.respondWith(
      fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE_NAME).then((c) => c.put(req, copy)).catch(() => {});
        return res;
      }).catch(() => caches.match(req).then((r) => r || caches.match(OFFLINE_URL)))
    );
  }
});
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/sale/',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    public function offline()
    {
        $ctx = $this->context();

        return response()->view('sale.pwa_offline', $ctx)->header('Cache-Control', 'no-cache');
    }
}
