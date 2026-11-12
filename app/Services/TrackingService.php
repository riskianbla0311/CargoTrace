<?php
namespace App\Services;
use App\Enums\ShipmentStatus;
use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class TrackingService {
 public function add(Shipment $shipment,array $data,int $actorId):TrackingEvent {
  return DB::transaction(function()use($shipment,$data,$actorId){$when=\Illuminate\Support\Carbon::parse($data['event_at']);$latest=$shipment->trackingEvents()->latest('event_at')->first();if($latest&&$when->lt($latest->event_at))throw ValidationException::withMessages(['event_at'=>'Waktu event tidak boleh lebih awal dari event terakhir.']);$status=$data['status']??null;if($status&&$status!==$shipment->current_status){$from=ShipmentStatus::tryFrom($shipment->current_status);$to=ShipmentStatus::tryFrom($status);if(!$from||!$to||!$from->canTransitionTo($to))throw ValidationException::withMessages(['status'=>'Transisi status tidak valid.']);}$event=$shipment->trackingEvents()->create($data+['created_by'=>$actorId]);if($status)$shipment->update(['current_status'=>$status]);
$shipment->loadMissing('customer');
Notification::create(['user_id'=>$shipment->customer_id,'title'=>'Shipment tracking updated','message'=>'A new tracking event was added for '.$shipment->shipment_number.'.','type'=>'tracking','data'=>['shipment_id'=>$shipment->id,'tracking_event_id'=>$event->id]]);
AuditLog::create(['user_id'=>$actorId,'action'=>'tracking.created','entity_type'=>TrackingEvent::class,'entity_id'=>$event->id,'details'=>['shipment_id'=>$shipment->id],'created_at'=>now()]);return $event->load(['location','creator']);});
 }
}
