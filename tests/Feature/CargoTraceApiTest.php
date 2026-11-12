<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class CargoTraceApiTest extends TestCase {
 use RefreshDatabase;
 public function test_customer_registration_assigns_customer_role():void{$this->postJson('/api/register',['name'=>'Test','email'=>'test@example.com','password'=>'password123'])->assertCreated()->assertJsonPath('user.role','customer');}
 public function test_api_shipments_require_authentication():void{$this->getJson('/api/shipments')->assertUnauthorized();}
 public function test_customer_cannot_create_shipment():void{$u=User::factory()->create(['role'=>'customer']);$this->actingAs($u,'sanctum')->postJson('/api/shipments',[])->assertForbidden();}
}
