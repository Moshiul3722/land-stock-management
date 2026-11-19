<?php

namespace App\Livewire\Vehicles;

/** Mobile numbers as stored: 01XXXXXXXXX (Bangla digits, spaces, +88 / 88 removed). */
final class MobileNumber
{
    public static function normalise(string $value): string
    {
        $value = strtr(trim($value), ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        if ($value === '') {
            return '';
        }
        $digits = preg_replace('/\D+/', '', $value);
        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '88') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }
}
