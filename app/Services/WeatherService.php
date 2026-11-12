<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
class WeatherService {
 public function current(float $lat,float $lon):array {
  $key=config('cargotrace.openweather_api_key');
  if(!$key)return ['available'=>false,'message'=>'OPENWEATHER_API_KEY belum dikonfigurasi.'];
  try {$response=Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/weather',['lat'=>$lat,'lon'=>$lon,'appid'=>$key,'units'=>'metric']);if(!$response->successful())return ['available'=>false,'message'=>'Layanan cuaca tidak tersedia.'];$d=$response->json();return ['available'=>true,'temperature'=>$d['main']['temp']??null,'condition'=>$d['weather'][0]['description']??null,'wind_speed'=>$d['wind']['speed']??null];}
  catch(\Throwable $e){return ['available'=>false,'message'=>'Gagal menghubungi layanan cuaca.'];}
 }
}
