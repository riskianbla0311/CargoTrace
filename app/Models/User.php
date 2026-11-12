<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable {
 use HasApiTokens,HasFactory,Notifiable;
 protected $fillable=['name','email','password','role'];
 protected $hidden=['password','remember_token'];
 protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
 public function shipments(): HasMany { return $this->hasMany(Shipment::class,'customer_id'); }
 public function trackingEvents(): HasMany { return $this->hasMany(TrackingEvent::class,'created_by'); }
 public function appNotifications(): HasMany { return $this->hasMany(Notification::class); }
}
