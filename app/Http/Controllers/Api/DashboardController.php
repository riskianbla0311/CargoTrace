<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Shipment,ShipmentException};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller {
 public function summary(Request $r){$q=Shipment::query();if($r->user()->role==='customer')$q->where('customer_id',$r->user()->id);$total=(clone$q)->count();$statuses=(clone$q)->select('current_status',DB::raw('COUNT(*) total'))->groupBy('current_status')->pluck('total','current_status');$exceptions=ShipmentException::where('status','!=','RESOLVED')->whereHas('shipment',function($s)use($r){if($r->user()->role==='customer')$s->where('customer_id',$r->user()->id);})->count();return response()->json(['total_shipments'=>$total,'in_transit'=>(clone$q)->where('current_status','IN_TRANSIT')->count(),'delivered'=>(clone$q)->where('current_status','DELIVERED')->count(),'open_exceptions'=>$exceptions,'shipments_by_status'=>$statuses]);}
}
