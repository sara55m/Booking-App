<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

//success and cancel routes for stripe
Route::get('/payment-success', function () {
    return 'Payment completed successfully';
});

Route::get('/payment-cancelled', function () {
    return 'Payment cancelled';
});

Route::get('/test-pusher', function () {
    return view('test');
});


Route::get('/debug-log', function () {
    Log::error('Wasmer file-log write test');
    return response()->json([
        'path' => storage_path('logs/laravel.log'),
        'exists' => file_exists(storage_path('logs/laravel.log')),
        'writable' => is_writable(storage_path('logs')),
    ]);
});
