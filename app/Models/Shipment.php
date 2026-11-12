<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Shipment extends Model {
 protected $fillable=['shipment_number','customer_id','carrier_id','origin_id','destination_id','cargo_type','weight_kg','volume_m3','expected_departure_at','expected_arrival_at','current_status','description'];
 protected function casts():array{return ['weight_kg'=>'decimal:2','volume_m3'=>'decimal:3','expected_departure_at'=>'datetime','expected_arrival_at'=>'datetime'];}
 public function customer():BelongsTo{return $this->belongsTo(User::class,'customer_id');}
 public function carrier():BelongsTo{return $this->belongsTo(Carrier::class);}
 public function origin():BelongsTo{return $this->belongsTo(Location::class,'origin_id');}
 public function destination():BelongsTo{return $this->belongsTo(Location::class,'destination_id');}
 public function trackingEvents():HasMany{return $this->hasMany(TrackingEvent::class)->orderBy('event_at');}
 public function exceptions():HasMany{return $this->hasMany(ShipmentException::class);}
 public function items():HasMany{return $this->hasMany(ShipmentItem::class);}
}
