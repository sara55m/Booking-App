<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
