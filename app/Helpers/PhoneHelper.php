<?php

namespace App\Helpers;

class PhoneHelper
{
    public static function formatPhone($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Check for the '08' prefix and convert it to '+628'
        if (strpos($phone, '08') === 0) {
            return '+628' . substr($phone, 2);
        }

        // Check for the '62' prefix and convert it to '+62'
        if (strpos($phone, '62') === 0) {
            return '+' . $phone;
        }

        // If it doesn't match any of the above, just return it as-is
        return $phone;
    }
}
