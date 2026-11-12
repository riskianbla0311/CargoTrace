<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\{ShipmentService,EtaService};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ShipmentController extends Controller {
 private function query(Request $r){$q=Shipment::with(['customer','carrier','origin','destination']);if($r->user()->role==='customer')$q->where('customer_id',$r->user()->id);return $q;}
 public function index(Request $r){$q=$this->query($r);foreach(['current_status','carrier_id','origin_id','destination_id']as$f)if($r->filled($f))$q->where($f,$r->input($f));if($r->filled('q'))$q->where('shipment_number','like','%'.$r->q.'%');if($r->filled('from'))$q->whereDate('created_at','>=',$r->from);if($r->filled('to'))$q->whereDate('created_at','<=',$r->to);return response()->json($q->latest()->paginate(min(max((int)$r->input('per_page',15),1),100)));}
 public function store(Request $r,ShipmentService $s){abort_unless(in_array($r->user()->role,['admin','operator'],true),403);$d=$r->validate(['shipment_number'=>'required|string|max:60|unique:shipments','customer_id'=>'required|exists:users,id','carrier_id'=>'required|exists:carriers,id','origin_id'=>'required|exists:locations,id','destination_id'=>'required|exists:locations,id','cargo_type'=>'required|string|max:120','weight_kg'=>'nullable|numeric|min:0','volume_m3'=>'nullable|numeric|min:0','expected_departure_at'=>'required|date','expected_arrival_at'=>'required|date|after:expected_departure_at','description'=>'nullable|string']);$d['current_status']='CREATED';return response()->json(['data'=>$s->create($d,$r->user()->id)],201);}
 public function show(Request $r,Shipment $shipment,EtaService $eta){abort_if($r->user()->role==='customer'&&$shipment->customer_id!==$r->user()->id,403);$shipment->load(['customer','carrier','origin','destination','trackingEvents.location','exceptions']);return response()->json(['data'=>$shipment,'eta'=>$eta->assess($shipment)]);}
 public function update(Request $r,Shipment $shipment,ShipmentService $s){abort_unless(in_array($r->user()->role,['admin','operator'],true),403);$d=$r->validate(['carrier_id'=>'sometimes|exists:carriers,id','origin_id'=>'sometimes|exists:locations,id','destination_id'=>'sometimes|exists:locations,id','cargo_type'=>'sometimes|string|max:120','weight_kg'=>'nullable|numeric|min:0','volume_m3'=>'nullable|numeric|min:0','expected_departure_at'=>'sometimes|date','expected_arrival_at'=>'sometimes|date','description'=>'nullable|string','current_status'=>['sometimes',Rule::in(['CREATED','BOOKED','PICKED_UP','IN_TRANSIT','DELIVERED'])]]);return response()->json(['data'=>$s->update($shipment,$d,$r->user()->id)]);}
 public function destroy(Request $r,Shipment $shipment){abort_unless($r->user()->role==='admin',403);$shipment->delete();return response()->json(['message'=>'Shipment dihapus.']);}
}
