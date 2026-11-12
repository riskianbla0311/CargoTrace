<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;
class WeatherController extends Controller {
 public function show(Request $r,WeatherService $weather){$d=$r->validate(['latitude'=>'required|numeric|between:-90,90','longitude'=>'required|numeric|between:-180,180']);return response()->json($weather->current((float)$d['latitude'],(float)$d['longitude']));}
}
