<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ShipmentException extends Model {
 protected $fillable=['shipment_id','type','severity','status','description','detected_at','resolved_at','created_by'];
 protected function casts():array{return ['detected_at'=>'datetime','resolved_at'=>'datetime'];}
 public function shipment():BelongsTo{return $this->belongsTo(Shipment::class);}
 public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}
}
