<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthHelperController extends Controller
{
    public function getAuthenticatedUser()
    {
        $user = Auth::user();
        return $user->id;
    }
}