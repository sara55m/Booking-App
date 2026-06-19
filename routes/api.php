<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;

Route::middleware('throttle:3,1')->group(function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});
//email verification route
Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {

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

    $user->update([
        'otp' => null,
        'otp_expires_at' => null,
    ]);

    return response()->json([
        'message' => 'Email verified successfully'
    ]);

})->middleware('signed')->name('verification.verify');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/email/resend', [AuthController::class, 'resend'])->middleware('throttle:3,1');;
    Route::post('/logout', [AuthController::class, 'logout']);

    //properties
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{property}', [PropertyController::class, 'show']);
    Route::post('/properties/{property}/availability', [PropertyController::class, 'availability']);
    Route::get('/properties/{property}/favorites', [PropertyController::class, 'addToFavorites']);
    Route::delete('/properties/{property}/favorites', [PropertyController::class, 'removeFromFavorites']);
    Route::get('/properties/{property}/reviews', [PropertyController::class, 'topReviews']);

    //bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    //payment
    Route::post('/bookings/{booking}/checkout', [PaymentController::class, 'checkout']);
});

//webhook route for stripe
Route::post(
    '/stripe/webhook',
    [PaymentController::class, 'webhook']
);
