<?php

namespace App\Services;

use App\Models\EmailManagement;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendWelcomeEmail($email, $name, $username, $password)
    {
        // Check if WelcomeEmail module is active
        $emailManagement = EmailManagement::where('module_name', 'Welcome Email')->first();

        if ($emailManagement && $emailManagement->is_active) {
            $message = "Hi, $name. You are successfully registered. Welcome to our Service.\n\nYour login credentials are:\n\nUsername: $username\nPassword: $password";

            Mail::to($email)->send(new WelcomeEmail($message));
        }
    }


    
}
