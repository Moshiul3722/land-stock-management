@php
    $menuItems = $menuItems->original;
@endphp
<div class="card-header">
    <h3 class="card-title">{{ __('rolemanagement.role_wise_permission') }}</h3>
</div>
<div class="card-body">
    <div class="row">
        <div class="table-responsive col-12">
            @if (collect($menuItems)->isEmpty())
                <p>{{ __('rolemanagement.no_record_found') }}</p>
            @else
                <form id="permission_form" name="permission_form">
                    <input type="hidden" id="role_id" name="role_id" value="{{ $roleId }}">
                    <table class="table table-bordered" id="permission_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>{{ __('rolemanagement.module') }}</th>
                                <th>{{ __('rolemanagement.submodule') }}</th>
                                <th>{{ __('rolemanagement.menu') }}</th>
                                <th>{{ __('rolemanagement.permission') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($menuItems as $module => $submodules)
                                @foreach ($submodules as $submodule => $menus)
                                    @foreach ($menus as $key => $menuItem)
                                        <tr>
                                            @if ($key === 0)
                                                <td rowspan="{{ count($menus) }}">{{ $module }}</td>
                                                <td rowspan="{{ count($menus) }}">{{ $submodule }}</td>
                                            @endif
                                            <td>{{ $menuItem['menu_name'] }}</td>
                                            <td class="permission-cell">
                                                <input type="hidden" class="menu-id"
                                                    value="{{ $menuItem['menu_id'] }}">
                                                <label class="permission-label">
                                                    <input type="checkbox" class="permission-checkbox"
                                                        value="{{ $menuItem['menu_id'] }}"
                                                        aria-label="{{ __('rolemanagement.permission') }}: {{ $menuItem['menu_name'] }}"
                                                        {{ $menuItem['has_permission'] == 1 ? 'checked' : '' }}>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-right">
                        <button type="button" class="btn btn-primary" id="save_permissions"><i class="fas fa-save"></i>
                            {{ __('buttons.save') }}</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#save_permissions').click(function() {
            var roleId = $('#role_id').val();
            // var formData = $('#permissions_form').serialize();
            var permissionsArray = [];
            $('.permission-checkbox').each(function() {
                var menuId = $(this).closest('tr').find('.menu-id')
                    .val(); // Assuming each row has a hidden input with class "menu-id" containing the menu_id
                var permissionId = $(this).prop('checked') ? 1 : 0;
                permissionsArray.push({
                    menu_id: menuId,
                    permission_id: permissionId,
                    role_id: roleId
                });
            });
            Swal.fire({
                title: "{{ __('sweetalert.confirmation') }}",
                text: "{{ __('sweetalert.about_to_save') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: "{{ __('sweetalert.cancel') }}",
                confirmButtonText: "{{ __('sweetalert.save_notice') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('savepermission') }}",
                        type: 'POST',
                        data: {
                            permissions: permissionsArray
                        },
                        beforeSend: function() {
                            $('#wait').show();
                        },
                        success: function(data) {
                            $('#wait').hide();
                            Swal.fire({
                                icon: 'success',
                                title: "{{ __('sweetalert.success') }}",
                                text: "{{ __('sweetalert.save_complete') }}",
                            }).then((result) => {
                                $('#wait').show();
                                window.location.href =
                                    "{{ route('cnfPermissions.index') }}";
                            });
                        },
                        error: function(xhr, status, error) {
                            $('#wait').hide();
                            Swal.fire({
                                icon: 'error',
                                title: "{{ __('sweetalert.error') }}",
                                text: "{{ __('sweetalert.error_title') }}",
                            });
                        },
                        complete: function() {
                            $('#wait').hide();
                        }
                    });
                }
            });
        });
    });
</script>
