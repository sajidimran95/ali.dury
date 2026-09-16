<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryRouteOrder extends Model
{
    protected $table = 'delivery_route_orders';

    public const STATUS_PENDING = 'pending';

    public const STATUS_EN_ROUTE = 'en_route';

    public const STATUS_ARRIVED = 'arrived';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public const FAIL_REASONS = [
        'customer_unavailable' => 'Customer unavailable',
        'wrong_address' => 'Wrong address',
        'customer_refused' => 'Customer refused',
        'unable_to_access' => 'Unable to access location',
        'other' => 'Other',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'arrived_at' => 'datetime',
        'delivered_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'delivery_route_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /** Alias used by delivery PWA views (invoice = sell transaction). */
    public function order()
    {
        return $this->transaction();
    }

    public function formattedAddress(): string
    {
        $line2 = collect([$this->ship_to_city, $this->ship_to_state, $this->ship_to_zip])->filter()->implode(', ');

        return collect([$this->ship_to_address, $line2])->filter()->implode("\n");
    }

    /** Auto Google Maps navigate link for this stop */
    public function navigateUrl(): string
    {
        if ($this->latitude && $this->longitude) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($this->latitude.','.$this->longitude);
        }

        $addr = trim(preg_replace('/\s+/', ' ', str_replace("\n", ', ', $this->formattedAddress())));

        return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($addr !== '' ? $addr : 'United States');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_FAILED, self::STATUS_SKIPPED], true);
    }

    public function canAct(): bool
    {
        return ! $this->isFinished();
    }

    /** Failed stops can still be marked delivered later. */
    public function canMarkDelivered(): bool
    {
        return ! in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_SKIPPED], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_EN_ROUTE => 'En Route',
            self::STATUS_ARRIVED => 'Arrived',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_SKIPPED => 'Skipped',
            default => 'Pending',
        };
    }
}
