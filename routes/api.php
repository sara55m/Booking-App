<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Models\User;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

/*Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json([
        'message' => 'Email verified successfully'
    ]);
})->middleware(['signed'])->name('verification.verify');*/

//email verification route
/*Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {

    if (! URL::hasValidSignature($request)) {
        return response()->json(['message' => 'Invalid or expired link'], 403);
    }

    $user = User::findOrFail($id);

    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        return response()->json(['message' => 'Invalid hash'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Already verified']);
    }
    //fill email_verified_at column with current timestamp
    $user->markEmailAsVerified();

    return response()->json([
        'message' => 'Email verified successfully'
    ]);

})->middleware('signed');*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    //Route::post('/email/resend', [AuthController::class, 'resend']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
