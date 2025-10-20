<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
        return response()->json('ok',201);

    }
}
