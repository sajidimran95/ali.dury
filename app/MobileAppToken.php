<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Device token for the native Sales / Delivery / Customer apps.
 * Plain token format sent to the app: "{id}|{40 random chars}".
 */
class MobileAppToken extends Model
{
    public const APP_SALE = 'sale';

    public const APP_DELIVERY = 'delivery';

    public const APP_CUSTOMER = 'customer';

    protected $table = 'mobile_app_tokens';

    protected $guarded = ['id'];

    protected $hidden = ['token'];

    protected $casts = [
        'meta' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable()
    {
        return $this->morphTo();
    }

    /**
     * Create a token for a user/contact and return [model, plainTextToken].
     *
     * @return array{0: self, 1: string}
     */
    public static function issue(string $app, Model $owner, ?int $businessId, ?string $deviceName = null): array
    {
        $plain = Str::random(40);
        $ttlDays = (int) config('mobile_api.token_ttl_days', 365);
        $request = request();

        $token = self::create([
            'app' => $app,
            'tokenable_type' => get_class($owner),
            'tokenable_id' => $owner->getKey(),
            'business_id' => $businessId,
            'name' => $deviceName ? mb_substr($deviceName, 0, 191) : null,
            'token' => hash('sha256', $plain),
            'meta' => [],
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
            'last_used_at' => now(),
            'expires_at' => $ttlDays > 0 ? now()->addDays($ttlDays) : null,
        ]);

        return [$token, $token->id.'|'.$plain];
    }

    /**
     * Resolve a bearer token string for the given app (null when invalid / expired).
     */
    public static function findValid(?string $bearer, string $app): ?self
    {
        $bearer = trim((string) $bearer);
        if ($bearer === '' || ! str_contains($bearer, '|')) {
            return null;
        }

        [$id, $plain] = explode('|', $bearer, 2);
        if (! ctype_digit($id) || $plain === '') {
            return null;
        }

        $token = self::where('app', $app)->find((int) $id);
        if (! $token || ! hash_equals($token->token, hash('sha256', $plain))) {
            return null;
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            return null;
        }

        return $token;
    }

    public function getMetaValue(string $key, $default = null)
    {
        return data_get($this->meta ?? [], $key, $default);
    }
}
