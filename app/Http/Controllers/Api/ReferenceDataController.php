<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Carrier,Location};
use Illuminate\Http\Request;
class ReferenceDataController extends Controller {
 public function carriers(){return response()->json(Carrier::where('is_active',true)->orderBy('name')->paginate(30));}
 public function storeCarrier(Request $r){abort_unless($r->user()->role==='admin',403);$d=$r->validate(['name'=>'required|string|max:120','code'=>'required|string|max:30|unique:carriers','website'=>'nullable|url','contact_email'=>'nullable|email','phone'=>'nullable|string|max:40']);return response()->json(['data'=>Carrier::create($d)],201);}
 public function locations(){return response()->json(Location::orderBy('name')->paginate(50));}
 public function storeLocation(Request $r){abort_unless($r->user()->role==='admin',403);$d=$r->validate(['name'=>'required|string|max:150','city'=>'required|string|max:100','country'=>'required|string|max:100','address'=>'nullable|string','latitude'=>'nullable|numeric|between:-90,90','longitude'=>'nullable|numeric|between:-180,180','location_type'=>'nullable|string|max:50']);return response()->json(['data'=>Location::create($d)],201);}
}
