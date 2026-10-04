<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MailService;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data=$request->validate([
            'name'=>'required|string',
            'email'=>['required','email',Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password'=>'required|string|min:6',
            'phone'=>'nullable|string',
            'locale'=>'nullable|string|in:en,ar'
        ]);

        //hash password
        $data['password']=Hash::make($data['password']);

        $user=User::create($data);

        //generate otp
        $otp=rand(100000, 999999);

        $user->update([
            'otp'=>$otp,
            'otp_expires_at'=>now()->addMinutes(10),
        ]);

        // send otp via email
        MailService::sendOtpEmail($user, $otp);

        //send email verification
        //$user->sendEmailVerificationNotification();

        return response()->json([
            'user'=>$user,
            'message'=>__("messages.otp_verification_email_sent")
        ]);

    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => __("messages.user_not_found")], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json(['message' => __("messages.invalid_otp")], 400);
        }

        if (now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => __("messages.otp_expired")], 400);
        }

        // mark verified
        $user->update([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        // create token
        $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' =>__("messages.verified_successfully"),
            'data'=>[
                'user'=>[
                    'id'=>$user->id,
                    'name'=>$user->name,
                    'email'=>$user->email,
                ],
            ]
        ]);
    }

    public function login(Request $request)
    {
        $credentials=$request->validate([
            'email'=>'required|email',
            'password'=>'required|string',
        ]);

        if(!Auth::attempt($credentials)){
            return response()->json(['message'=>__("messages.invalid_credentials")],401);
        }

        $user=Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __("messages.verify_email_first")
            ], 403);
        }

        //delete old tokens
        $user->tokens()->delete();

        //create token
        $token=$user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'=>'success',
            'message'=>__("messages.logged_in_successfully"),
            'data'=>[
                'user'=>[
                    'id'=>$user->id,
                    'name'=>$user->name,
                    'email'=>$user->email,
                ],
                'access_token'=>$token,
                'token_type'=>'Bearer',
            ]
        ]);
    }

    //resend verification mail
    /*public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __("messages.already_verified")]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => __("messages.verification_email_sent)]);
    }*/

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __("messages.already_verified")]);
        }

        // prevent spamming otp requests
        if ($user->otp_expires_at && now()->lt($user->otp_expires_at->subMinutes(9))) {
            return response()->json([
                'message' => __("messages.wait_before_requesting_otp")
            ], 429);
        }

        //generate new otp
        $otp = rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        //send otp via mail
        MailService::sendOtpEmail($user, $otp);

        return response()->json(['message' => __("messages.otp_resent")]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        //generate otp
        $otp = rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        //send otp via mail
        MailService::sendOtpEmail($user, $otp);

        return response()->json(['message' => __("messages.otp_sent_to_email")]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->otp !== $request->otp) {
            return response()->json(['message' => __("messages.invalid_otp")], 400);
        }

        if (now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => __("messages.otp_expired")], 400);
        }

        //update password
        $user->update([
            'password' => Hash::make($request->new_password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        //delete old tokens
        $user->tokens()->delete();

        return response()->json(['message' => __("messages.password_reset_successfully")]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message'=>__("messages.logged_out")]);
    }
}
