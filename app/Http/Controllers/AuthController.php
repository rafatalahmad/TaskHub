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
        'name' => 'required|string|max:40',
        'email' => 'required|string|email|max:50|unique:users,email',
        'password' => 'required|string|confirmed|min:8',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
    ]);

    
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('avatars', 'public');
    }

    
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'image' => $imagePath, 
    ]);

    return response()->json([
        'message' => 'Registration completed successfully',
        'user' => $user
    ], 201);
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
    public function updateProfile(Request $request)
{
    $user =$request->user();

    $request->validate([
        'name' => 'sometimes|string|max:40',
        'email' => 'sometimes|string|email|max:50|unique:users,email,' . $user->id,
        'password' => 'sometimes|string|confirmed|min:8',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $data = [];

    if ($request->has('name')) {
        $data['name'] = $request->name;
    }

    if ($request->has('email')) {
        $data['email'] = $request->email;
    }

    if ($request->has('password')) {
        $data['password'] = Hash::make($request->password);
    }
    
    if ($request->hasFile('image')) {

        if ($user->image && file_exists(storage_path('app/public/' . $user->image))) {
            unlink(storage_path('app/public/' . $user->image));
        }

        $data['image'] = $request->file('image')->store('avatars', 'public');
    }
    $user->update($data);

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user
    ]);
}



}
