<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocalizationController extends Controller
{
    public function changeLanguage($locale)
    {
        if (in_array($locale, config('app.locales'))) {
            session()->put('locale', $locale);
        }
        
        return redirect()->back(); // Redirect back to the previous page
    }
}
