<?php

/*
|--------------------------------------------------------------------------
| SMS (login codes)
|--------------------------------------------------------------------------
|
| How the one-time login code reaches the user's phone.
|
|   log   Development only: the message is written to storage/logs/laravel.log
|         instead of being sent. Refused outside the "local" environment, so a
|         live server never writes login codes to a log file.
|
| A real gateway is added as another driver in App\Services\Sms\SmsSender;
| its address and keys belong in .env (never in this file).
|
*/

return [
    'driver' => env('SMS_DRIVER', 'log'),

    // The login code
    'otp' => [
        'digits' => 6,
        'expires_seconds' => 180,   // a code is valid for 3 minutes
        'resend_seconds' => 60,     // wait before asking for another code
        'max_attempts' => 5,        // wrong tries allowed per code
        'sends_per_window' => 5,    // codes per number ...
        'window_seconds' => 600,    // ... every 10 minutes
    ],
];
