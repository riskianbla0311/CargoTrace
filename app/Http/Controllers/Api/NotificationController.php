<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class NotificationController extends Controller {
 public function index(Request $r){return response()->json($r->user()->appNotifications()->latest()->paginate(20));}
 public function read(Request $r,int $id){$n=$r->user()->appNotifications()->findOrFail($id);$n->update(['read_at'=>now()]);return response()->json(['data'=>$n->refresh()]);}
}
