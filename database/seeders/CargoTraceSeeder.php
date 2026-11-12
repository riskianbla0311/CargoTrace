<?php
namespace Database\Seeders;
use App\Models\{User,Carrier,Location,Shipment,TrackingEvent};
use Illuminate\Database\Seeder;
class CargoTraceSeeder extends Seeder {
 public function run():void {
  $admin=User::updateOrCreate(['email'=>'admin@cargotrace.test'],['name'=>'CargoTrace Admin','password'=>'password','role'=>'admin']);
  $operator=User::updateOrCreate(['email'=>'operator@cargotrace.test'],['name'=>'CargoTrace Operator','password'=>'password','role'=>'operator']);
  $customer=User::updateOrCreate(['email'=>'customer@cargotrace.test'],['name'=>'Demo Customer','password'=>'password','role'=>'customer']);
  $carrier=Carrier::firstOrCreate(['code'=>'CT-EXP'],['name'=>'CargoTrace Express','is_active'=>true]);
  $a=Location::firstOrCreate(['name'=>'Jakarta Port','city'=>'Jakarta','country'=>'Indonesia'],['location_type'=>'PORT','latitude'=>-6.104,'longitude'=>106.88]);
  $b=Location::firstOrCreate(['name'=>'Surabaya Port','city'=>'Surabaya','country'=>'Indonesia'],['location_type'=>'PORT','latitude'=>-7.20,'longitude'=>112.73]);
  $s=Shipment::firstOrCreate(['shipment_number'=>'CT-DEMO-0001'],['customer_id'=>$customer->id,'carrier_id'=>$carrier->id,'origin_id'=>$a->id,'destination_id'=>$b->id,'cargo_type'=>'General Cargo','weight_kg'=>250,'volume_m3'=>2.5,'expected_departure_at'=>now()->subDay(),'expected_arrival_at'=>now()->addDays(2),'current_status'=>'IN_TRANSIT','description'=>'Demo shipment']);
  TrackingEvent::firstOrCreate(['shipment_id'=>$s->id,'event_type'=>'DEPARTED_ORIGIN'],['status'=>'IN_TRANSIT','event_at'=>now()->subHours(5),'location_id'=>$a->id,'latitude'=>$a->latitude,'longitude'=>$a->longitude,'description'=>'Shipment departed origin facility','estimated_arrival_at'=>now()->addDays(2)->addHours(3),'created_by'=>$operator->id]);
 }
}
