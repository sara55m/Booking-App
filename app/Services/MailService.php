<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function sendOtpEmail($user, $otp): void
    {
        Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your OTP Code');
        });
    }
}
