<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // An expired session (the "419 Page Expired" screen): everyone goes to the login page instead.
        // (Laravel hands the expired-token error to this callback as an HTTP 419 error.)
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            // Background requests (AJAX, Livewire) get the status; the page's script then opens the login page.
            if ($request->expectsJson() || $request->ajax() || $request->hasHeader('X-Livewire')) {
                return response()->json(['message' => 'Session expired.', 'redirect' => route('login')], 419);
            }

            // Signing out with an expired page still signs the user out.
            if ($request->routeIs('logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            return redirect()->route('login');
        });
    }
}
