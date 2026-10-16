<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    protected $table = 'driver_delivery_routes';

    public const STATUS_PLANNED = 'planned';

    public const STATUS_STARTED = 'started';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = ['id'];

    protected $casts = [
        'route_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'driver_latitude' => 'float',
        'driver_longitude' => 'float',
        'driver_location_at' => 'datetime',
    ];

    public function stops()
    {
        return $this->hasMany(DeliveryRouteOrder::class, 'delivery_route_id')->orderBy('stop_no');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'business_location_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_STARTED => 'In progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Planned',
        };
    }

    public function remainingCount(): int
    {
        $this->loadMissing('stops');

        return $this->stops->filter(fn (DeliveryRouteOrder $s) => ! $s->isFinished())->count();
    }

    public function currentStop(): ?DeliveryRouteOrder
    {
        $this->loadMissing('stops');

        return $this->stops->first(fn (DeliveryRouteOrder $s) => ! $s->isFinished());
    }
}
