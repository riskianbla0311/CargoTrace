<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Location extends Model { protected $fillable=['name','city','country','address','latitude','longitude','location_type']; protected function casts():array{return ['latitude'=>'decimal:7','longitude'=>'decimal:7'];} }
