@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
@endphp

<div class="card-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0 mr-2">{{ __('rolemanagement.table_header') }}</h3>
        <button type="button" class="btn btn-sm btn-success role-touch-btn" id="open_create_role"
            title="{{ __('rolemanagement.create_new_role') }}" aria-label="{{ __('rolemanagement.create_new_role') }}">
            <i class="fas fa-plus" aria-hidden="true"></i>
            <span class="d-none d-sm-inline ml-1">{{ __('rolemanagement.create_new_role') }}</span>
        </button>
    </div>
</div>
<div class="card-body">
    {{-- Toolbar: page size and search share one row --}}
    <div class="form-row align-items-end mb-3">
        <div class="col-5 col-md-3 col-lg-2">
            <label for="role_page_length" class="mb-1">{{ __('commontable.per_page') }}</label>
            <select id="role_page_length" class="custom-select">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}">{{ $isEn ? $size : strtr((string) $size, $bnDigits) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-9 col-lg-10 mt-2 mt-md-0">
            <label for="role_search" class="mb-1">{{ __('commontable.search') }}</label>
            <input type="search" id="role_search" class="form-control" autocomplete="off"
                placeholder="{{ __('rolemanagement.search_placeholder') }}"
                title="{{ __('rolemanagement.search_placeholder') }}">
        </div>
    </div>

    <div class="row">
        <div class="table-responsive col-12">
            <table class="table" id="roles_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center role-serial-col">{{ __('commontable.serial') }}</th>
                        <th class="text-center">{{ __('rolemanagement.role_name') }}</th>
                        <th class="text-center d-none d-md-table-cell">{{ __('commontable.created_by') }}</th>
                        <th class="text-center d-none d-md-table-cell">{{ __('commontable.created_at') }}</th>
                        <th class="text-center" style="width: 110px;">{{ __('commontable.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listOfRoles as $role)
                        @php
                            // Shown as: English name (Bangla name), e.g. Admin (অ্যাডমিন)
                            $roleLabel = $role->role_name_en;
                            if ($role->role_name_bn && $role->role_name_bn !== $role->role_name_en) {
                                $roleLabel = trim($role->role_name_en . ' (' . $role->role_name_bn . ')');
                            }
                            $createdDate = $createdTime = null;
                            if ($role->created_at) {
                                $createdDate = $isEn
                                    ? $role->created_at->format('d M, Y')
                                    : bangla_date($role->created_at->timestamp, 'en');
                                $createdTime = $isEn
                                    ? $role->created_at->format('h:i A')
                                    : strtr($role->created_at->format('h:i'), $bnDigits) . ' ' . __('rolemanagement.' . $role->created_at->format('a'));
                            }
                        @endphp
                        <tr id="row_{{ $role->id }}">
                            {{-- Serial number: refreshed by the table script after sorting, search and paging --}}
                            <td class="text-center role-serial-col">{{ $isEn ? $loop->iteration : strtr((string) $loop->iteration, $bnDigits) }}</td>
                            <td class="text-left role-name-cell" data-order="{{ $role->role_name_en }}">
                                <span class="font-weight-bold">{{ $roleLabel }}</span>
                            </td>
                            <td class="text-center d-none d-md-table-cell">{{ $role->creator->name ?? '—' }}</td>
                            <td class="text-center d-none d-md-table-cell"
                                data-order="{{ ($role->created_at?->timestamp ?? 0) * 100000 + $role->id }}">
                                @if ($createdDate)
                                    <span class="text-nowrap">{{ $createdDate }}<i class="fas fa-clock text-muted ml-2 mr-1" aria-hidden="true"></i>{{ $createdTime }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-primary role-touch-btn edit-role-btn"
                                    data-id="{{ $role->id }}" data-name-en="{{ $role->role_name_en }}"
                                    data-name-bn="{{ $role->role_name_bn }}"
                                    title="{{ __('buttons.edit') }}"
                                    aria-label="{{ __('buttons.edit') }}: {{ $roleLabel }}">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger role-touch-btn delete-role-btn"
                                    data-id="{{ $role->id }}" data-label="{{ $roleLabel }}"
                                    title="{{ __('buttons.delete') }}"
                                    aria-label="{{ __('buttons.delete') }}: {{ $roleLabel }}">
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

{{-- Create / edit role modal --}}
<div class="modal fade" id="roleFormModal" tabindex="-1" role="dialog" aria-labelledby="roleFormModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form id="role_form" method="POST" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="roleFormModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('buttons.cancel') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="role_form_error" role="alert"></div>
                    <div class="form-group">
                        <label for="modal_role_name_en">{{ __('rolemanagement.role_name_en') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="role_name_en" id="modal_role_name_en" class="form-control"
                            maxlength="255" required autocomplete="off">
                        <span class="invalid-feedback" role="alert"></span>
                    </div>
                    <div class="form-group mb-0">
                        <label for="modal_role_name_bn">{{ __('rolemanagement.role_name_bn') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="role_name_bn" id="modal_role_name_bn" class="form-control"
                            maxlength="255" required autocomplete="off" lang="bn">
                        <span class="invalid-feedback" role="alert"></span>
                    </div>
                </div>
                <div class="modal-footer role-modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ __('buttons.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="role_form_submit">
                        <i class="fas fa-save" id="role_form_icon" aria-hidden="true"></i>
                        <span id="role_form_submit_text"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="modal fade" id="roleDeleteModal" tabindex="-1" role="dialog" aria-labelledby="roleDeleteModalLabel"
    aria-describedby="roleDeleteModalText" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger mr-1" aria-hidden="true"></i>
                    {{ __('rolemanagement.delete_role') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('buttons.cancel') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="role_delete_error" role="alert"></div>
                <p class="mb-1" id="roleDeleteModalText">{{ __('rolemanagement.delete_confirm_text') }}</p>
                <p class="mb-0 font-weight-bold role-name-cell" id="role_delete_name"></p>
            </div>
            <div class="modal-footer role-modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="role_delete_cancel">
                    {{ __('sweetalert.cancel') }}
                </button>
                <button type="button" class="btn btn-danger" id="role_delete_confirm">
                    <i class="fas fa-trash-alt" id="role_delete_icon" aria-hidden="true"></i>
                    {{ __('sweetalert.delete_notice') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('page_css')
    <style>
        /* Role list — same look as the user list */
        .role-name-cell {
            overflow-wrap: break-word;
        }

        /* Compact rows */
        #roles_table td {
            vertical-align: middle;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
            line-height: 1.35;
        }

        #roles_table thead th {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        #roles_table .role-serial-col {
            width: 64px;
            white-space: nowrap;
        }

        .role-touch-btn {
            min-width: 34px;
            min-height: 34px;
        }

        #roles_table .role-touch-btn {
            padding: 0.2rem 0.45rem;
        }

        /* Table footer (entry count + pagination) sits on its own tinted band */
        #roles_table_wrapper > .row:last-child {
            margin: 0.75rem 0 0;
            padding: 0.6rem 0.75rem;
            align-items: center;
            background: #e9eefb;
            border: 1px solid #cfd9f3;
            border-radius: 0.5rem;
        }

        #roles_table_wrapper > .row:last-child .dt-info {
            padding-top: 0;
            color: #1f2d5a;
            font-weight: 600;
        }

        #roles_table_wrapper > .row:last-child .pagination {
            margin-bottom: 0;
        }

        @media (max-width: 767.98px) {
            #roles_table_wrapper .dt-info,
            #roles_table_wrapper .dt-paging {
                text-align: center;
                float: none;
                margin-top: 0.25rem;
            }

            #roles_table_wrapper .dt-paging .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 575.98px) {
            #roles_table th,
            #roles_table td {
                padding-left: 0.3rem;
                padding-right: 0.3rem;
            }

            .role-name-cell {
                overflow-wrap: anywhere;
            }

            #roles_table .role-serial-col {
                width: 40px;
                padding-left: 2px;
                padding-right: 2px;
            }

            #roles_table th:last-child {
                width: 88px !important;
            }

            #roles_table thead th:not(:last-child) {
                padding-right: 22px;
                padding-left: 6px;
            }

            /* Finger-sized buttons and fields on phones */
            .role-touch-btn {
                min-width: 38px;
                min-height: 38px;
            }

            #roles_table_wrapper .pagination .page-link {
                padding: 0.4rem 0.55rem;
            }

            /* Full-width, stacked buttons in modals on phones */
            .role-modal-footer {
                flex-direction: column-reverse;
            }

            .role-modal-footer>.btn {
                width: 100%;
                margin: 0.25rem 0;
                min-height: 44px;
            }

            #roleFormModal .form-control,
            #role_page_length,
            #role_search {
                min-height: 44px;
                font-size: 16px; /* stops iOS zooming into the field */
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(function() {
            var TOAST_KEY = 'role_toast';
            var urls = {
                index: "{{ route('cnfRoles.index') }}",
                store: "{{ route('cnfRoles.store') }}",
                item: "{{ route('cnfRoles.update', ['cnfRole' => ':id']) }}"
            };
            var text = {
                createTitle: @json(__('rolemanagement.create_new_role')),
                editTitle: @json(__('rolemanagement.edit_role')),
                save: @json(__('buttons.save')),
                update: @json(__('buttons.update')),
                required: @json(__('sweetalert.required')),
                genericError: @json(__('sweetalert.error_title')),
                confirmTitle: @json(__('sweetalert.confirmation')),
                confirmUpdate: @json(__('sweetalert.about_to_update')),
                confirmUpdateYes: @json(__('sweetalert.update_notice')),
                confirmNo: @json(__('sweetalert.cancel')),
                created: @json(__('rolemanagement.toast_created')),
                updated: @json(__('rolemanagement.toast_updated')),
                deleted: @json(__('rolemanagement.toast_deleted'))
            };

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
                    (window.appToast || toastr).success(pending);
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

            var table = $('#roles_table').DataTable({
                paging: true,
                pageLength: 10,
                searching: true,
                bDestroy: true,
                autoWidth: false,
                // Newest role first (column 3 = created time).
                order: [
                    [3, 'desc']
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

            $('#role_page_length').on('change', function() {
                table.page.len(parseInt($(this).val(), 10) || 10).draw();
            });

            // Searches the role name (English and Bangla) and who created it.
            $('#role_search').on('input search', function() {
                table.search($(this).val()).draw();
            });

            // ---- Create / edit modal ----
            var $formModal = $('#roleFormModal');
            var $form = $('#role_form');
            var $submit = $('#role_form_submit');
            var $icon = $('#role_form_icon');
            var $formError = $('#role_form_error');
            var editingId = null;

            function clearFormErrors() {
                $formError.addClass('d-none').text('');
                $form.find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $form.find('.invalid-feedback').text('');
            }

            function setFormBusy(busy) {
                $submit.prop('disabled', busy);
                $icon.toggleClass('fa-save', !busy).toggleClass('fa-spinner fa-spin', busy);
            }

            function openForm(role) {
                editingId = role ? role.id : null;
                $form[0].reset();
                clearFormErrors();
                setFormBusy(false);
                $('#roleFormModalLabel').text(role ? text.editTitle : text.createTitle);
                $('#role_form_submit_text').text(role ? text.update : text.save);
                $('#modal_role_name_en').val(role ? role.nameEn : '');
                $('#modal_role_name_bn').val(role ? role.nameBn : '');
                $formModal.modal('show');
            }

            $('#open_create_role').on('click', function() {
                openForm(null);
            });

            $(document).on('click', '.edit-role-btn', function() {
                var $btn = $(this);
                openForm({
                    id: $btn.data('id'),
                    nameEn: String($btn.attr('data-name-en') || ''),
                    nameBn: String($btn.attr('data-name-bn') || '')
                });
            });

            $formModal.on('shown.bs.modal', function() {
                $('#modal_role_name_en').trigger('focus');
            });

            $form.on('input', '.form-control', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid')
                    .siblings('.invalid-feedback').text('');
            });

            function sendForm() {
                setFormBusy(true);
                $.ajax({
                    url: editingId ? urls.item.replace(':id', editingId) : urls.store,
                    type: editingId ? 'PATCH' : 'POST',
                    dataType: 'json',
                    data: {
                        role_name_en: $.trim($('#modal_role_name_en').val()),
                        role_name_bn: $.trim($('#modal_role_name_bn').val())
                    },
                    success: function() {
                        $formModal.modal('hide');
                        notifyAfterReload(editingId ? text.updated : text.created);
                    },
                    error: function(xhr) {
                        setFormBusy(false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(inputName, messages) {
                                $form.find('input[name="' + inputName + '"]')
                                    .addClass('is-invalid').attr('aria-invalid', 'true')
                                    .siblings('.invalid-feedback').text(messages[0]);
                            });
                            $form.find('.is-invalid').first().trigger('focus');
                        } else {
                            $formError.removeClass('d-none').text(text.genericError);
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

                var hasEmpty = false;
                $form.find('input[type="text"]').each(function() {
                    if ($.trim($(this).val()) === '') {
                        hasEmpty = true;
                        $(this).addClass('is-invalid').attr('aria-invalid', 'true')
                            .siblings('.invalid-feedback').text(text.required);
                    }
                });
                if (hasEmpty) {
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
            var $deleteModal = $('#roleDeleteModal');
            var $deleteBtn = $('#role_delete_confirm');
            var $deleteIcon = $('#role_delete_icon');
            var $deleteError = $('#role_delete_error');
            var deletingId = null;

            function setDeleteBusy(busy) {
                $deleteBtn.prop('disabled', busy);
                $deleteIcon.toggleClass('fa-trash-alt', !busy).toggleClass('fa-spinner fa-spin', busy);
            }

            $(document).on('click', '.delete-role-btn', function() {
                deletingId = $(this).data('id');
                $('#role_delete_name').text(String($(this).attr('data-label') || ''));
                $deleteError.addClass('d-none').text('');
                setDeleteBusy(false);
                $deleteModal.modal('show');
            });

            // Focus the safe choice first, so Enter does not delete by accident.
            $deleteModal.on('shown.bs.modal', function() {
                $('#role_delete_cancel').trigger('focus');
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
                        var message = (xhr.responseJSON && xhr.responseJSON.error && xhr.status === 422) ?
                            xhr.responseJSON.error :
                            text.genericError;
                        $deleteError.removeClass('d-none').text(message);
                    }
                });
            });
        });
    </script>
@endpush
