<div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ __('user.table_header') }}</h3>
    </div>
</div>
<div class="card-body">
    <div class="row">
        <div class="table-responsive">
            <table class="table" id="users_table">
                @if ($userListByRole->isEmpty())
                    <p>{{ __('commontable.no_record_found') }}</p>
                @else
                    <thead>
                        <tr>
                            <th>{{ __('user.user_name_en') }}</th>
                            <th>{{ __('user.user_name_bn') }}</th>
                            <th>{{ __('user.email') }}</th>
                            <th>{{ __('commontable.created_at') }}</th>
                            <th>{{ __('commontable.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($userListByRole as $user)
                            <tr>

                                <td>
                                    {{ $user->name }}
                                </td>
                                <td>
                                    {{ $user->name_bn }}
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    {{ app()->getLocale() === 'en' ? $user->created_at->format('d-m-Y') : bangla_date($user->created_at->timestamp, 'en') }}
                                </td>
                                <td>
                                    <!-- Edit Button -->
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary"
                                        title="{{ __('buttons.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Delete Button -->
                                    <form class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger"
                                            title="{{ __('buttons.delete') }}"
                                            onclick="deleteUser('{{ $user->id }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Initialize DataTables on the table
        $('#users_table').DataTable({
            "paging": true,
            "searching": true,
            "bDestroy": true
        });
    });

    // Remove button functionality
    function deleteUser(roleId) {
        // Show SweetAlert confirmation dialog
        Swal.fire({
            title: "{{ __('sweetalert.confirmation') }}",
            text: '',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: "{{ __('sweetalert.cancel') }}",
            confirmButtonText: "{{ __('sweetalert.delete_notice') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user confirms, send an AJAX request to delete the record
                $('#wait').show(); // Show loading spinner
                $.ajax({
                    url: "{{ route('cnfRoles.destroy', ['cnfRole' => ':roleId']) }}"
                        .replace(':roleId', roleId),
                    type: 'DELETE',
                    success: function(response) {
                        // If successful, remove the table row
                        $('#row_' + roleId).remove();
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: "{{ __('sweetalert.success') }}",
                            text: "{{ __('sweetalert.deleted') }}",
                        }).then((result) => {
                            $('#wait').show();
                            window.location.href =
                                "{{ route('cnfRoles.index') }}";
                        });
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: "{{ __('sweetalert.error') }}",
                            text: "{{ __('sweetalert.error_title') }}",
                        });
                    },
                    complete: function() {
                        $('#wait').hide(); // Hide loading spinner
                    }
                });
            }
        });
    }
</script>
