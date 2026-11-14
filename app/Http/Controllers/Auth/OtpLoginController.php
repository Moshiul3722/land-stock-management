<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\Sms\SmsSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * Sign-in with a mobile number and a one-time code (OTP).
 *
 * Only a user whose mobile number has been registered by a Super Admin / Admin, and who is
 * active, gets a code. The code is 6 digits, valid for a few minutes and for a limited number
 * of tries; only its hash is stored.
 */
class OtpLoginController extends Controller
{
    /** Session key that remembers which number is being verified. */
    public const SESSION_KEY = 'otp_login';

    public function __construct(private SmsSender $sms)
    {
        $this->middleware('guest');
    }

    /** Step 1: the mobile number. Sends a code and moves on to step 2. */
    public function send(Request $request): RedirectResponse
    {
        $mobile = self::normaliseMobile((string) $request->input('mobile'));

        if ($mobile === null) {
            return $this->backWithError('mobile', __('login.mobile_invalid'), $request);
        }

        return $this->issueCode($request, $mobile);
    }

    /** Step 2: another code for the same number. */
    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('login');
        }

        return $this->issueCode($request, $pending['mobile']);
    }

    /** Step 2: checks the code and signs the user in. */
    public function verify(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('login');
        }

        $code = self::toLatinDigits(preg_replace('/\s+/u', '', (string) $request->input('code')));
        $digits = (int) config('sms.otp.digits', 6);

        if (! preg_match('/^\d{' . $digits . '}$/', $code)) {
            return $this->backWithError('code', __('login.code_invalid'));
        }

        $otp = DB::table('login_otps')->where('id', $pending['otp_id'])->whereNull('used_at')->first();

        if (! $otp || now()->greaterThan($otp->expires_at)) {
            return $this->backWithError('code', __('login.code_expired'));
        }

        if ((int) $otp->attempts >= (int) config('sms.otp.max_attempts', 5)) {
            return $this->backWithError('code', __('login.code_too_many'));
        }

        if (! Hash::check($code, $otp->code_hash)) {
            DB::table('login_otps')->where('id', $otp->id)->increment('attempts');
            $left = (int) config('sms.otp.max_attempts', 5) - ((int) $otp->attempts + 1);

            return $this->backWithError('code', $left > 0
                ? __('login.code_wrong', ['left' => self::digits($left)])
                : __('login.code_too_many'));
        }

        // The code is right: the user must still be registered and active at this moment.
        $user = $this->approvedUser($otp->mobile);

        if (! $user || (int) $user->id !== (int) $otp->user_id) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('login')->withErrors(['mobile' => __('login.not_approved')]);
        }

        DB::table('login_otps')->where('id', $otp->id)->update(['used_at' => now(), 'updated_at' => now()]);
        $request->session()->forget(self::SESSION_KEY);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /** Step 2: back to step 1 to type another number. */
    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('login');
    }

    // ------------------------------------------------------------------

    private function issueCode(Request $request, string $mobile): RedirectResponse
    {
        $user = $this->approvedUser($mobile);

        if (! $user) {
            $request->session()->forget(self::SESSION_KEY);

            return $this->backWithError('mobile', __('login.not_approved'), $request);
        }

        if (! $this->sms->isAvailable()) {
            return $this->backWithError('mobile', __('login.sms_unavailable'), $request);
        }

        // One code per minute, and a handful per ten minutes, for each number.
        $latest = DB::table('login_otps')->where('mobile', $mobile)->orderByDesc('id')->first();
        $wait = (int) config('sms.otp.resend_seconds', 60);
        if ($latest && now()->diffInSeconds($latest->created_at, true) < $wait) {
            $seconds = $wait - (int) now()->diffInSeconds($latest->created_at, true);
            // The code that was just sent is still the one to type in.
            $request->session()->put(self::SESSION_KEY, ['mobile' => $mobile, 'otp_id' => $latest->id]);

            return $this->backWithError('code', __('login.resend_wait', ['seconds' => self::digits($seconds)]));
        }

        $key = 'otp-send:' . $mobile;
        if (RateLimiter::tooManyAttempts($key, (int) config('sms.otp.sends_per_window', 5))) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return $this->backWithError('mobile', __('login.too_many_sends', ['minutes' => self::digits($minutes)]), $request);
        }
        RateLimiter::hit($key, (int) config('sms.otp.window_seconds', 600));

        $digits = (int) config('sms.otp.digits', 6);
        $code = str_pad((string) random_int(0, (10 ** $digits) - 1), $digits, '0', STR_PAD_LEFT);
        $expires = (int) config('sms.otp.expires_seconds', 180);

        // Earlier codes for this number stop working.
        DB::table('login_otps')->where('mobile', $mobile)->whereNull('used_at')->update(['used_at' => now(), 'updated_at' => now()]);

        $otpId = DB::table('login_otps')->insertGetId([
            'user_id' => $user->id,
            'mobile' => $mobile,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds($expires),
            'ip' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->sms->send($mobile, __('login.sms_text', ['code' => $code, 'minutes' => (int) ceil($expires / 60)]));
        } catch (Throwable $e) {
            Log::error('Login code could not be sent: ' . $e->getMessage());
            DB::table('login_otps')->where('id', $otpId)->update(['used_at' => now()]);

            return $this->backWithError('mobile', __('login.sms_unavailable'), $request);
        }

        $request->session()->put(self::SESSION_KEY, ['mobile' => $mobile, 'otp_id' => $otpId]);

        return redirect()->route('login')->with('otp_sent', true);
    }

    /** An active, not removed user with this registered number; null when there is none (or more than one). */
    private function approvedUser(string $mobile): ?User
    {
        $users = User::query()
            ->where('mobile', $mobile)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->limit(2)
            ->get();

        return $users->count() === 1 ? $users->first() : null;
    }

    private function backWithError(string $field, string $message, ?Request $request = null): RedirectResponse
    {
        $response = redirect()->route('login')->withErrors([$field => $message]);

        return $request ? $response->withInput($request->only('mobile')) : $response;
    }

    /**
     * A Bangladeshi mobile number as 01XXXXXXXXX, or null when it is not one.
     * Accepts Bangla digits, spaces, dashes and the +88 / 88 / 0088 prefix.
     */
    public static function normaliseMobile(string $value): ?string
    {
        $number = preg_replace('/[^0-9]/', '', self::toLatinDigits($value));
        $number = preg_replace('/^(0088|88)(?=01)/', '', (string) $number);

        return preg_match('/^01[3-9]\d{8}$/', (string) $number) ? $number : null;
    }

    public static function toLatinDigits(string $value): string
    {
        return strtr($value, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
    }

    /** A number in the page's digits (Bangla on Bangla pages). */
    private static function digits(int|string $value): string
    {
        return app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;
    }
}
