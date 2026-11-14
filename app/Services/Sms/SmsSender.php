<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Sends a text message to a mobile number through the driver named in config/sms.php.
 */
class SmsSender
{
    /**
     * @throws RuntimeException when no usable driver is configured
     */
    public function send(string $mobile, string $message): void
    {
        $driver = (string) config('sms.driver', 'log');

        match ($driver) {
            'log' => $this->sendToLog($mobile, $message),
            default => throw new RuntimeException("SMS driver [{$driver}] is not set up."),
        };
    }

    /** True when messages can actually be delivered (or, locally, logged). */
    public function isAvailable(): bool
    {
        return config('sms.driver', 'log') !== 'log' || app()->environment('local', 'testing');
    }

    /**
     * Development only: the message goes to the application log. A live server must never
     * write login codes to a log file, so this is refused everywhere else.
     */
    private function sendToLog(string $mobile, string $message): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('The "log" SMS driver works only in the local environment.');
        }

        Log::info("SMS to {$mobile}: {$message}");
    }
}
