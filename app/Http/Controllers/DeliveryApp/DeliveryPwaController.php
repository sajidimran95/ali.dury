<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Business;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeliveryPwaController extends Controller
{
    protected function context(): array
    {
        $business = null;
        if (session()->has('user.business_id')) {
            $business = Business::find(session('user.business_id'));
        }
        $name = ($business->name ?? config('app.name', 'Delivery')).' Delivery';

        return [
            'app_name' => $name,
            'short_name' => mb_substr($name, 0, 12),
            'theme_color' => '#0ea5e9',
            'background_color' => '#0b1220',
        ];
    }

    public function manifest(Request $request)
    {
        $ctx = $this->context();
        $icon192 = file_exists(public_path('pwa/sale-icon-192.png'))
            ? asset('pwa/sale-icon-192.png')
            : asset('img/logo-small.png');
        $icon512 = file_exists(public_path('pwa/sale-icon-512.png'))
            ? asset('pwa/sale-icon-512.png')
            : $icon192;

        return response()->json([
            'id' => url('/delivery').'#delivery-pwa',
            'name' => $ctx['app_name'],
            'short_name' => $ctx['short_name'],
            'description' => 'Delivery driver app',
            'start_url' => url('/delivery/login').'?source=pwa',
            'scope' => url('/delivery').'/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => $ctx['background_color'],
            'theme_color' => $ctx['theme_color'],
            'icons' => [
                ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }

    public function serviceWorker()
    {
        $js = <<<'JS'
const CACHE='japspos-delivery-pwa-v1';
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(['/delivery/pwa/offline'])).then(()=>self.skipWaiting()));});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim()));});
self.addEventListener('fetch',e=>{
  if(e.request.method!=='GET') return;
  e.respondWith(fetch(e.request).catch(()=>caches.match('/delivery/pwa/offline')));
});
JS;

        return response($js, 200, ['Content-Type' => 'application/javascript']);
    }

    public function offline()
    {
        return view('delivery_app.offline');
    }
}
