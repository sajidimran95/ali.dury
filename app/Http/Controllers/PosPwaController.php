<?php

namespace App\Http\Controllers;

use App\Business;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;

class PosPwaController extends Controller
{
    /**
     * Resolve business + POS settings for PWA responses.
     */
    protected function resolvePosPwaContext(?int $business_id = null): array
    {
        $business = null;

        if ($business_id) {
            $business = Business::find($business_id);
        }

        if (! $business && session()->has('user.business_id')) {
            $business = Business::find(session('user.business_id'));
        }

        if (! $business && session()->has('business')) {
            $session_business = session('business');
            $business = $session_business instanceof Business
                ? $session_business
                : Business::find($session_business->id ?? null);
        }

        if (! $business) {
            $business = Business::query()->orderBy('id')->first();
        }

        $defaults = (new BusinessUtil)->defaultPosSettings();
        $pos_settings = $defaults;

        if ($business && ! empty($business->pos_settings)) {
            $decoded = json_decode($business->pos_settings, true);
            if (is_array($decoded)) {
                $pos_settings = array_merge($defaults, $decoded);
            }
        }

        $business_name = $business->name ?? config('app.name', 'POS');

        return [
            'business' => $business,
            'pos_settings' => $pos_settings,
            'enabled' => ! array_key_exists('enable_pwa', $pos_settings) || ! empty($pos_settings['enable_pwa']),
            'app_name' => trim((string) ($pos_settings['pwa_app_name'] ?? '')) ?: $business_name,
            'short_name' => trim((string) ($pos_settings['pwa_short_name'] ?? '')) ?: 'POS',
            'description' => trim((string) ($pos_settings['pwa_description'] ?? ''))
                ?: ('Point of Sale — '.$business_name),
            'theme_color' => $this->sanitizeHex($pos_settings['pwa_theme_color'] ?? '#0f0f0f', '#0f0f0f'),
            'background_color' => $this->sanitizeHex($pos_settings['pwa_background_color'] ?? '#0f0f0f', '#0f0f0f'),
        ];
    }

    protected function sanitizeHex(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)) {
            return strtolower($value);
        }

        return $fallback;
    }

    /**
     * Icon URL list for manifest / apple touch icon.
     */
    public function iconUrls(?array $pos_settings = null, $business = null): array
    {
        $pos_settings = $pos_settings ?? [];
        $icons = [];

        $pwa_icon = $pos_settings['pwa_icon'] ?? null;
        if (! empty($pwa_icon) && file_exists(public_path('uploads/pwa_icons/'.$pwa_icon))) {
            $url = asset('uploads/pwa_icons/'.$pwa_icon);
            $icons[] = ['src' => $url, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => $url, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => $url, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'];

            return $icons;
        }

        if ($business && ! empty($business->logo) && file_exists(public_path('uploads/business_logos/'.$business->logo))) {
            $url = asset('uploads/business_logos/'.$business->logo);
            $icons[] = ['src' => $url, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => $url, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'];

            return $icons;
        }

        $icons[] = [
            'src' => asset('pwa/icon-192.png'),
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ];
        $icons[] = [
            'src' => asset('pwa/icon-512.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ];
        $icons[] = [
            'src' => asset('pwa/icon-512.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ];

        return $icons;
    }

    /**
     * Web app manifest for POS PWA install.
     */
    public function manifest(Request $request)
    {
        $ctx = $this->resolvePosPwaContext($request->filled('business_id') ? (int) $request->input('business_id') : null);

        if (! $ctx['enabled']) {
            return response()->json(['error' => 'PWA disabled'], 404);
        }

        $start_url = url('/pos/create');
        $scope = url('/');
        $icons = $this->iconUrls($ctx['pos_settings'], $ctx['business']);

        $manifest = [
            'id' => url('/pos/create'),
            'name' => $ctx['app_name'],
            'short_name' => mb_substr($ctx['short_name'], 0, 12),
            'description' => $ctx['description'],
            'start_url' => $start_url.'?source=pwa',
            'scope' => $scope.'/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation' => 'any',
            'background_color' => $ctx['background_color'],
            'theme_color' => $ctx['theme_color'],
            'lang' => app()->getLocale() ?: 'en',
            'dir' => 'ltr',
            'categories' => ['business', 'finance', 'productivity'],
            'icons' => $icons,
            'shortcuts' => [
                [
                    'name' => 'New Sale',
                    'short_name' => 'POS',
                    'description' => 'Open POS terminal',
                    'url' => url('/pos/create').'?source=pwa',
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

    /**
     * Service worker script (scoped to site root via header).
     */
    public function serviceWorker()
    {
        $version = config('app.asset_version', config('constants.asset_version', '1'));
        $offline_url = url('/pos-pwa/offline');
        $cache_name = 'japspos-pwa-v'.preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $version);

        $shell = [
            url('/pos/create'),
            $offline_url,
            asset('css/pos-sale-theme.css'),
            asset('pwa/icon-192.png'),
            asset('pwa/icon-512.png'),
        ];

        $shell_json = json_encode(array_values(array_unique($shell)));

        $js = <<<JS
/* JAPS POS Progressive Web App service worker */
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
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Never cache API / mutating POS endpoints aggressively — network first
  const isDoc = req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html');
  const isStatic = /\\.(?:css|js|woff2?|ttf|png|jpe?g|gif|svg|webp|ico|mp3|ogg)(?:\\?|$)/i.test(url.pathname);

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

    /**
     * Offline fallback page for installed PWA.
     */
    public function offline()
    {
        $ctx = $this->resolvePosPwaContext();

        return response()
            ->view('sale_pos.pwa_offline', [
                'app_name' => $ctx['app_name'],
                'theme_color' => $ctx['theme_color'],
                'background_color' => $ctx['background_color'],
            ])
            ->header('Cache-Control', 'no-cache');
    }
}
