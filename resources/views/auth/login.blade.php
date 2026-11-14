{{--
    Sign-in page.

    Two steps with a mobile number: (1) the registered number, (2) the one-time code (OTP) sent
    to it - see App\Http\Controllers\Auth\OtpLoginController. Signing in with e-mail and
    password stays available as a second option (the form posts to the usual /login).
    The page uses only the project's own files (no CDN): the icon font and the Bangla font.
--}}
@php
    $isBn = app()->getLocale() === 'bn';
    $digits = fn ($value) => $isBn
        ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
        : (string) $value;
    $pending = session(\App\Http\Controllers\Auth\OtpLoginController::SESSION_KEY);
    $otp = $pending ? \Illuminate\Support\Facades\DB::table('login_otps')->where('id', $pending['otp_id'])->first() : null;
    $onOtpStep = $pending && $otp;
    $otpDigits = (int) config('sms.otp.digits', 6);
    // Seconds left for the code, and until another one may be asked for
    $expiresIn = $onOtpStep ? max(0, (int) now()->diffInSeconds($otp->expires_at, false)) : 0;
    $resendIn = $onOtpStep ? max(0, (int) config('sms.otp.resend_seconds', 60) - (int) \Illuminate\Support\Carbon::parse($otp->created_at)->diffInSeconds(now(), true)) : 0;
    // The number being verified, with the middle hidden: 017XXXXXX45
    $maskedMobile = $onOtpStep ? $digits(substr($pending['mobile'], 0, 3) . 'XXXXXX' . substr($pending['mobile'], -2)) : '';
    // E-mail sign-in opens by itself when that form came back with an error
    $onEmail = ! $onOtpStep && ($errors->has('email') || $errors->has('password'));
@endphp
<!DOCTYPE html>
<html lang="{{ $isBn ? 'bn' : 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('login.page_title') }} | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}">
    {{-- Site-wide responsive base font size (17px desktop, 16px tablet, 15px phone) --}}
    <link rel="stylesheet" href="{{ asset('css/base-font.css') }}?v={{ @filemtime(public_path('css/base-font.css')) }}">
    <style>
        @font-face { font-family: 'Hind-Siliguri'; src: url('{{ asset('fonts/Hind_Siliguri_Light-Regular.ttf') }}') format('truetype'); font-weight: 400; font-display: swap; }
        @font-face { font-family: 'Hind-Siliguri'; src: url('{{ asset('fonts/Hind_Siliguri_Medium-Regular.ttf') }}') format('truetype'); font-weight: 600; font-display: swap; }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; }
        body { margin: 0; font-family: 'Hind-Siliguri', system-ui, -apple-system, 'Segoe UI', sans-serif; font-weight: 400; line-height: 1.5; color: #0f172a; background: #0b1430; -webkit-font-smoothing: antialiased; }
        button, input { font: inherit; }
        [hidden] { display: none !important; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

        /* ---- Page: the picture side and the form side ---- */
        .auth { position: relative; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); min-height: 100vh; min-height: 100dvh; overflow: hidden; background: linear-gradient(135deg, #0b1430 0%, #13244f 38%, #1d4fd8 78%, #4f46e5 100%); }
        .auth::before, .auth::after { content: ""; position: absolute; border-radius: 50%; filter: blur(70px); pointer-events: none; }
        .auth::before { top: -12rem; left: -10rem; width: 34rem; height: 34rem; background: rgba(56, 189, 248, 0.28); }
        .auth::after { right: -12rem; bottom: -14rem; width: 38rem; height: 38rem; background: rgba(167, 139, 250, 0.32); }

        /* ---- Background decoration (see the <svg class="auth-lines"> at the top of the page) ---- */
        .auth-lines { position: absolute; inset: 0; z-index: 0; width: 100%; height: 100%; pointer-events: none; }
        .auth-lines .flow { animation: auth-flow 60s linear infinite; }
        .auth-lines .flow-2 { animation-duration: 80s; animation-direction: reverse; }
        .auth-lines .flow-3 { animation-duration: 100s; }
        .auth-lines .flow-4 { animation-duration: 45s; animation-direction: reverse; }
        .auth-lines .auth-rings circle { transform-box: fill-box; transform-origin: center; animation: auth-spin 180s linear infinite; }
        .auth-lines .auth-rings circle:nth-child(even) { animation-direction: reverse; animation-duration: 240s; }
        .auth-lines .auth-sparks circle { filter: drop-shadow(0 0 5px rgba(186, 230, 253, 0.95)); opacity: 0.75; animation: auth-twinkle 4.5s ease-in-out infinite; }
        .auth-lines .auth-sparks circle:nth-child(2n) { animation-delay: 1.4s; }
        .auth-lines .auth-sparks circle:nth-child(3n) { animation-delay: 2.6s; }
        @keyframes auth-flow { to { stroke-dashoffset: -480; } }
        @keyframes auth-spin { to { transform: rotate(360deg); } }
        @keyframes auth-twinkle { 0%, 100% { opacity: 0.35; } 50% { opacity: 0.95; } }

        /* ---- Left: who this is ---- */
        .auth-hero { position: relative; z-index: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 2rem; padding: 2.5rem clamp(1.5rem, 4vw, 4rem); color: #fff; }
        .auth-brand { display: flex; align-items: center; gap: 0.85rem; }
        .auth-brand img { width: 3.25rem; height: 3.25rem; flex: 0 0 auto; }
        .auth-brand strong { display: block; font-size: 1.15rem; font-weight: 600; line-height: 1.25; }
        .auth-brand span { display: block; color: rgba(226, 232, 240, 0.8); font-size: 0.85rem; }
        .auth-pitch { max-width: 34rem; }
        .auth-pitch h1 { margin: 0 0 1rem; font-size: clamp(1.6rem, 2.6vw, 2.35rem); font-weight: 600; line-height: 1.3; letter-spacing: -0.01em; }
        .auth-pitch p { margin: 0 0 1.75rem; color: rgba(226, 232, 240, 0.85); font-size: 1.05rem; }
        .auth-points { display: grid; gap: 0.75rem; margin: 0; padding: 0; list-style: none; }
        .auth-points li { display: flex; align-items: center; gap: 0.85rem; padding: 0.7rem 0.9rem; border: 1px solid rgba(255, 255, 255, 0.14); border-radius: 0.9rem; background: rgba(255, 255, 255, 0.07); backdrop-filter: blur(8px); font-weight: 600; }
        .auth-points i { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 0.7rem; background: rgba(255, 255, 255, 0.16); font-size: 0.95rem; }
        .auth-foot { color: rgba(226, 232, 240, 0.65); font-size: 0.8rem; }

        /* ---- Right: the card ---- */
        .auth-side { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; padding: 2rem clamp(1rem, 3vw, 3rem); }
        .auth-card { width: 100%; max-width: 27rem; padding: 2rem clamp(1.25rem, 3vw, 2.25rem) 1.75rem; border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 1.5rem; background: #f8fafc; /* solid: the background's glowing points must not show through */ box-shadow: 0 40px 80px -30px rgba(2, 6, 23, 0.65), 0 2px 6px rgba(2, 6, 23, 0.08); }
        .auth-card-logo { display: none; width: 3.5rem; height: 3.5rem; margin: 0 auto 0.75rem; }
        /* ---- The two steps: number, name and a line between them ----
           The numbers use the device's own Bangla font: the page font draws "১" as a small
           tick that is hard to read inside the circle. */
        .auth-steps { display: flex; align-items: center; gap: 0.6rem; margin: 0 0 1.4rem; padding: 0; list-style: none; }
        .auth-steps li { display: flex; align-items: center; gap: 0.5rem; min-width: 0; color: #94a3b8; font-size: 0.85rem; font-weight: 600; white-space: nowrap; }
        .auth-steps .auth-step-no { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border: 1.5px solid #cbd5e1; border-radius: 50%; background: #fff; color: #64748b; font-family: 'Kohinoor Bangla', 'Nirmala UI', 'Noto Sans Bengali', 'Vrinda', system-ui, sans-serif; font-size: 1.15rem; font-weight: 600; line-height: 1; }
        .auth-steps li.is-current { color: #0f172a; }
        .auth-steps li.is-current .auth-step-no { border-color: transparent; background: #1d4fd8; color: #fff; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.2); }
        .auth-steps li.is-done { color: #047857; }
        .auth-steps li.is-done .auth-step-no { border-color: transparent; background: #10b981; color: #fff; font-size: 0.95rem; }
        .auth-steps .auth-step-line { flex: 1 1 auto; min-width: 1rem; height: 2px; border-radius: 2px; background: #e2e8f0; }
        .auth-steps .auth-step-line.is-done { background: #10b981; }
        .auth-title { margin: 0; font-size: 1.6rem; font-weight: 600; line-height: 1.25; letter-spacing: -0.01em; }
        .auth-sub { margin: 0.35rem 0 1.4rem; color: #475569; font-size: 0.95rem; }

        .auth-label { display: block; margin-bottom: 0.4rem; color: #334155; font-size: 0.875rem; font-weight: 600; }
        .auth-field { display: flex; align-items: stretch; height: 3.25rem; overflow: hidden; border: 1.5px solid #cbd5e1; border-radius: 0.9rem; background: #fff; transition: border-color 0.15s, box-shadow 0.15s; }
        .auth-field:focus-within { border-color: #2f7cf6; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.18); }
        .auth-field.is-invalid { border-color: #dc2626; }
        .auth-field > .auth-prefix { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 0.45rem; padding: 0 0.85rem; border-right: 1.5px solid #e2e8f0; color: #475569; font-weight: 600; }
        /* Square inside the rounded field, so the browser's saved-value (autofill) colour fills
           the whole space up to the field's own corners */
        .auth-field > input { flex: 1 1 auto; min-width: 0; height: 100%; margin: 0; padding: 0 0.9rem; border: 0; border-radius: 0; background: transparent; color: #0f172a; font-size: 1.1rem; outline: 0; }
        .auth-field > input::placeholder { color: #94a3b8; }
        .auth-field > button { flex: 0 0 auto; width: 3rem; border: 0; border-radius: 0; background: transparent; color: #64748b; cursor: pointer; }
        .auth-field > button:hover { color: #0f172a; }
        .auth-error { display: flex; align-items: flex-start; gap: 0.4rem; margin: 0.45rem 0 0; color: #b91c1c; font-size: 0.85rem; font-weight: 600; }
        .auth-error i { margin-top: 0.2rem; }
        .auth-ok { display: flex; align-items: center; gap: 0.5rem; margin: 0 0 1rem; padding: 0.6rem 0.8rem; border: 1px solid #a7f3d0; border-radius: 0.75rem; background: #ecfdf5; color: #065f46; font-size: 0.875rem; font-weight: 600; }

        .auth-btn { display: flex; align-items: center; justify-content: center; gap: 0.55rem; width: 100%; min-height: 3.25rem; margin-top: 1.1rem; padding: 0 1rem; border: 0; border-radius: 0.9rem; background: linear-gradient(135deg, #1d65db, #4f46e5); color: #fff; font-size: 1.05rem; font-weight: 600; cursor: pointer; box-shadow: 0 14px 28px -12px rgba(37, 99, 235, 0.75); transition: transform 0.12s, box-shadow 0.15s, filter 0.15s; }
        .auth-btn:hover { filter: brightness(1.07); box-shadow: 0 18px 32px -12px rgba(37, 99, 235, 0.85); }
        .auth-btn:active { transform: translateY(1px); }
        .auth-btn:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.35); }
        .auth-btn:disabled { background: #cbd5e1; color: #64748b; box-shadow: none; cursor: not-allowed; filter: none; }
        .auth-link { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.25rem; border: 0; background: transparent; color: #1d4fd8; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; }
        .auth-link:hover { color: #1a3f9f; text-decoration: underline; }
        .auth-link:focus-visible { outline: 0; border-radius: 0.4rem; box-shadow: 0 0 0 3px rgba(47, 124, 246, 0.35); }
        .auth-link:disabled { color: #94a3b8; cursor: not-allowed; text-decoration: none; }
        .auth-note { display: flex; align-items: flex-start; gap: 0.5rem; margin: 1.1rem 0 0; padding: 0.65rem 0.8rem; border-radius: 0.75rem; background: #f1f5f9; color: #475569; font-size: 0.8125rem; line-height: 1.45; }
        .auth-note i { margin-top: 0.15rem; color: #1d65db; }
        .auth-or { display: flex; align-items: center; gap: 0.75rem; margin: 1.25rem 0 0.5rem; color: #94a3b8; font-size: 0.8rem; }
        .auth-or::before, .auth-or::after { content: ""; flex: 1 1 auto; height: 1px; background: #e2e8f0; }
        .auth-center { text-align: center; }
        .auth-gap { margin-top: 0.9rem; }

        /* ---- The code: one box per digit ---- */
        .otp-boxes { display: grid; grid-template-columns: repeat({{ $otpDigits }}, minmax(0, 1fr)); gap: 0.5rem; }
        .otp-boxes input { width: 100%; height: 3.5rem; padding: 0; border: 1.5px solid #cbd5e1; border-radius: 0.85rem; background: #fff; color: #0f172a; font-size: 1.5rem; font-weight: 600; text-align: center; outline: 0; transition: border-color 0.15s, box-shadow 0.15s, background-color 0.15s; }
        .otp-boxes input:focus { border-color: #2f7cf6; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.18); }
        .otp-boxes input.is-filled { border-color: #93b4f5; background: #f5f8ff; }
        .otp-boxes.is-invalid input { border-color: #dc2626; }
        .otp-meta { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.25rem 0.75rem; margin-top: 0.75rem; color: #475569; font-size: 0.875rem; }
        .otp-meta strong { color: #0f172a; font-variant-numeric: tabular-nums; }
        .otp-meta .is-over { color: #b91c1c; font-weight: 600; }
        .otp-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.25rem 1rem; margin-top: 0.9rem; }
        .otp-row form { margin: 0; }

        /* ---- Smaller screens: the card alone, on the same background ---- */
        @media (max-width: 899.98px) {
            .auth { grid-template-columns: minmax(0, 1fr); }
            .auth-hero { display: none; }
            .auth-side { padding: 1.25rem 1rem; }
            .auth-card-logo { display: block; }
            .auth-card { text-align: left; }
            .auth-card-head { text-align: center; }
            .auth-steps { justify-content: center; }
        }
        @media (max-width: 380px) {
            .otp-boxes { gap: 0.35rem; }
            .otp-boxes input { height: 3rem; font-size: 1.25rem; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
    </style>
</head>
<body>
<main class="auth">

    {{-- Decoration only, behind everything else: curvy dashed lines that fade at their ends and
         drift slowly, two dashed rings, a faint dot grid and a few softly glowing points --}}
    <svg class="auth-lines" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="line-a" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#fff" stop-opacity="0"/>
                <stop offset="0.25" stop-color="#bae6fd" stop-opacity="0.55"/>
                <stop offset="0.75" stop-color="#fff" stop-opacity="0.5"/>
                <stop offset="1" stop-color="#fff" stop-opacity="0"/>
            </linearGradient>
            <linearGradient id="line-b" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#c7d2fe" stop-opacity="0"/>
                <stop offset="0.5" stop-color="#c7d2fe" stop-opacity="0.5"/>
                <stop offset="1" stop-color="#a5f3fc" stop-opacity="0"/>
            </linearGradient>
            <linearGradient id="line-c" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#fff" stop-opacity="0"/>
                <stop offset="0.5" stop-color="#e0e7ff" stop-opacity="0.42"/>
                <stop offset="1" stop-color="#fff" stop-opacity="0"/>
            </linearGradient>
            <pattern id="dot-grid" width="34" height="34" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="1.3" fill="#fff" fill-opacity="0.3"/>
            </pattern>
            <radialGradient id="grid-fade" cx="0.28" cy="0.5" r="0.62">
                <stop offset="0" stop-color="#fff" stop-opacity="1"/>
                <stop offset="1" stop-color="#fff" stop-opacity="0"/>
            </radialGradient>
            <mask id="grid-mask"><rect width="1440" height="900" fill="url(#grid-fade)"/></mask>
        </defs>

        <rect width="1440" height="900" fill="url(#dot-grid)" mask="url(#grid-mask)"/>

        <g fill="none" stroke-linecap="round" class="auth-rings">
            <circle cx="1310" cy="110" r="190" stroke="rgba(255,255,255,0.34)" stroke-width="1.6" stroke-dasharray="3 13"/>
            <circle cx="1310" cy="110" r="262" stroke="rgba(186,230,253,0.28)" stroke-width="1.4" stroke-dasharray="14 16"/>
            <circle cx="110" cy="830" r="170" stroke="rgba(199,210,254,0.34)" stroke-width="1.6" stroke-dasharray="10 12"/>
            <circle cx="110" cy="830" r="238" stroke="rgba(255,255,255,0.26)" stroke-width="1.4" stroke-dasharray="2 12"/>
        </g>

        <g fill="none" stroke-linecap="round">
            <path class="flow flow-1" d="M-80 220 C 170 90, 380 340, 640 205 S 1060 60, 1520 250" stroke="url(#line-a)" stroke-width="1.8" stroke-dasharray="2 12"/>
            <path class="flow flow-2" d="M-80 440 C 160 580, 430 300, 720 455 S 1170 630, 1520 410" stroke="url(#line-a)" stroke-width="2.2" stroke-dasharray="12 12"/>
            <path class="flow flow-3" d="M-60 670 C 240 540, 470 790, 780 655 S 1190 520, 1520 710" stroke="url(#line-b)" stroke-width="1.8" stroke-dasharray="6 11"/>
            <path class="flow flow-4" d="M-60 560 C 220 470, 520 600, 760 540 S 1220 420, 1520 560" stroke="url(#line-b)" stroke-width="1.2" stroke-dasharray="1 9"/>
            <path class="flow flow-2" d="M230 -40 C 140 200, 400 380, 310 620 S 430 860, 370 960" stroke="url(#line-c)" stroke-width="1.5" stroke-dasharray="3 11"/>
            <path class="flow flow-3" d="M1010 -60 C 1130 170, 880 340, 1030 560 S 1180 820, 1080 960" stroke="url(#line-c)" stroke-width="1.5" stroke-dasharray="12 14"/>
        </g>

        <g class="auth-sparks" fill="#fff">
            <circle cx="640" cy="205" r="3.5"/><circle cx="720" cy="455" r="4"/><circle cx="310" cy="620" r="3"/>
            <circle cx="1030" cy="560" r="3"/><circle cx="780" cy="655" r="3.5"/><circle cx="1195" cy="160" r="2.5"/><circle cx="150" cy="330" r="2.5"/>
        </g>
    </svg>

    {{-- Left: the ministry and what the application is for (hidden on phones) --}}
    <section class="auth-hero" aria-hidden="false">
        <div class="auth-brand">
            <img src="{{ asset('Government_Seal_of_Bangladesh.svg') }}" alt="">
            <div>
                <strong>{{ __('login.ministry') }}</strong>
                <span>{{ __('login.government') }}</span>
            </div>
        </div>
        <div class="auth-pitch">
            <h1>{{ config('app.name') }}</h1>
            <p>{{ __('login.tagline') }}</p>
            <ul class="auth-points">
                <li><i class="fas fa-shield-alt" aria-hidden="true"></i>{{ __('login.point_secure') }}</li>
                <li><i class="fas fa-user-check" aria-hidden="true"></i>{{ __('login.point_approved') }}</li>
                <li><i class="fas fa-mobile-alt" aria-hidden="true"></i>{{ __('login.point_anywhere') }}</li>
            </ul>
        </div>
        <p class="auth-foot">{{ __('login.footer', ['year' => $digits(date('Y'))]) }}</p>
    </section>

    {{-- Right: the sign-in card --}}
    <section class="auth-side">
        <div class="auth-card">
            <img class="auth-card-logo" src="{{ asset('Government_Seal_of_Bangladesh.svg') }}" alt="">

            @if ($onOtpStep)
                {{-- ============ Step 2: the code ============ --}}
                <ol class="auth-steps" aria-label="{{ __('login.step', ['current' => $digits(2), 'total' => $digits(2)]) }}">
                    <li class="is-done"><span class="auth-step-no"><i class="fas fa-check" aria-hidden="true"></i></span>{{ __('login.step_mobile') }}</li>
                    <li class="auth-step-line is-done" aria-hidden="true"></li>
                    <li class="is-current" aria-current="step"><span class="auth-step-no">{{ $digits(2) }}</span>{{ __('login.step_otp') }}</li>
                </ol>
                <div class="auth-card-head">
                    <h2 class="auth-title">{{ __('login.otp_title') }}</h2>
                    <p class="auth-sub">{{ __('login.otp_subtitle', ['mobile' => $maskedMobile, 'digits' => $digits($otpDigits)]) }}</p>
                </div>

                @if (session('otp_sent'))
                    <p class="auth-ok" role="status"><i class="fas fa-check-circle" aria-hidden="true"></i>{{ __('login.otp_sent') }}</p>
                @endif

                <form method="post" action="{{ route('login.otp.verify') }}" id="otp-form" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" name="code" id="otp-code">
                    <div class="otp-boxes @error('code') is-invalid @enderror" id="otp-boxes">
                        @for ($i = 1; $i <= $otpDigits; $i++)
                            <input type="text" inputmode="numeric" pattern="[0-9০-৯]*" maxlength="1" autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}"
                                aria-label="{{ __('login.otp_label', ['n' => $digits($i)]) }}" @if ($i === 1) autofocus @endif>
                        @endfor
                    </div>
                    @error('code')
                        <p class="auth-error" role="alert"><i class="fas fa-exclamation-circle" aria-hidden="true"></i><span>{{ $message }}</span></p>
                    @enderror

                    <div class="otp-meta">
                        <span id="otp-expiry" data-seconds="{{ $expiresIn }}" data-over="{{ __('login.expired') }}">
                            <i class="fas fa-clock" aria-hidden="true"></i> {{ __('login.expires_in') }} <strong id="otp-expiry-time"></strong>
                        </span>
                    </div>

                    <button type="submit" class="auth-btn" id="otp-submit" disabled>
                        <i class="fas fa-check" aria-hidden="true"></i> {{ __('login.verify') }}
                    </button>
                </form>

                <div class="otp-row">
                    <form method="post" action="{{ route('login.otp.cancel') }}">
                        @csrf
                        <button type="submit" class="auth-link"><i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('login.change_number') }}</button>
                    </form>
                    <form method="post" action="{{ route('login.otp.resend') }}">
                        @csrf
                        <button type="submit" class="auth-link" id="otp-resend" data-seconds="{{ $resendIn }}"
                            data-label="{{ __('login.resend') }}" data-wait="{{ __('login.resend_in') }}">
                            <i class="fas fa-redo" aria-hidden="true"></i> <span id="otp-resend-text">{{ __('login.resend') }}</span>
                        </button>
                    </form>
                </div>
            @else
                {{-- ============ Step 1: the mobile number ============ --}}
                <div id="mobile-pane" @if ($onEmail) hidden @endif>
                    <ol class="auth-steps" aria-label="{{ __('login.step', ['current' => $digits(1), 'total' => $digits(2)]) }}">
                        <li class="is-current" aria-current="step"><span class="auth-step-no">{{ $digits(1) }}</span>{{ __('login.step_mobile') }}</li>
                        <li class="auth-step-line" aria-hidden="true"></li>
                        <li><span class="auth-step-no">{{ $digits(2) }}</span>{{ __('login.step_otp') }}</li>
                    </ol>
                    <div class="auth-card-head">
                        <h2 class="auth-title">{{ __('login.title') }}</h2>
                        <p class="auth-sub">{{ __('login.subtitle') }}</p>
                    </div>

                    <form method="post" action="{{ route('login.otp.send') }}" novalidate>
                        @csrf
                        <label class="auth-label" for="mobile">{{ __('login.mobile') }}</label>
                        <div class="auth-field @error('mobile') is-invalid @enderror">
                            <span class="auth-prefix"><i class="fas fa-mobile-alt" aria-hidden="true"></i>{{ $isBn ? '+৮৮' : '+88' }}</span>
                            <input type="tel" id="mobile" name="mobile" inputmode="numeric" autocomplete="tel-national" maxlength="17"
                                value="{{ old('mobile') }}" placeholder="{{ __('login.mobile_placeholder') }}" required @if (! $onEmail) autofocus @endif
                                @error('mobile') aria-invalid="true" aria-describedby="mobile-error" @enderror>
                        </div>
                        @error('mobile')
                            <p class="auth-error" id="mobile-error" role="alert"><i class="fas fa-exclamation-circle" aria-hidden="true"></i><span>{{ $message }}</span></p>
                        @enderror

                        <button type="submit" class="auth-btn">
                            {{ __('login.send') }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>

                    <p class="auth-note"><i class="fas fa-user-shield" aria-hidden="true"></i><span>{{ __('login.approved_only') }}</span></p>

                    <div class="auth-or">{{ __('login.or') }}</div>
                    <div class="auth-center">
                        <button type="button" class="auth-link" data-show="email-pane"><i class="fas fa-envelope" aria-hidden="true"></i> {{ __('login.use_email') }}</button>
                    </div>
                </div>

                {{-- ============ The other way in: e-mail and password ============ --}}
                <div id="email-pane" @if (! $onEmail) hidden @endif>
                    <div class="auth-card-head">
                        <h2 class="auth-title">{{ __('login.use_email') }}</h2>
                        <p class="auth-sub">&nbsp;</p>
                    </div>

                    <form method="post" action="{{ url('/login') }}">
                        @csrf
                        <label class="auth-label" for="email">{{ __('login.email') }}</label>
                        <div class="auth-field @error('email') is-invalid @enderror">
                            <span class="auth-prefix"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required @if ($onEmail) autofocus @endif>
                        </div>
                        @error('email')
                            <p class="auth-error" role="alert"><i class="fas fa-exclamation-circle" aria-hidden="true"></i><span>{{ $message }}</span></p>
                        @enderror

                        <label class="auth-label auth-gap" for="password">{{ __('login.password') }}</label>
                        <div class="auth-field @error('password') is-invalid @enderror">
                            <span class="auth-prefix"><i class="fas fa-lock" aria-hidden="true"></i></span>
                            <input type="password" id="password" name="password" autocomplete="current-password" required>
                            <button type="button" id="toggle-password" aria-label="{{ __('login.show_password') }}" aria-pressed="false"><i class="fas fa-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password')
                            <p class="auth-error" role="alert"><i class="fas fa-exclamation-circle" aria-hidden="true"></i><span>{{ $message }}</span></p>
                        @enderror

                        <button type="submit" class="auth-btn"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> {{ __('login.email_login') }}</button>
                    </form>

                    <div class="auth-or">{{ __('login.or') }}</div>
                    <div class="auth-center">
                        <button type="button" class="auth-link" data-show="mobile-pane"><i class="fas fa-mobile-alt" aria-hidden="true"></i> {{ __('login.use_mobile') }}</button>
                    </div>
                </div>
            @endif
        </div>
    </section>
</main>

<script>
    (function () {
        var useBangla = @json($isBn);
        var toLatin = function (text) { return String(text).replace(/[০-৯]/g, function (d) { return '০১২৩৪৫৬৭৮৯'.indexOf(d); }); };
        var toLocal = function (text) { return useBangla ? String(text).replace(/[0-9]/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }) : String(text); };
        var clock = function (seconds) {
            var m = Math.floor(seconds / 60), s = seconds % 60;
            return toLocal((m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s);
        };

        // ---- Mobile / e-mail: switch between the two ways of signing in ----
        Array.prototype.forEach.call(document.querySelectorAll('[data-show]'), function (button) {
            button.addEventListener('click', function () {
                ['mobile-pane', 'email-pane'].forEach(function (id) {
                    var pane = document.getElementById(id);
                    if (pane) { pane.hidden = id !== button.getAttribute('data-show'); }
                });
                var first = document.querySelector('#' + button.getAttribute('data-show') + ' input:not([type=hidden])');
                if (first) { first.focus(); }
            });
        });
        var toggle = document.getElementById('toggle-password');
        if (toggle) {
            toggle.addEventListener('click', function () {
                var field = document.getElementById('password'), show = field.type === 'password';
                field.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(show));
                toggle.firstElementChild.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        }

        // ---- The code: one digit per box, moving on by itself; a pasted code fills them all ----
        var boxes = Array.prototype.slice.call(document.querySelectorAll('#otp-boxes input'));
        if (!boxes.length) { return; }
        var hidden = document.getElementById('otp-code'), submit = document.getElementById('otp-submit');
        var expired = false;

        function sync() {
            var code = boxes.map(function (box) { return toLatin(box.value); }).join('');
            hidden.value = code;
            boxes.forEach(function (box) { box.classList.toggle('is-filled', box.value !== ''); });
            submit.disabled = expired || code.length !== boxes.length;
        }
        function fill(text, from) {
            var chars = toLatin(text).replace(/[^0-9]/g, '').split('');
            chars.forEach(function (ch, i) { if (boxes[from + i]) { boxes[from + i].value = toLocal(ch); } });
            var next = Math.min(from + chars.length, boxes.length - 1);
            boxes[next].focus();
            sync();
        }
        boxes.forEach(function (box, index) {
            box.addEventListener('input', function () {
                var value = toLatin(box.value).replace(/[^0-9]/g, '');
                if (value.length > 1) { box.value = ''; fill(value, index); return; }
                box.value = toLocal(value);
                if (value !== '' && boxes[index + 1]) { boxes[index + 1].focus(); }
                sync();
            });
            box.addEventListener('keydown', function (event) {
                if (event.key === 'Backspace' && box.value === '' && boxes[index - 1]) { boxes[index - 1].focus(); boxes[index - 1].value = ''; sync(); event.preventDefault(); }
                if (event.key === 'ArrowLeft' && boxes[index - 1]) { boxes[index - 1].focus(); }
                if (event.key === 'ArrowRight' && boxes[index + 1]) { boxes[index + 1].focus(); }
            });
            box.addEventListener('paste', function (event) {
                event.preventDefault();
                fill((event.clipboardData || window.clipboardData).getData('text'), index);
            });
            box.addEventListener('focus', function () { box.select(); });
        });

        // ---- Time left for the code, and until another one may be asked for ----
        var expiry = document.getElementById('otp-expiry'), expiryTime = document.getElementById('otp-expiry-time');
        var resend = document.getElementById('otp-resend'), resendText = document.getElementById('otp-resend-text');
        var left = parseInt(expiry.getAttribute('data-seconds'), 10) || 0;
        var wait = parseInt(resend.getAttribute('data-seconds'), 10) || 0;

        function tick() {
            if (left > 0) {
                expiryTime.textContent = clock(left);
            } else if (!expired) {
                expired = true;
                expiry.className = 'is-over';
                expiry.textContent = expiry.getAttribute('data-over');
                sync();
            }
            resend.disabled = wait > 0;
            resendText.textContent = wait > 0
                ? resend.getAttribute('data-wait') + ' ' + clock(wait)
                : resend.getAttribute('data-label');
            left -= 1;
            wait -= 1;
        }
        tick();
        setInterval(tick, 1000);
        sync();
    })();
</script>
</body>
</html>
