<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data=$request->validate([
            'name'=>'required|string',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6',
            'phone'=>'nullable|string',
        ]);

        $user=User::create($data);

        //create token
        $token=$user->createToken('auth_token')->plainTextToken;

        //send email verification
        $user->sendEmailVerificationNotification();

        return response()->json([
            'user'=>$user,
            'access_token'=>$token,
        ]);

    }

    public function login(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required|string',
        ]);

        if(!Auth::attempt($request->only('email','password'))){
            return response()->json(['message'=>'Invalid credentials'],401);
        }

        $user=Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email first'
            ], 403);
        }

        //create token
        $token=$user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'=>$user,
            'access_token'=>$token,
        ]);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message'=>'Logged out']);
    }
}
