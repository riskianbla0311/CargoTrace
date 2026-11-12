<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Shipment,ShipmentException};
use Illuminate\Http\Request;
class ExceptionController extends Controller {
 public function index(Request $r,Shipment $shipment){abort_if($r->user()->role==='customer'&&$shipment->customer_id!==$r->user()->id,403);return response()->json($shipment->exceptions()->latest()->paginate(20));}
 public function store(Request $r,Shipment $shipment,ExceptionService $service){abort_unless(in_array($r->user()->role,['admin','operator'],true),403);$d=$r->validate(['type'=>'required|in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE','severity'=>'required|in:LOW,MEDIUM,HIGH,CRITICAL','description'=>'required|string']);return response()->json(['data'=>$service->create($shipment,$d,$r->user()->id)],201);}
 public function update(Request $r,ShipmentException $exception,ExceptionService $service){abort_unless(in_array($r->user()->role,['admin','operator'],true),403);$d=$r->validate(['status'=>'required|in:OPEN,INVESTIGATING,RESOLVED']);return response()->json(['data'=>$service->update($exception,$d,$r->user()->id)]);}
}
