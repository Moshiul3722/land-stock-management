@extends('layouts.app')

@push('page_css')
    <style>
        /* ---- Profile page ---- */
        .profile-page { padding: 1rem 0.5rem 1.5rem; }
        .profile-page .pp-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; margin-bottom: 1rem; }
        .profile-page .pp-crumb { display: flex; flex-wrap: wrap; gap: 0.4rem; margin: 0; padding: 0; list-style: none; color: #64748b; font-size: 0.875rem; }
        .profile-page .pp-crumb a { color: #1d65db; font-weight: 500; }
        .profile-page .pp-crumb li + li::before { content: "/"; margin-right: 0.4rem; color: #cbd5e1; }
        .profile-page .pp-title { margin: 0; color: #0f172a; font-size: 1.5rem; font-weight: 700; line-height: 1.25; }
        .profile-page .pp-subtitle { margin: 0.15rem 0 0; color: #64748b; font-size: 0.875rem; }
        .profile-page .pp-head-text { text-align: right; }

        .profile-page .pp-grid { display: grid; grid-template-columns: minmax(0, 20rem) minmax(0, 1fr); gap: 1rem; align-items: start; }
        .profile-page .pp-card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 12px 28px -18px rgba(15, 23, 42, 0.25); }

        /* Identity card */
        .profile-page .pp-cover { height: 6rem; background: linear-gradient(120deg, #13244f 0%, #1d65db 60%, #4f46e5 100%); }
        .profile-page .pp-identity { padding: 0 1.25rem 1.25rem; text-align: center; }
        .profile-page .pp-avatar { width: 6.5rem; height: 6.5rem; margin-top: -3.25rem; border: 4px solid #fff; border-radius: 999px; background: #fff; object-fit: cover; box-shadow: 0 8px 20px -10px rgba(15, 23, 42, 0.5); }
        .profile-page .pp-name { margin: 0.6rem 0 0; color: #0f172a; font-size: 1.2rem; font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; }
        .profile-page .pp-designation { margin: 0.15rem 0 0; color: #475569; font-size: 0.9rem; }
        .profile-page .pp-role { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.6rem; padding: 0.25rem 0.75rem; border: 1px solid #c7d9fb; border-radius: 999px; background: #eef4ff; color: #1d4fd8; font-size: 0.8125rem; font-weight: 600; }
        .profile-page .pp-role::before { content: ""; width: 0.4rem; height: 0.4rem; border-radius: 999px; background: currentColor; }
        .profile-page .pp-facts { margin: 1.1rem 0 0; padding: 0; list-style: none; text-align: left; }
        .profile-page .pp-facts li { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.7rem 0; border-top: 1px solid #eef2f7; }
        .profile-page .pp-fact-icon { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 0.65rem; background: #f1f5f9; color: #1d65db; }
        .profile-page .pp-fact-label { display: block; color: #64748b; font-size: 0.75rem; font-weight: 600; }
        .profile-page .pp-fact-value { display: block; color: #0f172a; font-size: 0.9rem; font-weight: 500; overflow-wrap: anywhere; }

        /* Tabs */
        .profile-page .pp-tabs { display: flex; gap: 4px; margin: 1rem 1.25rem 0; padding: 4px; border-radius: 0.75rem; background: #f1f5f9; }
        .profile-page .pp-tabs .nav-item { flex: 1 1 0; }
        .profile-page .pp-tabs .nav-link { display: flex; align-items: center; justify-content: center; gap: 0.45rem; min-height: 2.5rem; padding: 0 0.75rem; border-radius: 0.55rem; background: transparent; color: #64748b; font-size: 0.9375rem; font-weight: 600; text-align: center; transition: background-color 0.15s, color 0.15s, box-shadow 0.15s; }
        .profile-page .pp-tabs .nav-link:hover { color: #0f172a; }
        .profile-page .pp-tabs .nav-link.active { background: #fff; color: #1d4fd8; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.14); }
        .profile-page .pp-tabs .nav-link:focus-visible { outline: 0; box-shadow: 0 0 0 3px rgba(47, 124, 246, 0.35); }

        /* Forms */
        .profile-page .pp-body { padding: 1.25rem; }
        .profile-page .pp-section { margin: 0 0 0.85rem; color: #0f172a; font-size: 1rem; font-weight: 700; }
        .profile-page .pp-section + .pp-fields { margin-bottom: 1.5rem; }
        /* Two fields side by side (never more), one per row in a narrow card */
        .profile-page .pp-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, max(15rem, calc(50% - 0.5rem))), 1fr)); gap: 1rem; }
        .profile-page .pp-field-wide { grid-column: 1 / -1; }
        .profile-page .pp-field label.pp-label { display: block; margin-bottom: 0.35rem; color: #334155; font-size: 0.875rem; font-weight: 600; }
        .profile-page .pp-field .form-control { height: 2.75rem; border: 1px solid #cbd5e1; border-radius: 0.6rem; color: #0f172a; font-size: 0.9375rem; box-shadow: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .profile-page .pp-field .form-control:focus { border-color: #2f7cf6; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.18); }
        .profile-page .pp-field .form-control.is-invalid { border-color: #dc2626; }
        .profile-page .pp-field .form-control[readonly] { border-color: #e2e8f0; background: #f1f5f9; color: #64748b; cursor: not-allowed; }
        .profile-page .pp-hint { display: block; margin-top: 0.35rem; color: #64748b; font-size: 0.8125rem; line-height: 1.45; }
        .profile-page .pp-error { display: block; margin-top: 0.35rem; color: #dc2626; font-size: 0.8125rem; font-weight: 600; }

        /* File pickers */
        .profile-page .custom-file { height: 2.75rem; }
        .profile-page .custom-file-input { height: 2.75rem; }
        .profile-page .custom-file-label { display: flex; align-items: center; height: 2.75rem; padding: 0 5.5rem 0 0.9rem; overflow: hidden; border: 1px dashed #94a3b8; border-radius: 0.6rem; background: #f8fafc; color: #475569; font-size: 0.9rem; white-space: nowrap; text-overflow: ellipsis; }
        .profile-page .custom-file-label::after { content: "{{ __('profile.browse') }}" !important; display: flex; align-items: center; height: auto; top: 4px; right: 4px; bottom: 4px; padding: 0 0.9rem; border: 0; border-radius: 0.45rem; background: #1d65db; color: #fff; font-size: 0.8125rem; font-weight: 600; }
        .profile-page .custom-file-input:focus ~ .custom-file-label { border-color: #2f7cf6; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.18); }
        .profile-page .pp-preview { display: flex; align-items: center; justify-content: center; min-height: 5.5rem; margin-bottom: 0.6rem; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.6rem; background: #fff; color: #94a3b8; font-size: 0.8125rem; text-align: center; }
        .profile-page .pp-preview img { max-width: 100%; max-height: 4.5rem; }
        .profile-page .pp-preview img.pp-preview-photo { width: 4.5rem; height: 4.5rem; border-radius: 999px; object-fit: cover; }

        /* Buttons */
        .profile-page .pp-actions { display: flex; justify-content: flex-end; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #eef2f7; }
        .profile-page .pp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; min-height: 2.6rem; padding: 0 1.25rem; border: 1px solid #1d65db; border-radius: 0.6rem; background: #1d65db linear-gradient(to bottom, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0)); color: #fff; font-size: 0.9375rem; font-weight: 600; cursor: pointer; box-shadow: 0 6px 14px -6px rgba(37, 99, 235, 0.7); transition: background-color 0.15s, border-color 0.15s, box-shadow 0.15s; }
        .profile-page .pp-btn:hover { border-color: #1a51b0; background-color: #1a51b0; }
        .profile-page .pp-btn:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.3); }

        .profile-page .pp-flash { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1rem; padding: 0.75rem 1rem; border: 1px solid #a7f3d0; border-radius: 0.75rem; background: #ecfdf5; color: #065f46; font-size: 0.9rem; font-weight: 600; }

        @media (max-width: 1199.98px) {
            .profile-page .pp-grid { grid-template-columns: minmax(0, 1fr); }
        }

        @media (max-width: 575.98px) {
            .profile-page .pp-head-text { width: 100%; text-align: left; }
            .profile-page .pp-body { padding: 1rem; }
            .profile-page .pp-tabs { margin: 1rem 1rem 0; }
            .profile-page .pp-actions .pp-btn { width: 100%; }
        }
    </style>
@endpush

@section('content')
    @php
        $profileBn = app()->getLocale() === 'bn';
        // Role name, department and joining date in the page's language
        $role = \App\Models\CnfRole::find($user->role_id);
        $profileRole = $role ? ($profileBn ? ($role->role_name_bn ?: $role->role_name_en) : $role->role_name_en) : __('profile.default_role');
        $profileJoined = $profileBn
            ? strtr($user->created_at->locale('bn')->translatedFormat('j F Y'), ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : $user->created_at->format('M d, Y');
        $profileName = $profileBn && trim((string) $user->name_bn) !== '' ? $user->name_bn : $user->name;
        $department = $user->department;
        $profileDepartment = $department ? ($profileBn ? ($department->name_bn ?: $department->name) : $department->name) : '';
        $photoUrl = $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('theme/assets/img/gov.png');
        $signatureUri = $user->signatureDataUri();
        // A password error brings the page back on the password tab
        $onPasswordTab = $errors->has('current_password') || $errors->has('password');
    @endphp

    <section class="content profile-page">
        <div class="container-fluid">
            <div class="pp-head">
                <ol class="pp-crumb">
                    <li><a href="{{ route('home') }}">{{ __('profile.home') }}</a></li>
                    <li aria-current="page">{{ __('profile.breadcrumb') }}</li>
                </ol>
                <div class="pp-head-text">
                    <h1 class="pp-title">{{ __('profile.title') }}</h1>
                    <p class="pp-subtitle">{{ __('profile.subtitle') }}</p>
                </div>
            </div>

            @if (session('success'))
                <div class="pp-flash" role="status">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="pp-grid">
                {{-- Left: who you are --}}
                <div class="pp-card">
                    <div class="pp-cover"></div>
                    <div class="pp-identity">
                        <img class="pp-avatar" src="{{ $photoUrl }}" alt="{{ __('profile.photo_alt') }}">
                        <h2 class="pp-name">{{ $profileName }}</h2>
                        @if (trim((string) $user->designation) !== '')
                            <p class="pp-designation">{{ $user->designation }}</p>
                        @endif
                        <span class="pp-role">{{ $profileRole }}</span>

                        <ul class="pp-facts">
                            <li>
                                <span class="pp-fact-icon"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                <span>
                                    <span class="pp-fact-label">{{ __('profile.email') }}</span>
                                    <span class="pp-fact-value">{{ $user->email }}</span>
                                </span>
                            </li>
                            @if ($profileDepartment !== '')
                                <li>
                                    <span class="pp-fact-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                                    <span>
                                        <span class="pp-fact-label">{{ __('profile.department') }}</span>
                                        <span class="pp-fact-value">{{ $profileDepartment }}</span>
                                    </span>
                                </li>
                            @endif
                            <li>
                                <span class="pp-fact-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                                <span>
                                    <span class="pp-fact-label">{{ __('profile.joined') }}</span>
                                    <span class="pp-fact-value">{{ $profileJoined }}</span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Right: settings --}}
                <div class="pp-card">
                    <ul class="nav pp-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $onPasswordTab ? '' : 'active' }}" href="#settings" data-toggle="tab" role="tab">
                                <i class="fas fa-user-edit" aria-hidden="true"></i> {{ __('profile.tab_settings') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $onPasswordTab ? 'active' : '' }}" href="#password" data-toggle="tab" role="tab">
                                <i class="fas fa-lock" aria-hidden="true"></i> {{ __('profile.tab_password') }}
                            </a>
                        </li>
                    </ul>

                    <div class="pp-body">
                        <div class="tab-content">

                            {{-- Tab 1: General Settings --}}
                            <div class="tab-pane {{ $onPasswordTab ? '' : 'active' }}" id="settings" role="tabpanel">
                                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <h3 class="pp-section">{{ __('profile.section_personal') }}</h3>
                                    <div class="pp-fields">
                                        <div class="pp-field">
                                            <label class="pp-label" for="name">{{ __('profile.name_en') }}</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                            @error('name')
                                                <span class="pp-error" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="pp-field">
                                            <label class="pp-label" for="name_bn">{{ __('profile.name_bn') }}</label>
                                            <input type="text" class="form-control @error('name_bn') is-invalid @enderror" id="name_bn" name="name_bn" value="{{ old('name_bn', $user->name_bn) }}">
                                            @error('name_bn')
                                                <span class="pp-error" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="pp-field">
                                            <label class="pp-label" for="email">{{ __('profile.email') }}</label>
                                            {{-- Shown only: the email address cannot be changed here --}}
                                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly aria-describedby="email-hint">
                                            <small class="pp-hint" id="email-hint"><i class="fas fa-lock" aria-hidden="true"></i> {{ __('profile.email_locked') }}</small>
                                        </div>
                                        <div class="pp-field">
                                            <label class="pp-label" for="designation">{{ __('profile.designation') }}</label>
                                            <input type="text" class="form-control @error('designation') is-invalid @enderror" id="designation" name="designation" maxlength="191" value="{{ old('designation', $user->designation) }}">
                                            @error('designation')
                                                <span class="pp-error" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Signature and photo: the signature is printed on the requisition slip --}}
                                    <h3 class="pp-section">{{ __('profile.section_slip') }}</h3>
                                    <div class="pp-fields">
                                        <div class="pp-field">
                                            <label class="pp-label" for="signature">{{ __('profile.signature') }}</label>
                                            <div class="pp-preview">
                                                @if ($signatureUri)
                                                    <img src="{{ $signatureUri }}" alt="{{ __('profile.signature_current') }}">
                                                @else
                                                    <span>{{ __('profile.signature_empty') }}</span>
                                                @endif
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input @error('signature') is-invalid @enderror" id="signature" name="signature" accept="image/png,image/jpeg">
                                                <label class="custom-file-label" for="signature">{{ __('profile.choose_file') }}</label>
                                            </div>
                                            @error('signature')
                                                <span class="pp-error" role="alert">{{ $message }}</span>
                                            @enderror
                                            <small class="pp-hint">{{ __('profile.signature_hint') }}</small>
                                        </div>
                                        <div class="pp-field">
                                            <label class="pp-label" for="profile_photo">{{ __('profile.photo') }}</label>
                                            <div class="pp-preview">
                                                <img class="pp-preview-photo" src="{{ $photoUrl }}" alt="{{ __('profile.photo_alt') }}">
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/png,image/jpeg,image/gif">
                                                <label class="custom-file-label" for="profile_photo">{{ __('profile.choose_file') }}</label>
                                            </div>
                                            @error('profile_photo')
                                                <span class="pp-error" role="alert">{{ $message }}</span>
                                            @enderror
                                            <small class="pp-hint">{{ __('profile.photo_hint') }}</small>
                                        </div>
                                    </div>

                                    <div class="pp-actions">
                                        <button type="submit" class="pp-btn">
                                            <i class="fas fa-save" aria-hidden="true"></i> {{ __('profile.update') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Tab 2: Change Password --}}
                            <div class="tab-pane {{ $onPasswordTab ? 'active' : '' }}" id="password" role="tabpanel">
                                <form method="POST" action="{{ route('profile.password') }}">
                                    @csrf
                                    <h3 class="pp-section">{{ __('profile.section_password') }}</h3>
                                    <div class="pp-fields">
                                        <div class="pp-field pp-field-wide">
                                            <label class="pp-label" for="current_password">{{ __('profile.current_password') }}</label>
                                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" autocomplete="current-password" required>
                                            @error('current_password')
                                                <span class="pp-error" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="pp-field">
                                            <label class="pp-label" for="password">{{ __('profile.new_password') }}</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password" required>
                                            @error('password')
                                                <span class="pp-error" role="alert">{{ $message }}</span>
                                            @enderror
                                            <small class="pp-hint">{{ __('profile.password_hint') }}</small>
                                        </div>
                                        <div class="pp-field">
                                            <label class="pp-label" for="password_confirmation">{{ __('profile.confirm_password') }}</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                                        </div>
                                    </div>

                                    <div class="pp-actions">
                                        <button type="submit" class="pp-btn">
                                            <i class="fas fa-key" aria-hidden="true"></i> {{ __('profile.change_password') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
<script>
    $(function () {
        bsCustomFileInput.init();
    });
</script>
@endpush
