<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Http\Request;
class TrackingController extends Controller {
 public function index(Request $r,Shipment $shipment){abort_if($r->user()->role==='customer'&&$shipment->customer_id!==$r->user()->id,403);return response()->json($shipment->trackingEvents()->with(['location','creator'])->paginate(30));}
 public function store(Request $r,Shipment $shipment,TrackingService $s){abort_unless(in_array($r->user()->role,['admin','operator'],true),403);$d=$r->validate(['event_type'=>'required|string|max:80','status'=>'nullable|in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED','event_at'=>'required|date','location_id'=>'nullable|exists:locations,id','latitude'=>'nullable|numeric|between:-90,90','longitude'=>'nullable|numeric|between:-180,180','description'=>'nullable|string','estimated_arrival_at'=>'nullable|date']);return response()->json(['data'=>$s->add($shipment,$d,$r->user()->id)],201);}
}
