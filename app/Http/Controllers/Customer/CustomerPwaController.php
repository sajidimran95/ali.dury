<?php

namespace App\Http\Controllers\Customer;

use App\Business;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * PWA for customer portal only (scoped to /customer).
 * Separate from Sale / POS / Ecommerce PWAs.
 */
class CustomerPwaController extends Controller
{
    protected function context(): array
    {
        $business = null;
        if (auth('customer')->check()) {
            $business = Business::find(auth('customer')->user()->business_id);
        }
        if (! $business) {
            $business = Business::query()->orderBy('id')->first();
        }

        $name = ($business->name ?? config('app.name', 'Customer')).' Customer';

        return [
            'business' => $business,
            'app_name' => $name,
            'short_name' => mb_substr($name, 0, 12),
            'theme_color' => '#e11d48',
            'background_color' => '#0f172a',
        ];
    }

    public function manifest(Request $request)
    {
        $ctx = $this->context();
        $icons = [
            [
                'src' => asset('pwa/customer-icon-192.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('pwa/customer-icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('pwa/customer-icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ];

        if (! file_exists(public_path('pwa/customer-icon-192.png'))) {
            $icons = [
                ['src' => asset('pwa/ecom-icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('pwa/ecom-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('pwa/ecom-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ];
        }

        $manifest = [
            'id' => url('/customer').'#customer-pwa',
            'name' => $ctx['app_name'],
            'short_name' => $ctx['short_name'],
            'description' => 'Customer app — orders, prices, invoices',
            'start_url' => url('/customer/login').'?source=pwa',
            'scope' => url('/customer').'/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => $ctx['background_color'],
            'theme_color' => $ctx['theme_color'],
            'categories' => ['business', 'shopping'],
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
        $offline = url('/customer/pwa/offline');
        $cache = 'japspos-customer-pwa-v'.preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $version);

        $shell = json_encode([
            url('/customer'),
            url('/customer/login'),
            url('/customer/home'),
            $offline,
            asset('pwa/customer-icon-192.png'),
            asset('pwa/customer-icon-512.png'),
        ]);

        $js = <<<JS
/* Customer portal service worker — scope /customer only */
const CACHE_NAME = '{$cache}';
const OFFLINE_URL = '{$offline}';
const SHELL = {$shell};

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((c) => c.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k.startsWith('japspos-customer-pwa-') && k !== CACHE_NAME).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (!url.pathname.startsWith('/customer')) return;

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
            'Service-Worker-Allowed' => '/customer/',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    public function offline()
    {
        $ctx = $this->context();

        return response()->view('customer.pwa_offline', $ctx)->header('Cache-Control', 'no-cache');
    }
}
