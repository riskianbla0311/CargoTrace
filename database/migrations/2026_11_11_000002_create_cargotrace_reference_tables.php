<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration {public function up():void{
Schema::create('carriers',function(Blueprint $t){$t->id();$t->string('name',120);$t->string('code',30)->unique();$t->string('website')->nullable();$t->string('contact_email')->nullable();$t->string('phone',40)->nullable();$t->boolean('is_active')->default(true);$t->timestamps();});
Schema::create('locations',function(Blueprint $t){$t->id();$t->string('name',150);$t->string('city',100);$t->string('country',100);$t->text('address')->nullable();$t->decimal('latitude',10,7)->nullable();$t->decimal('longitude',10,7)->nullable();$t->string('location_type',50)->nullable();$t->timestamps();$t->index(['city','country']);});
}public function down():void{Schema::dropIfExists('locations');Schema::dropIfExists('carriers');}};
