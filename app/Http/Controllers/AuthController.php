<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function Register(Request $request)
    {
        $request->validate([
             'name'=>'required|string|max:40',
             'email'=>'required|string|email|max:50|unique:users,email',
             'password'=>'required|string|confirmed|min:8'
             
        ]);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)

        ]);
        return response()->json('Registration completed successfully',201);

    }

    public function login(Request $request)
    {
         $request->validate([
             'email'=>'required|string|email',
             'password'=>'required|string'
             
        ]);
        if(!Auth::attempt($request->only('email','password')))
        return response()->json(['message'=>'invalid email or password'],401);

        $user=User::where('email',$request->email)->firstOrfail();
        $Token=$user->createToken('auth_Token')->plainTextToken;
        return response()->json(['message'=>'Hello dear',
            'user'=>$user,
             'Token'=>$Token],201
            );

    }

     public function logout(Request $request)
    {
       $request->user()->currentAccessToken()->delete();
       return response()->json(['Logout successfully'],201);

    }
}
