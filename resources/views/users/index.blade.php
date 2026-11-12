@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $roleNames = $roles->mapWithKeys(fn($role) => [
        $role->id => $isEn ? $role->role_name_en : ($role->role_name_bn ?: $role->role_name_en),
    ]);
    $authId = auth()->id();
    // Escapes the text, then lets long e-mail addresses wrap after the "@" instead of mid-word.
    $softBreak = fn($value) => str_replace('@', '@<wbr>', e($value));
@endphp

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('user.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ __('user.title') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-0 mr-2">{{ __('user.table_header') }}</h3>
                    <button type="button" class="btn btn-sm btn-success user-touch-btn" id="open_create_user"
                        title="{{ __('user.create_new_user') }}" aria-label="{{ __('user.create_new_user') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ml-1">{{ __('user.create_new_user') }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body">
                {{-- Toolbar: role filter, page size and search share one row --}}
                <div class="form-row align-items-end mb-3 user-toolbar">
                    <div class="col-5 col-md-3 col-lg-2">
                        <label for="user_page_length" class="mb-1">{{ __('commontable.per_page') }}</label>
                        <select id="user_page_length" class="custom-select">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}">{{ $isEn ? $size : strtr((string) $size, $bnDigits) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7 col-md-4">
                        <label for="user_role_filter" class="mb-1">{{ __('user.role') }}</label>
                        <select id="user_role_filter" class="custom-select">
                            <option value="">{{ __('user.all_roles') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $roleNames[$role->id] }}">{{ $roleNames[$role->id] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-5 col-lg-6 mt-2 mt-md-0">
                        <label for="user_search" class="mb-1">{{ __('commontable.search') }}</label>
                        <input type="search" id="user_search" class="form-control" autocomplete="off"
                            placeholder="{{ __('user.search_placeholder') }}"
                            title="{{ __('user.search_placeholder') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="table-responsive col-12">
                        <table class="table" id="users_table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center user-serial-col">{{ __('commontable.serial') }}</th>
                                    <th class="text-center">{{ __('user.user_name') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ __('user.email') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ __('user.role') }}</th>
                                    <th class="text-center d-none d-lg-table-cell">{{ __('user.section_department') }}</th>
                                    <th class="text-center d-none d-xl-table-cell">{{ __('commontable.created_at') }}</th>
                                    <th class="text-center" style="width: 110px;">{{ __('commontable.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    @php
                                        // Shown as: English name (Bangla name)
                                        $userLabel = $user->name;
                                        if ($user->name_bn && $user->name_bn !== $user->name) {
                                            $userLabel = trim($user->name . ' (' . $user->name_bn . ')');
                                        }
                                        $roleName = $roleNames[$user->role_id] ?? '—';
                                        $department = $user->department
                                            ? ($isEn ? $user->department->name : ($user->department->name_bn ?: $user->department->name))
                                            : __('user.not_applicable');
                                        $createdDate = $createdTime = '—';
                                        if ($user->created_at) {
                                            $createdDate = $isEn
                                                ? $user->created_at->format('d M, Y')
                                                : bangla_date($user->created_at->timestamp, 'en');
                                            $createdTime = $isEn
                                                ? $user->created_at->format('h:i A')
                                                : strtr($user->created_at->format('h:i'), $bnDigits) . ' ' . __('user.' . $user->created_at->format('a'));
                                        }
                                        $isSelf = (int) $user->id === (int) $authId;
                                    @endphp
                                    <tr id="user_row_{{ $user->id }}">
                                        {{-- Serial number: filled in by the table script so it follows sorting, search and paging --}}
                                        <td class="text-center user-serial-col">{{ $isEn ? $loop->iteration : strtr((string) $loop->iteration, $bnDigits) }}</td>
                                        <td class="text-left user-wrap-cell" data-order="{{ $user->name }}">
                                            <span class="font-weight-bold">{!! $softBreak($userLabel) !!}</span>
                                            @if ($isSelf)
                                                <span class="badge badge-info ml-1">{{ __('user.you') }}</span>
                                            @endif
                                            {{-- On phones the hidden columns are summarised under the name --}}
                                            @if ($user->name !== $user->email)
                                                <small class="d-block d-md-none text-muted">{!! $softBreak($user->email) !!}</small>
                                            @endif
                                            <small class="d-block d-md-none text-muted">{{ $roleName }}</small>
                                        </td>
                                        <td class="text-center user-wrap-cell d-none d-md-table-cell">{!! $softBreak($user->email) !!}</td>
                                        <td class="text-center d-none d-md-table-cell">{{ $roleName }}</td>
                                        <td class="text-center user-wrap-cell d-none d-lg-table-cell">{{ $department }}</td>
                                        <td class="text-center d-none d-xl-table-cell"
                                            data-order="{{ ($user->created_at?->timestamp ?? 0) * 100000 + $user->id }}">
                                            <span class="text-nowrap">{{ $createdDate }}@if ($user->created_at)<i class="fas fa-clock text-muted ml-2 mr-1" aria-hidden="true"></i>{{ $createdTime }}@endif</span>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-sm btn-primary user-touch-btn edit-user-btn"
                                                data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                data-name-bn="{{ $user->name_bn }}" data-email="{{ $user->email }}"
                                                data-role-id="{{ $user->role_id }}"
                                                data-department-id="{{ $user->department_id }}"
                                                title="{{ __('buttons.edit') }}"
                                                aria-label="{{ __('buttons.edit') }}: {{ $userLabel }}">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger user-touch-btn delete-user-btn"
                                                data-id="{{ $user->id }}" data-label="{{ $userLabel }}"
                                                data-email="{{ $user->email }}"
                                                title="{{ __('buttons.delete') }}"
                                                aria-label="{{ __('buttons.delete') }}: {{ $userLabel }}"
                                                @disabled($isSelf)>
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create / edit user modal --}}
    <div class="modal fade" id="userFormModal" tabindex="-1" role="dialog" aria-labelledby="userFormModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form id="user_form" method="POST" novalidate autocomplete="off">
                    @csrf
                    <div class="modal-header">
                        <div class="modal-heading">
                            <h5 class="modal-title" id="userFormModalLabel"></h5>
                            <p class="modal-hint">{{ __('user.form_hint') }}</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('buttons.cancel') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="user_form_error" role="alert"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_name">{{ __('user.user_name_en') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="user_name" class="form-control" maxlength="191"
                                        required>
                                    <span class="invalid-feedback" role="alert"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_name_bn">{{ __('user.user_name_bn') }}</label>
                                    <input type="text" name="name_bn" id="user_name_bn" class="form-control"
                                        maxlength="191" lang="bn">
                                    <span class="invalid-feedback" role="alert"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_email">{{ __('user.email') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" id="user_email" class="form-control"
                                        maxlength="191" required autocomplete="off" inputmode="email">
                                    <span class="invalid-feedback" role="alert"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_role_id">{{ __('user.role') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="role_id" id="user_role_id" class="custom-select" required>
                                        <option value="">{{ __('user.select_role') }}</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $roleNames[$role->id] }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback" role="alert"></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="user_department_id">{{ __('user.section_department') }}
                                        <span class="text-danger d-none" id="user_department_required">*</span></label>
                                    <select name="department_id" id="user_department_id" class="custom-select" style="width: 100%;">
                                        <option value="">{{ __('user.select_department') }}</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $isEn ? $dept->name : ($dept->name_bn ?: $dept->name) }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback" role="alert"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-md-0">
                                    <label for="user_password"><span id="user_password_label"></span>
                                        <span class="text-danger" id="user_password_required">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="user_password" class="form-control"
                                            maxlength="191" autocomplete="new-password"
                                            aria-describedby="user_password_hint">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary user-password-toggle"
                                                data-target="#user_password" aria-pressed="false"
                                                aria-label="{{ __('user.show_password') }}"
                                                title="{{ __('user.show_password') }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <span class="invalid-feedback" role="alert"></span>
                                    </div>
                                    <small class="form-text text-muted" id="user_password_hint"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="user_confirm_password"><span id="user_confirm_label"></span>
                                        <span class="text-danger" id="user_confirm_required">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_password" id="user_confirm_password"
                                            class="form-control" maxlength="191" autocomplete="new-password">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary user-password-toggle"
                                                data-target="#user_confirm_password" aria-pressed="false"
                                                aria-label="{{ __('user.show_password') }}"
                                                title="{{ __('user.show_password') }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <span class="invalid-feedback" role="alert"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer user-modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" id="user_form_submit">
                            <i class="fas fa-save" id="user_form_icon" aria-hidden="true"></i>
                            <span id="user_form_submit_text"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete confirmation: same layout as the lot / product delete dialog --}}
    <div class="modal fade" id="userDeleteModal" tabindex="-1" role="dialog" aria-labelledby="userDeleteModalLabel"
        aria-describedby="userDeleteModalText" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-confirm">
                    <div class="alert alert-danger d-none" id="user_delete_error" role="alert"></div>
                    <div class="modal-confirm-icon" aria-hidden="true"><i class="fas fa-trash-alt"></i></div>
                    <h5 class="modal-title" id="userDeleteModalLabel">{{ __('user.delete_user') }}</h5>
                    <p class="modal-confirm-text" id="userDeleteModalText">{{ __('user.delete_confirm_text') }}</p>
                    <p class="modal-confirm-name" id="user_delete_name"></p>
                    <p class="modal-confirm-sub" id="user_delete_email"></p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="user_delete_cancel">
                        {{ __('sweetalert.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="user_delete_confirm">
                        <i class="fas fa-trash-alt" id="user_delete_icon" aria-hidden="true"></i>
                        {{ __('sweetalert.delete_notice') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_css')
    <style>
        /* User list: comfortable touch targets and wrapping on small screens */
        .user-touch-btn {
            min-width: 38px;
            min-height: 38px;
        }

        .user-wrap-cell {
            overflow-wrap: break-word;
        }

        /* Compact rows */
        #users_table td {
            vertical-align: middle;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
            line-height: 1.35;
        }

        #users_table thead th {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        #users_table .user-touch-btn {
            min-width: 34px;
            min-height: 34px;
            padding: 0.2rem 0.45rem;
        }

        /* Searchable section/department box, sized and coloured like the other fields */
        #userFormModal .select2-container .select2-selection--single {
            height: 2.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        #userFormModal .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: calc(2.75rem - 2px);
            padding-left: 0.75rem;
            padding-right: 3.9rem;
            color: #495057;
        }

        #userFormModal .select2-container .select2-selection--single .select2-selection__arrow {
            height: calc(2.75rem - 2px);
            right: 0.4rem;
        }

        /* Clear (×): a plain, centred icon just left of the arrow — not a boxed button */
        #userFormModal .select2-container .select2-selection--single {
            position: relative;
            padding: 0; /* the text span carries the padding, so it lines up with other fields */
        }

        #userFormModal .select2-container .select2-selection--single .select2-selection__clear {
            position: absolute;
            top: 0;
            right: 1.9rem;
            float: none;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 100%;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0.25rem;
            background: transparent;
            box-shadow: none;
            -webkit-appearance: none;
            appearance: none;
            color: #6c757d;
            font-size: 1.25rem;
            font-weight: 400;
            line-height: 1;
            cursor: pointer;
        }

        #userFormModal .select2-container .select2-selection--single .select2-selection__clear:hover,
        #userFormModal .select2-container .select2-selection--single .select2-selection__clear:focus {
            color: #dc3545;
            outline: 0;
        }

        #userFormModal .select2-container .select2-selection--single .select2-selection__clear span {
            line-height: 1;
        }

        #userFormModal .select2-container--focus .select2-selection--single,
        #userFormModal .select2-container--open .select2-selection--single {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: 0;
        }

        #userFormModal select.is-invalid + .select2-container .select2-selection--single {
            border-color: #dc3545;
        }

        #userFormModal .select2-dropdown {
            border-color: #ced4da;
        }

        #userFormModal .select2-search--dropdown .select2-search__field {
            padding: 0.4rem 0.6rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            font-size: 1rem;
        }

        #userFormModal .select2-results__option {
            padding: 0.4rem 0.75rem;
            line-height: 1.4;
        }

        #userFormModal .select2-results > .select2-results__options {
            max-height: 240px;
        }

        #userFormModal .input-group > .invalid-feedback {
            flex: 0 0 100%;
        }

        #users_table small {
            font-size: 0.875rem;
        }

        #users_table .user-serial-col {
            width: 64px;
            white-space: nowrap;
        }

        /* Table footer (entry count + pagination) sits on its own tinted band */
        #users_table_wrapper > .row:last-child {
            margin: 0.75rem 0 0;
            padding: 0.6rem 0.75rem;
            align-items: center;
            background: #e9eefb;
            border: 1px solid #cfd9f3;
            border-radius: 0.5rem;
        }

        #users_table_wrapper > .row:last-child .dt-info {
            padding-top: 0;
            color: #1f2d5a;
            font-weight: 600;
        }

        #users_table_wrapper > .row:last-child .pagination {
            margin-bottom: 0;
        }

        @media (max-width: 575.98px) {
            #users_table th,
            #users_table td {
                padding: 0.4rem 0.3rem;
            }

            /* Keep buttons finger-sized on phones */
            #users_table .user-touch-btn {
                min-width: 38px;
                min-height: 38px;
            }

            #users_table_wrapper .dt-search,
            #users_table_wrapper .dataTables_filter {
                display: flex;
                align-items: center;
                text-align: left;
            }

            #users_table_wrapper .dt-search input,
            #users_table_wrapper .dataTables_filter input {
                flex: 1 1 auto;
                min-width: 0;
                min-height: 40px;
            }

            #users_table th:last-child {
                width: 88px !important;
            }

            /* Narrow screens: allow a break anywhere rather than scrolling sideways */
            .user-wrap-cell {
                overflow-wrap: anywhere;
            }

            #users_table .user-serial-col {
                width: 40px;
                padding-left: 2px;
                padding-right: 2px;
            }

            #users_table thead th:not(:last-child) {
                padding-right: 22px;
                padding-left: 6px;
            }

            /* Compact page buttons so the whole pager stays on one line */
            #users_table_wrapper .pagination .page-link {
                padding: 0.4rem 0.55rem;
            }

            /* Full-width, stacked buttons in modals on phones */
            .user-modal-footer {
                flex-direction: column-reverse;
            }

            .user-modal-footer>.btn {
                width: 100%;
                margin: 0.25rem 0;
                min-height: 44px;
            }

            #userFormModal .select2-container .select2-selection--single {
                height: 44px;
            }

            #userFormModal .select2-container .select2-selection--single .select2-selection__rendered {
                line-height: 42px;
                font-size: 16px;
            }

            #userFormModal .select2-container .select2-selection--single .select2-selection__arrow {
                height: 42px;
            }

            #userFormModal .select2-search--dropdown .select2-search__field {
                min-height: 44px;
                font-size: 16px;
            }

            #userFormModal .select2-results__option {
                padding: 0.6rem 0.75rem;
            }

            #userFormModal .form-control,
            #userFormModal .custom-select,
            #user_role_filter,
            #user_page_length,
            #user_search {
                min-height: 44px;
                font-size: 16px; /* stops iOS zooming into the field */
            }
        }

        /* DataTables footer (info + pagination) stacks neatly on narrow screens */
        @media (max-width: 767.98px) {
            #users_table_wrapper .dt-info,
            #users_table_wrapper .dt-paging,
            #users_table_wrapper .dt-length {
                text-align: center;
                float: none;
                margin-top: 0.5rem;
            }

            #users_table_wrapper .dt-paging .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(function() {
            var TOAST_KEY = 'user_toast';
            var DEPARTMENT_REQUIRED_ROLE = '5';
            var PASSWORD_MIN_LENGTH = 6;
            var urls = {
                index: "{{ route('users.index') }}",
                store: "{{ route('users.store') }}",
                item: "{{ route('users.update', ['user' => ':id']) }}"
            };
            var text = {
                createTitle: @json(__('user.create_new_user')),
                editTitle: @json(__('user.edit_user')),
                save: @json(__('buttons.save')),
                update: @json(__('buttons.update')),
                required: @json(__('sweetalert.required')),
                genericError: @json(__('sweetalert.error_title')),
                confirmTitle: @json(__('sweetalert.confirmation')),
                confirmUpdate: @json(__('sweetalert.about_to_update')),
                confirmUpdateYes: @json(__('sweetalert.update_notice')),
                confirmNo: @json(__('sweetalert.cancel')),
                created: @json(__('user.toast_created')),
                updated: @json(__('user.toast_updated')),
                deleted: @json(__('user.toast_deleted')),
                password: @json(__('user.password')),
                confirmPassword: @json(__('user.confirm_password')),
                resetPassword: @json(__('user.reset_password')),
                resetPasswordConfirm: @json(__('user.reset_password_confirm')),
                passwordHint: @json(__('user.password_hint')),
                resetPasswordHint: @json(__('user.reset_password_hint')),
                passwordMismatch: @json(__('user.password_match')),
                passwordTooShort: @json(__('user.password_min')),
                selectDepartment: @json(__('user.select_department')),
                clearDepartment: @json(__('user.clear_department')),
                searchDepartment: @json(__('user.search_department')),
                noDepartmentFound: @json(__('user.no_department_found')),
                searching: @json(__('user.searching')),
                passwordKept: @json(__('user.password_kept_placeholder')),
                showPassword: @json(__('user.show_password')),
                hidePassword: @json(__('user.hide_password')),
                departmentRequired: @json(__('user.department_required'))
            };

            function toast(type, message) {
                (window.appToast || window.toastr)[type](message);
            }

            // ---- Notification shown after the list reloads ----
            function notifyAfterReload(message) {
                try {
                    sessionStorage.setItem(TOAST_KEY, message);
                } catch (e) {}
                $('#wait').show();
                window.location.href = urls.index;
            }

            try {
                var pending = sessionStorage.getItem(TOAST_KEY);
                if (pending) {
                    sessionStorage.removeItem(TOAST_KEY);
                    toast('success', pending);
                }
            } catch (e) {}

            // ---- Table ----
            var useBanglaDigits = @json(!$isEn);
            var banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

            function localDigits(value) {
                var out = String(value);
                return useBanglaDigits ? out.replace(/[0-9]/g, function(d) {
                    return banglaDigits[d];
                }) : out;
            }

            // The page-size select and search box live in the toolbar above the table,
            // so DataTables' own controls are switched off (layout.top*).
            var table = $('#users_table').DataTable({
                paging: true,
                pageLength: 10,
                searching: true,
                bDestroy: true,
                autoWidth: false,
                // Newest user first (column 5 = created time).
                order: [
                    [5, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    searchable: false,
                    targets: 0
                }, {
                    orderable: false,
                    targets: -1
                }],
                layout: {
                    topStart: null,
                    topEnd: null,
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                formatNumber: function(number) {
                    return localDigits(number);
                },
                language: {
                    info: @json(__('commontable.dt_info')),
                    infoEmpty: @json(__('commontable.dt_info_empty')),
                    infoFiltered: @json(__('commontable.dt_info_filtered')),
                    zeroRecords: @json(__('commontable.dt_zero_records')),
                    emptyTable: @json(__('commontable.no_record_found')),
                    paginate: {
                        first: '«',
                        previous: '‹',
                        next: '›',
                        last: '»'
                    },
                    aria: {
                        paginate: {
                            first: @json(__('commontable.dt_first')),
                            previous: @json(__('commontable.dt_previous')),
                            next: @json(__('commontable.dt_next')),
                            last: @json(__('commontable.dt_last'))
                        }
                    }
                }
            });

            // Serial numbers run 1..N over the rows as currently sorted and filtered,
            // continuing across pages.
            function renumber() {
                var start = table.page.info().start;
                table.rows({
                    search: 'applied',
                    order: 'applied',
                    page: 'current'
                }).nodes().each(function(row, index) {
                    $(row).children('td').first().text(localDigits(start + index + 1));
                });
            }
            table.on('draw', renumber);
            renumber();

            // Role filter: exact match on the role column.
            $('#user_role_filter').on('change', function() {
                var value = $(this).val();
                var pattern = value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '';
                table.column(3).search(pattern, true, false).draw();
            });

            $('#user_page_length').on('change', function() {
                table.page.len(parseInt($(this).val(), 10) || 10).draw();
            });

            // Searches name, email, role and section/department.
            $('#user_search').on('input search', function() {
                table.search($(this).val()).draw();
            });

            // ---- Create / edit modal ----
            var $formModal = $('#userFormModal');
            var $form = $('#user_form');
            var $submit = $('#user_form_submit');
            var $icon = $('#user_form_icon');
            var $formError = $('#user_form_error');
            var editingId = null;

            function fieldError($field, message) {
                $field.addClass('is-invalid').attr('aria-invalid', 'true')
                    .siblings('.invalid-feedback').text(message);
            }

            function clearFormErrors() {
                $formError.addClass('d-none').text('');
                $form.find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $form.find('.invalid-feedback').text('');
            }

            function setFormBusy(busy) {
                $submit.prop('disabled', busy);
                $icon.toggleClass('fa-save', !busy).toggleClass('fa-spinner fa-spin', busy);
            }

            function syncDepartmentRequired() {
                $('#user_department_required').toggleClass('d-none',
                    String($('#user_role_id').val()) !== DEPARTMENT_REQUIRED_ROLE);
            }

            function openForm(user) {
                editingId = user ? user.id : null;
                $form[0].reset();
                clearFormErrors();
                setFormBusy(false);

                $('#userFormModalLabel').text(user ? text.editTitle : text.createTitle);
                $('#user_form_submit_text').text(user ? text.update : text.save);
                $('#user_password_label').text(user ? text.resetPassword : text.password);
                $('#user_confirm_label').text(user ? text.resetPasswordConfirm : text.confirmPassword);
                $('#user_password_hint').text(user ? text.resetPasswordHint : text.passwordHint);
                $('#user_password_required, #user_confirm_required').toggleClass('d-none', !!user);

                $('#user_name').val(user ? user.name : '');
                $('#user_name_bn').val(user ? user.nameBn : '');
                $('#user_email').val(user ? user.email : '');
                // New users default to the general "User" role.
                $('#user_role_id').val(user ? String(user.roleId) : DEPARTMENT_REQUIRED_ROLE);
                if (!$('#user_role_id').val()) {
                    $('#user_role_id').val('');
                }

                // Stored passwords are one-way hashes and cannot be shown again. When editing,
                // the fields stay empty and the placeholder says the current password is kept.
                $('#user_password, #user_confirm_password')
                    .attr('type', 'password')
                    .attr('placeholder', user ? text.passwordKept : '');
                $('.user-password-toggle').attr('aria-pressed', 'false')
                    .attr('aria-label', text.showPassword).attr('title', text.showPassword)
                    .find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                // .trigger('change') keeps the searchable (Select2) box in step with the value.
                $('#user_department_id').val(user && user.departmentId ? String(user.departmentId) : '')
                    .trigger('change');
                clearFormErrors();
                syncDepartmentRequired();

                $formModal.modal('show');
            }

            function userFromButton($btn) {
                return {
                    id: $btn.data('id'),
                    name: String($btn.attr('data-name') || ''),
                    nameBn: String($btn.attr('data-name-bn') || ''),
                    email: String($btn.attr('data-email') || ''),
                    roleId: String($btn.attr('data-role-id') || ''),
                    departmentId: String($btn.attr('data-department-id') || '')
                };
            }

            $('#open_create_user').on('click', function() {
                openForm(null);
            });

            $(document).on('click', '.edit-user-btn', function() {
                openForm(userFromButton($(this)));
            });

            $('#user_role_id').on('change', syncDepartmentRequired);

            // Section/department: searchable dropdown (Select2). The dropdown is attached to
            // the modal so its search box can receive focus inside Bootstrap's focus trap.
            $('#user_department_id').select2({
                dropdownParent: $formModal,
                width: '100%',
                placeholder: text.selectDepartment,
                allowClear: true,
                language: {
                    noResults: function() {
                        return text.noDepartmentFound;
                    },
                    searching: function() {
                        return text.searching;
                    }
                }
            }).on('change', function() {
                // Select2 rebuilds the clear (×) button on every change; label it in the page language.
                $formModal.find('.select2-selection__clear')
                    .attr('title', text.clearDepartment).attr('aria-label', text.clearDepartment);
            }).on('select2:open', function() {
                // Put the cursor in the search box and label it.
                var search = $formModal.find('.select2-container--open .select2-search__field').get(0);
                if (search) {
                    search.setAttribute('placeholder', text.searchDepartment);
                    search.setAttribute('aria-label', text.searchDepartment);
                    search.focus();
                }
            });

            // Show / hide what was typed in a password field.
            $form.on('click', '.user-password-toggle', function() {
                var $button = $(this);
                var $input = $($button.attr('data-target'));
                var show = $input.attr('type') === 'password';
                $input.attr('type', show ? 'text' : 'password');
                $button.attr('aria-pressed', show ? 'true' : 'false')
                    .attr('aria-label', show ? text.hidePassword : text.showPassword)
                    .attr('title', show ? text.hidePassword : text.showPassword)
                    .find('i').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
            });

            $formModal.on('shown.bs.modal', function() {
                $('#user_name').trigger('focus');
            });

            $form.on('input change', '.form-control, .custom-select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid')
                    .siblings('.invalid-feedback').text('');
            });

            function sendForm() {
                setFormBusy(true);
                var data = {
                    name: $.trim($('#user_name').val()),
                    name_bn: $.trim($('#user_name_bn').val()),
                    email: $.trim($('#user_email').val()),
                    role_id: $('#user_role_id').val(),
                    department_id: $('#user_department_id').val()
                };
                var password = $('#user_password').val();
                if (password !== '') {
                    data.password = password;
                    data.confirm_password = $('#user_confirm_password').val();
                }

                $.ajax({
                    url: editingId ? urls.item.replace(':id', editingId) : urls.store,
                    type: editingId ? 'PUT' : 'POST',
                    dataType: 'json',
                    data: data,
                    success: function() {
                        $formModal.modal('hide');
                        notifyAfterReload(editingId ? text.updated : text.created);
                    },
                    error: function(xhr) {
                        setFormBusy(false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var firstMessage = null;
                            $.each(xhr.responseJSON.errors, function(inputName, messages) {
                                firstMessage = firstMessage || messages[0];
                                fieldError($form.find('[name="' + inputName + '"]'), messages[0]);
                            });
                            $form.find('.is-invalid').first().trigger('focus');
                            if (firstMessage) {
                                toast('error', firstMessage);
                            }
                        } else {
                            $formError.removeClass('d-none').text(
                                (xhr.responseJSON && xhr.responseJSON.error) || text.genericError);
                        }
                    }
                });
            }

            $form.on('submit', function(event) {
                event.preventDefault();
                if ($submit.prop('disabled')) {
                    return;
                }
                clearFormErrors();

                // Client-side checks (the server validates everything again).
                $.each(['#user_name', '#user_email', '#user_role_id'], function(_, selector) {
                    if ($.trim($(selector).val() || '') === '') {
                        fieldError($(selector), text.required);
                    }
                });
                if (String($('#user_role_id').val()) === DEPARTMENT_REQUIRED_ROLE && !$('#user_department_id').val()) {
                    fieldError($('#user_department_id'), text.departmentRequired);
                }
                var password = $('#user_password').val();
                var confirmPassword = $('#user_confirm_password').val();
                if (!editingId && password === '') {
                    fieldError($('#user_password'), text.required);
                }
                if (password !== '' && password.length < PASSWORD_MIN_LENGTH) {
                    fieldError($('#user_password'), text.passwordTooShort);
                }
                if (password !== '' && password !== confirmPassword) {
                    fieldError($('#user_confirm_password'), text.passwordMismatch);
                }
                if ($form.find('.is-invalid').length) {
                    $form.find('.is-invalid').first().trigger('focus');
                    return;
                }

                if (!editingId) {
                    sendForm();
                    return;
                }

                // Editing: ask for confirmation before saving the change.
                Swal.fire({
                    title: text.confirmTitle,
                    text: text.confirmUpdate,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: text.confirmUpdateYes,
                    cancelButtonText: text.confirmNo
                }).then(function(result) {
                    if (result.isConfirmed) {
                        sendForm();
                    }
                });
            });

            // ---- Delete confirmation modal ----
            var $deleteModal = $('#userDeleteModal');
            var $deleteBtn = $('#user_delete_confirm');
            var $deleteIcon = $('#user_delete_icon');
            var $deleteError = $('#user_delete_error');
            var deletingId = null;

            function setDeleteBusy(busy) {
                $deleteBtn.prop('disabled', busy);
                $deleteIcon.toggleClass('fa-trash-alt', !busy).toggleClass('fa-spinner fa-spin', busy);
            }

            $(document).on('click', '.delete-user-btn', function() {
                deletingId = $(this).data('id');
                $('#user_delete_name').text(String($(this).attr('data-label') || ''));
                $('#user_delete_email').text(String($(this).attr('data-email') || ''));
                $deleteError.addClass('d-none').text('');
                setDeleteBusy(false);
                $deleteModal.modal('show');
            });

            // Focus the safe choice first, so Enter does not delete by accident.
            $deleteModal.on('shown.bs.modal', function() {
                $('#user_delete_cancel').trigger('focus');
            });

            $deleteBtn.on('click', function() {
                if (!deletingId || $deleteBtn.prop('disabled')) {
                    return;
                }
                $deleteError.addClass('d-none').text('');
                setDeleteBusy(true);
                $.ajax({
                    url: urls.item.replace(':id', deletingId),
                    type: 'DELETE',
                    dataType: 'json',
                    success: function() {
                        $deleteModal.modal('hide');
                        notifyAfterReload(text.deleted);
                    },
                    error: function(xhr) {
                        setDeleteBusy(false);
                        $deleteError.removeClass('d-none').text(
                            (xhr.responseJSON && xhr.responseJSON.error) || text.genericError);
                    }
                });
            });

            // ---- Deep links: ?create=1 (sidebar "add user") and ?edit=ID (old edit URL) ----
            var params = new URLSearchParams(window.location.search);
            if (params.get('create') === '1') {
                openForm(null);
            } else if (params.get('edit')) {
                var $target = $(table.rows().nodes()).find('.edit-user-btn[data-id="' + parseInt(params.get('edit'), 10) + '"]').first();
                if ($target.length) {
                    openForm(userFromButton($target));
                }
            }
            if (params.has('create') || params.has('edit')) {
                window.history.replaceState({}, '', urls.index);
            }
        });
    </script>
@endpush
