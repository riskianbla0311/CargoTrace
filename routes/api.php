<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController,ShipmentController,TrackingController,ExceptionController,ReferenceDataController,DashboardController,WeatherController,NotificationController,GeocodingController};
Route::post('/register',[AuthController::class,'register']);Route::post('/login',[AuthController::class,'login']);
Route::middleware('auth:sanctum')->group(function(){
 Route::post('/logout',[AuthController::class,'logout']);Route::get('/user',[AuthController::class,'user']);
 Route::get('/shipments',[ShipmentController::class,'index']);Route::post('/shipments',[ShipmentController::class,'store']);Route::get('/shipments/{shipment}',[ShipmentController::class,'show']);Route::match(['put','patch'],'/shipments/{shipment}',[ShipmentController::class,'update']);Route::delete('/shipments/{shipment}',[ShipmentController::class,'destroy']);
 Route::get('/shipments/{shipment}/tracking',[TrackingController::class,'index']);Route::post('/shipments/{shipment}/tracking',[TrackingController::class,'store']);Route::get('/shipments/{shipment}/exceptions',[ExceptionController::class,'index']);Route::post('/shipments/{shipment}/exceptions',[ExceptionController::class,'store']);Route::patch('/exceptions/{exception}',[ExceptionController::class,'update']);
 Route::get('/carriers',[ReferenceDataController::class,'carriers']);Route::post('/carriers',[ReferenceDataController::class,'storeCarrier']);Route::get('/locations',[ReferenceDataController::class,'locations']);Route::post('/locations',[ReferenceDataController::class,'storeLocation']);Route::get('/dashboard/summary',[DashboardController::class,'summary']);Route::get('/weather',[WeatherController::class,'show']);Route::get('/geocode',[GeocodingController::class,'search']);Route::get('/notifications',[NotificationController::class,'index']);Route::patch('/notifications/{id}/read',[NotificationController::class,'read']);
});
