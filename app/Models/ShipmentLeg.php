<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentLeg extends Model
{
    protected $fillable = [
        'shipment_id', 'leg_number', 'transport_mode', 'carrier_id',
        'origin_id', 'destination_id', 'expected_departure_at',
        'expected_arrival_at', 'actual_departure_at', 'actual_arrival_at',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_departure_at' => 'datetime',
            'expected_arrival_at' => 'datetime',
            'actual_departure_at' => 'datetime',
            'actual_arrival_at' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
    public function carrier(): BelongsTo { return $this->belongsTo(Carrier::class); }
    public function origin(): BelongsTo { return $this->belongsTo(Location::class, 'origin_id'); }
    public function destination(): BelongsTo { return $this->belongsTo(Location::class, 'destination_id'); }
}
