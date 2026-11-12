<?php
namespace App\Services;
use App\Enums\ShipmentStatus;
use App\Models\AuditLog;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ShipmentService {
 public function create(array $data,int $actorId):Shipment {
  return DB::transaction(function()use($data,$actorId){$s=Shipment::create($data);Notification::create(['user_id'=>$s->customer_id,'title'=>'New shipment registered','message'=>'Shipment '.$s->shipment_number.' has been registered.','type'=>'shipment','data'=>['shipment_id'=>$s->id]]);AuditLog::create(['user_id'=>$actorId,'action'=>'shipment.created','entity_type'=>Shipment::class,'entity_id'=>$s->id,'details'=>['shipment_number'=>$s->shipment_number],'created_at'=>now()]);return $s->load(['customer','carrier','origin','destination']);});
 }
 public function update(Shipment $s,array $data,int $actorId):Shipment {
  return DB::transaction(function()use($s,$data,$actorId){if(isset($data['current_status'])&&$data['current_status']!==$s->current_status){$from=ShipmentStatus::tryFrom($s->current_status);$to=ShipmentStatus::tryFrom($data['current_status']);if(!$from||!$to||!$from->canTransitionTo($to))throw ValidationException::withMessages(['current_status'=>'Transisi status tidak valid.']);}$s->update($data);Notification::create(['user_id'=>$s->customer_id,'title'=>'Shipment details updated','message'=>'Shipment '.$s->shipment_number.' information has been updated.','type'=>'shipment','data'=>['shipment_id'=>$s->id]]);AuditLog::create(['user_id'=>$actorId,'action'=>'shipment.updated','entity_type'=>Shipment::class,'entity_id'=>$s->id,'details'=>$data,'created_at'=>now()]);return $s->refresh()->load(['customer','carrier','origin','destination']);});
 }
}
