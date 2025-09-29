@extends('publiclayout')

@section('content')
    <style>
        #map {
            margin: 0 auto;
            width: 960px;
            height: 450px;
        }
        .custom-map-control-button {
            background-color: #fff;
            border: 0;
            border-radius: 2px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.3);
            margin: 10px;
            padding: 0 0.5em;
            text-align: center;
            font-size: 16px;
            font-family: Roboto, Arial, sans-serif;
            cursor: pointer;
        }
    </style>
    <section id="about" class="about section">
        <div class="container">
            <img src="{{ asset('theme/assets/img/Master_3.gif') }}" style="width:100%;">
        </div>
    </section>

    <!-- /Why Us Section -->
    <section id="why-us" class="section why-us light-background" data-builder="section">

        <div class="container-fluid">

            <div class="row gy-4">
                <div id="map">

                </div>
            </div>

        </div>

    </section>
@endsection
@push('js')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDz4YvJI8S9OrmSTayUxcZJSwOuoOPY0SY" async defer></script>
    <script>
        // Encode the Laravel collection to JSON and assign it to a JavaScript variable
        var locations = @json($locations);

        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 7,
                center: new google.maps.LatLng(24.0964352, 90.2050421),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            var infowindow = new google.maps.InfoWindow();

            var marker, i;
            var customIcon = '/img/final.gif';

            for (i = 0; i < locations.length; i++) {
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                    map: map,
                    icon: customIcon
                });

                google.maps.event.addListener(marker, 'click', (function(marker, i) {
                    return function() {
                        infowindow.setContent(locations[i][0]);
                        infowindow.open(map, marker);
                    }
                })(marker, i));
            }
            // Create the custom button
            var customButton = document.createElement('div');
            customButton.innerHTML = 'View Full Map';
            customButton.classList.add('custom-map-control-button');

            // Add the button to the map
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(customButton);

            // Add click event listener to the button
            customButton.addEventListener('click', function() {
                window.location.href = "{{ route('public.map') }}";
            });
        }
    </script>
@endpush
