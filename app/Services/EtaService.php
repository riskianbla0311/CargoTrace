<?php
namespace App\Services;
use App\Models\Shipment;
class EtaService {
 public function assess(Shipment $shipment):array {
  $latest=$shipment->trackingEvents()->latest('event_at')->first();$eta=$latest?->estimated_arrival_at;$expected=$shipment->expected_arrival_at;
  if(!$eta||!$expected)return ['estimated_arrival_at'=>$eta,'expected_arrival_at'=>$expected,'delay_hours'=>null,'delay_level'=>'UNKNOWN'];
  $hours=max(0,$expected->diffInMinutes($eta,false)/60);$low=config('cargotrace.delay_low_hours',6);$medium=config('cargotrace.delay_medium_hours',24);
  return ['estimated_arrival_at'=>$eta,'expected_arrival_at'=>$expected,'delay_hours'=>round($hours,2),'delay_level'=>$hours==0?'ON_TIME':($hours<=$low?'LOW':($hours<=$medium?'MEDIUM':'HIGH'))];
 }
}
