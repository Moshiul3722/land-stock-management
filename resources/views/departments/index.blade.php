@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
@endphp

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('department.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ __('department.title') }}
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
                    <h3 class="card-title mb-0 mr-2">{{ __('department.table_header') }}</h3>
                    <button type="button" class="btn btn-sm btn-success dept-touch-btn" id="open_create_dept"
                        title="{{ __('department.create_new') }}" aria-label="{{ __('department.create_new') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ml-1">{{ __('department.create_new') }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body">
                {{-- Toolbar: page size and search share one row --}}
                <div class="form-row align-items-end mb-3">
                    <div class="col-5 col-md-3 col-lg-2">
                        <label for="dept_page_length" class="mb-1">{{ __('commontable.per_page') }}</label>
                        <select id="dept_page_length" class="custom-select">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-9 col-lg-10 mt-2 mt-md-0">
                        <label for="dept_search" class="mb-1">{{ __('commontable.search') }}</label>
                        <input type="search" id="dept_search" class="form-control" autocomplete="off"
                            placeholder="{{ __('department.search_placeholder') }}"
                            title="{{ __('department.search_placeholder') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="table-responsive col-12">
                        <table class="table" id="departments_table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center dept-serial-col">{{ __('commontable.serial') }}</th>
                                    <th class="text-center">{{ __('department.name') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ __('department.code') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ __('department.users') }}</th>
                                    <th class="text-center d-none d-lg-table-cell">{{ __('commontable.created_at') }}</th>
                                    <th class="text-center" style="width: 110px;">{{ __('commontable.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($departments as $department)
                                    @php
                                        // Shown as: English name (Bangla name)
                                        $label = $department->name;
                                        if ($department->name_bn && $department->name_bn !== $department->name) {
                                            $label = trim($department->name . ' (' . $department->name_bn . ')');
                                        }
                                        $createdDate = $createdTime = null;
                                        if ($department->created_at) {
                                            $createdDate = $isEn
                                                ? $department->created_at->format('d M, Y')
                                                : bangla_date($department->created_at->timestamp, 'en');
                                            $createdTime = $isEn
                                                ? $department->created_at->format('h:i A')
                                                : $digits($department->created_at->format('h:i')) . ' ' . __('department.' . $department->created_at->format('a'));
                                        }
                                    @endphp
                                    <tr id="dept_row_{{ $department->id }}">
                                        {{-- Serial number: refreshed by the table script after sorting, search and paging --}}
                                        <td class="text-center dept-serial-col">{{ $digits($loop->iteration) }}</td>
                                        <td class="text-left dept-wrap-cell" data-order="{{ $department->name }}">
                                            <span class="font-weight-bold">{{ $label }}</span>
                                            @if ($department->code)
                                                <small class="d-block d-md-none text-muted">{{ __('department.code') }}: {{ $department->code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center dept-wrap-cell d-none d-md-table-cell">{{ $department->code ?: '—' }}</td>
                                        <td class="text-center d-none d-md-table-cell"
                                            data-order="{{ $department->active_users_count }}">{{ $digits($department->active_users_count) }}</td>
                                        <td class="text-center d-none d-lg-table-cell"
                                            data-order="{{ ($department->created_at?->timestamp ?? 0) * 100000 + $department->id }}">
                                            @if ($createdDate)
                                                <span class="text-nowrap">{{ $createdDate }}<i class="fas fa-clock text-muted ml-2 mr-1" aria-hidden="true"></i>{{ $createdTime }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-sm btn-primary dept-touch-btn edit-dept-btn"
                                                data-id="{{ $department->id }}" data-name="{{ $department->name }}"
                                                data-name-bn="{{ $department->name_bn }}" data-code="{{ $department->code }}"
                                                data-tip="{{ __('buttons.edit') }}"
                                                aria-label="{{ __('buttons.edit') }}: {{ $label }}">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger dept-touch-btn delete-dept-btn"
                                                data-id="{{ $department->id }}" data-label="{{ $label }}"
                                                data-tip="{{ __('buttons.delete') }}"
                                                aria-label="{{ __('buttons.delete') }}: {{ $label }}">
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

    {{-- Create / edit modal --}}
    <div class="modal fade" id="deptFormModal" tabindex="-1" role="dialog" aria-labelledby="deptFormModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form id="dept_form" method="POST" novalidate autocomplete="off">
                    @csrf
                    <div class="modal-header">
                        <div class="modal-heading">
                            <h5 class="modal-title" id="deptFormModalLabel"></h5>
                            <p class="modal-hint">{{ __('department.form_hint') }}</p>
                        </div>
                        <x-ui.modal-today variant="bs" />
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('buttons.cancel') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="dept_form_error" role="alert"></div>
                        <div class="form-group">
                            <label for="dept_name">{{ __('department.name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="dept_name" class="form-control" maxlength="191" required>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group">
                            <label for="dept_name_bn">{{ __('department.name_bn') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name_bn" id="dept_name_bn" class="form-control" maxlength="191"
                                required lang="bn">
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group mb-0">
                            <label for="dept_code">{{ __('department.code') }}</label>
                            <input type="text" name="code" id="dept_code" class="form-control" maxlength="191"
                                aria-describedby="dept_code_hint">
                            <span class="invalid-feedback" role="alert"></span>
                            <small class="form-text text-muted" id="dept_code_hint">{{ __('department.code_hint') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer dept-modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            {{ __('buttons.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" id="dept_form_submit">
                            <i class="fas fa-save" id="dept_form_icon" aria-hidden="true"></i>
                            <span id="dept_form_submit_text"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete confirmation: same layout as the lot / product delete dialog --}}
    <div class="modal fade" id="deptDeleteModal" tabindex="-1" role="dialog" aria-labelledby="deptDeleteModalLabel"
        aria-describedby="deptDeleteModalText" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-confirm">
                    <div class="alert alert-danger d-none" id="dept_delete_error" role="alert"></div>
                    <div class="modal-confirm-icon" aria-hidden="true"><i class="fas fa-trash-alt"></i></div>
                    <h5 class="modal-title" id="deptDeleteModalLabel">{{ __('department.delete') }}</h5>
                    <p class="modal-confirm-text" id="deptDeleteModalText">{{ __('department.delete_confirm_text') }}</p>
                    <p class="modal-confirm-name" id="dept_delete_name"></p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="dept_delete_cancel">
                        {{ __('sweetalert.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="dept_delete_confirm">
                        <i class="fas fa-trash-alt" id="dept_delete_icon" aria-hidden="true"></i>
                        {{ __('sweetalert.delete_notice') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_css')
    <style>
        .dept-wrap-cell {
            overflow-wrap: break-word;
        }

        /* Compact rows */
        #departments_table td {
            vertical-align: middle;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
            line-height: 1.35;
        }

        #departments_table thead th {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        #departments_table small {
            font-size: 0.875rem;
        }

        #departments_table .dept-serial-col {
            width: 64px;
            white-space: nowrap;
        }

        .dept-touch-btn {
            min-width: 34px;
            min-height: 34px;
        }

        #departments_table .dept-touch-btn {
            padding: 0.2rem 0.45rem;
        }

        /* Table footer (entry count + pagination) sits on its own tinted band */
        #departments_table_wrapper > .row:last-child {
            margin: 0.75rem 0 0;
            padding: 0.6rem 0.75rem;
            align-items: center;
            background: #e9eefb;
            border: 1px solid #cfd9f3;
            border-radius: 0.5rem;
        }

        #departments_table_wrapper > .row:last-child .dt-info {
            padding-top: 0;
            color: #1f2d5a;
            font-weight: 600;
        }

        #departments_table_wrapper > .row:last-child .pagination {
            margin-bottom: 0;
        }

        @media (max-width: 767.98px) {
            #departments_table_wrapper .dt-info,
            #departments_table_wrapper .dt-paging {
                text-align: center;
                float: none;
                margin-top: 0.25rem;
            }

            #departments_table_wrapper .dt-paging .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 575.98px) {
            #departments_table th,
            #departments_table td {
                padding-left: 0.3rem;
                padding-right: 0.3rem;
            }

            .dept-wrap-cell {
                overflow-wrap: anywhere;
            }

            #departments_table .dept-serial-col {
                width: 40px;
                padding-left: 2px;
                padding-right: 2px;
            }

            #departments_table th:last-child {
                width: 88px !important;
            }

            #departments_table thead th:not(:last-child) {
                padding-right: 22px;
                padding-left: 6px;
            }

            /* Finger-sized buttons and fields on phones */
            .dept-touch-btn {
                min-width: 38px;
                min-height: 38px;
            }

            #departments_table_wrapper .pagination .page-link {
                padding: 0.4rem 0.55rem;
            }

            .dept-modal-footer {
                flex-direction: column-reverse;
            }

            .dept-modal-footer>.btn {
                width: 100%;
                margin: 0.25rem 0;
                min-height: 44px;
            }

            #deptFormModal .form-control,
            #dept_page_length,
            #dept_search {
                min-height: 44px;
                font-size: 16px; /* stops iOS zooming into the field */
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(function() {
            var TOAST_KEY = 'department_toast';
            var urls = {
                index: "{{ route('departments.index') }}",
                store: "{{ route('departments.store') }}",
                item: "{{ route('departments.update', ['department' => ':id']) }}"
            };
            var text = {
                createTitle: @json(__('department.create_new')),
                editTitle: @json(__('department.edit')),
                save: @json(__('buttons.save')),
                update: @json(__('buttons.update')),
                required: @json(__('sweetalert.required')),
                genericError: @json(__('sweetalert.error_title')),
                confirmTitle: @json(__('sweetalert.confirmation')),
                confirmUpdate: @json(__('sweetalert.about_to_update')),
                confirmUpdateYes: @json(__('sweetalert.update_notice')),
                confirmNo: @json(__('sweetalert.cancel')),
                created: @json(__('department.toast_created')),
                updated: @json(__('department.toast_updated')),
                deleted: @json(__('department.toast_deleted'))
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

            var table = $('#departments_table').DataTable({
                paging: true,
                pageLength: 10,
                searching: true,
                bDestroy: true,
                autoWidth: false,
                // Newest first (column 4 = created time).
                order: [
                    [4, 'desc']
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

            // Serial numbers follow the current sort, search and page.
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

            $('#dept_page_length').on('change', function() {
                table.page.len(parseInt($(this).val(), 10) || 10).draw();
            });

            // Searches English name, Bangla name and code.
            $('#dept_search').on('input search', function() {
                table.search($(this).val()).draw();
            });

            // ---- Create / edit modal ----
            var $formModal = $('#deptFormModal');
            var $form = $('#dept_form');
            var $submit = $('#dept_form_submit');
            var $icon = $('#dept_form_icon');
            var $formError = $('#dept_form_error');
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

            function openForm(department) {
                editingId = department ? department.id : null;
                $form[0].reset();
                clearFormErrors();
                setFormBusy(false);
                $('#deptFormModalLabel').text(department ? text.editTitle : text.createTitle);
                $('#dept_form_submit_text').text(department ? text.update : text.save);
                $('#dept_name').val(department ? department.name : '');
                $('#dept_name_bn').val(department ? department.nameBn : '');
                $('#dept_code').val(department ? department.code : '');
                $formModal.modal('show');
            }

            $('#open_create_dept').on('click', function() {
                openForm(null);
            });

            $(document).on('click', '.edit-dept-btn', function() {
                var $btn = $(this);
                openForm({
                    id: $btn.data('id'),
                    name: String($btn.attr('data-name') || ''),
                    nameBn: String($btn.attr('data-name-bn') || ''),
                    code: String($btn.attr('data-code') || '')
                });
            });

            $formModal.on('shown.bs.modal', function() {
                $('#dept_name').trigger('focus');
            });

            $form.on('input', '.form-control', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid')
                    .siblings('.invalid-feedback').text('');
            });

            function sendForm() {
                setFormBusy(true);
                $.ajax({
                    url: editingId ? urls.item.replace(':id', editingId) : urls.store,
                    type: editingId ? 'PUT' : 'POST',
                    dataType: 'json',
                    data: {
                        name: $.trim($('#dept_name').val()),
                        name_bn: $.trim($('#dept_name_bn').val()),
                        code: $.trim($('#dept_code').val())
                    },
                    success: function() {
                        $formModal.modal('hide');
                        notifyAfterReload(editingId ? text.updated : text.created);
                    },
                    error: function(xhr) {
                        setFormBusy(false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(inputName, messages) {
                                fieldError($form.find('[name="' + inputName + '"]'), messages[0]);
                            });
                            $form.find('.is-invalid').first().trigger('focus');
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

                $.each(['#dept_name', '#dept_name_bn'], function(_, selector) {
                    if ($.trim($(selector).val()) === '') {
                        fieldError($(selector), text.required);
                    }
                });
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
            var $deleteModal = $('#deptDeleteModal');
            var $deleteBtn = $('#dept_delete_confirm');
            var $deleteIcon = $('#dept_delete_icon');
            var $deleteError = $('#dept_delete_error');
            var deletingId = null;

            function setDeleteBusy(busy) {
                $deleteBtn.prop('disabled', busy);
                $deleteIcon.toggleClass('fa-trash-alt', !busy).toggleClass('fa-spinner fa-spin', busy);
            }

            $(document).on('click', '.delete-dept-btn', function() {
                deletingId = $(this).data('id');
                $('#dept_delete_name').text(String($(this).attr('data-label') || ''));
                $deleteError.addClass('d-none').text('');
                setDeleteBusy(false);
                $deleteModal.modal('show');
            });

            // Focus the safe choice first, so Enter does not delete by accident.
            $deleteModal.on('shown.bs.modal', function() {
                $('#dept_delete_cancel').trigger('focus');
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
        });
    </script>
@endpush
