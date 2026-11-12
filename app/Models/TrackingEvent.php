<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TrackingEvent extends Model {
 protected $fillable=['shipment_id','event_type','status','event_at','location_id','latitude','longitude','description','estimated_arrival_at','created_by'];
 protected function casts():array{return ['event_at'=>'datetime','estimated_arrival_at'=>'datetime','latitude'=>'decimal:7','longitude'=>'decimal:7'];}
 public function shipment():BelongsTo{return $this->belongsTo(Shipment::class);}
 public function location():BelongsTo{return $this->belongsTo(Location::class);}
 public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}
}
