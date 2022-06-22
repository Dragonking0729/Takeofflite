<script>
    // initialize map
    var job_address = {!! $job_address !!};
    console.log('job address>>>', job_address);

    function initMap() {
        // const fenway = {
        //     lat: 33.7254725,
        //     lng: -84.3033083
        // };

        const address = {
            lat: job_address.lat,
            lng: job_address.lng
        };

        const map = new google.maps.Map(document.getElementById("map"), {
            center: address,
            zoom: 14,
        });
        const panorama = new google.maps.StreetViewPanorama(
            document.getElementById("pano"), {
                position: address,
                pov: {
                    heading: 34,
                    pitch: 10,
                },
            }
        );

        map.setStreetView(panorama);
    }

    // footer navigation
    $(document).on('click', '.job_portal_footer a', function () {
        $('.job_portal_footer a').each(function (i, obj) {
            $(obj).removeClass('active');
        });
        $(this).addClass('active');
    });

    // show flip detail
    $('.show-acquisition-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');
        $('.acquisition-detail').toggle();
    });
    $('.show-holding-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');
        $('.holding-detail').toggle();
    });
    $('.show-selling-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');
        $('.selling-detail').toggle();
    });
    $('.show-financing-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');
        $('.financing-detail').toggle();
    });
</script>