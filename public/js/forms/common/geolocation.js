$(document).ready(function () {
    // Getting the list of Divisions
    $.ajax({
        url: "{{ route('geolocation.divisions.getall') }}",
        type: 'POST',
        dataType: 'json',
        beforeSend: function () {
            $('#wait').show();
        },
        success: function (data) {
            var dropdown = $('#division');
            dropdown.empty();
            dropdown.append($('<option></option>').attr('value', '0').text(
                "--{{ __('dropdown.default_select') }}--"));
            $.each(data, function (key, value) {
                dropdown.append($('<option></option>').attr('value', value.id).text(
                    selectedLocale === 'en' ? value.name : value.name_bn));
            });
        },
        error: function (err) {
            $('#wait').hide();
            // Display Sweet Alert for error
            Swal.fire({
                icon: 'error',
                title: "{{ __('sweetalert.error') }}",
                text: "{{ __('sweetalert.error_title') }}",
                showCloseButton: true,
            });
        },
        complete: function () {
            $('#wait').hide();
        }
    });

    // Load Districts based on Division
    $('select[name="division"]').on('change', function () {
        var divisionId = $('#division').val();
        if (divisionId != 0) {
            $.ajax({
                url: "{{ route('geolocation.districts.getbydivisionid') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    divisionId: divisionId,
                },
                beforeSend: function () {
                    $('#wait').show();
                },
                success: function (data) {
                    var dropdown = $('#district');
                    dropdown.empty();
                    dropdown.append($('<option></option>').attr('value', '0').text(
                        "{{ __('dropdown.default_select') }}"));
                    $.each(data, function (key, value) {
                        dropdown.append($('<option></option>').attr('value', value.id).text(
                            selectedLocale === 'en' ? value.name : value.name_bn));
                    });
                },
                error: function (err) {
                    $('#wait').hide();
                    // Display Sweet Alert for error
                    Swal.fire({
                        icon: 'error',
                        title: "{{ __('sweetalert.error') }}",
                        text: "{{ __('sweetalert.error_title') }}",
                        showCloseButton: true,
                    });
                },
                complete: function () {
                    $('#wait').hide();
                }
            });
        }
    });

    // Load Upazilas based on District
    $('select[name="district"]').on('change', function () {
        var districtId = $('#district').val();
        if (districtId != 0) {
            $.ajax({
                url: "{{ route('geolocation.upazilas.getbydistrictid') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    districtId: districtId,
                },
                beforeSend: function () {
                    $('#wait').show();
                },
                success: function (data) {
                    var dropdown = $('#upazila');
                    dropdown.empty();
                    dropdown.append($('<option></option>').attr('value', '0').text(
                        "{{ __('dropdown.default_select') }}"));
                    $.each(data, function (key, value) {
                        dropdown.append($('<option></option>').attr('value', value.id).text(
                            selectedLocale === 'en' ? value.name : value.name_bn));
                    });
                },
                error: function (err) {
                    $('#wait').hide();
                    // Display Sweet Alert for error
                    Swal.fire({
                        icon: 'error',
                        title: "{{ __('sweetalert.error') }}",
                        text: "{{ __('sweetalert.error_title') }}",
                        showCloseButton: true,
                    });
                },
                complete: function () {
                    $('#wait').hide();
                }
            });
        }
    });
    // Clear Selected Districts and Upazilas on Division Change
    $('select[name="division"]').on('change', function () {
        var upazila = $('#upazila');
        upazila.empty();
        var district = $('#district');
        district.empty();
    });
});
