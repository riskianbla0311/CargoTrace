<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
 public function register(Request $r){$d=$r->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users','password'=>'required|string|min:8']);$u=User::create($d+['role'=>'customer']);return response()->json(['user'=>$u,'token'=>$u->createToken('cargotrace-api')->plainTextToken],201);}
 public function login(Request $r){$d=$r->validate(['email'=>'required|email','password'=>'required|string']);$u=User::where('email',$d['email'])->first();if(!$u||!Hash::check($d['password'],$u->password))throw ValidationException::withMessages(['email'=>['Kredensial tidak sesuai.']]);return response()->json(['user'=>$u,'token'=>$u->createToken('cargotrace-api')->plainTextToken]);}
 public function logout(Request $r){$r->user()->currentAccessToken()?->delete();return response()->json(['message'=>'Logout berhasil.']);}
 public function user(Request $r){return response()->json(['user'=>$r->user()]);}
}
