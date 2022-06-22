<script>
            @if(!empty($page_info) && $page_info['project_name'])
    var projectName = "{{$page_info['project_name']}}";
    var projectId = "{{$page_info['project_id']}}";
    $('#dropdownMenuLink').html(projectName);
    @endif

    // update documents sidebar status
    $('#sidebarCollapse').on('click', function () {
        let sidebarStatus = $(this).data('sidebar_status') ? 0 : 1;
        $(this).data('sidebar_status', sidebarStatus);
        $('#sidebar').toggleClass('active');
        $(this).toggleClass('open');
        $.ajax({
            url: "{{ route('documents.update_documents_sidebar_status') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: projectId,
                sidebar_status: sidebarStatus
            },
            success: function (res) {
                console.log(res);
            }
        });
    });

    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    $(document).ready(function () {
        var fullHeight = function () {

            $('.js-fullheight').css('height', $(window).height());
            $(window).resize(function () {
                $('.js-fullheight').css('height', $(window).height());
            });

        };
        fullHeight();

        $('#remove_measurement_objects').click(function () {
            swal({
                title: 'Are you sure you want to remove all objects?',
                text: "If you delete this, it will be gone forever.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        console.log('delete!!');
                        $('#remove_measurement_objects_form').submit();
                    }
                });
        });

    });
</script>