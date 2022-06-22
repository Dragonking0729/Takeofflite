<script>
    var url = window.location.href;
    var _token = $("input[name=_token]").val();
    var someBlock = $('.someBlock');
    var notifyCustomerPortal = 0;

    function getCustomerPortalLink(projectId) {
        someBlock.preloader();
        $.ajax({
            url: "{{ route('dashboard.get_customer_token') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: projectId,
                notifyCustomerPortal: notifyCustomerPortal
            },
            success: function (res) {
                someBlock.preloader('remove');
                notifyCustomerPortal = 0;
                if (res.status === 'success') {
                    toastr.success('Copied customer portal link successfully!');
                    navigator.clipboard.writeText(res.url);
                } else {
                    toastr.error('Something wrong in getting link');
                }
            }
        });
    }

    // passing project info to edit project modal
    $(document).on("click", ".edit-project", function () {
        let project_id = $(this).data('id');
        someBlock.preloader();
        $.ajax({
            url: "{{ route('dashboard.get_project_detail') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: project_id,
            },
            success: function (res) {
                $("#edit_project_modal_form").attr('action', `${url}/${project_id}`);
                let data = res.data;
                $("#update_project_name").val(data.project_name);
                $("#update_street_address1").val(data.street_address_1);
                $("#update_street_address2").val(data.street_address_2);
                $("#update_city").val(data.city);
                $("#update_state").val(data.state);
                $("#update_postal_code").val(data.postal_code);
                $("#update_customer_street_address1").val(data.customer_address_1);
                $("#update_customer_street_address2").val(data.customer_address_2);
                $("#update_customer_name").val(data.customer_name);
                $("#update_customer_email").val(data.customer_email);
                $("#update_customer_phone").val(data.customer_phone);
                $("#update_customer_city").val(data.customer_city);
                $("#update_customer_state").val(data.customer_state);
                $("#update_customer_postal_code").val(data.customer_postal_code);
                someBlock.preloader('remove');
            }
        });
    });

    // passing project id to delete project modal
    $(document).on("click", ".delete-project", function () {
        let project_id = $(this).data('id');

        $("#delete_project_modal_form").attr('action', `${url}/${project_id}`);
    });

    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // open job share modal
    $('.job_share').click(function () {
        let projectId = $(this).data('id');
        $("#job_share").data('share_project_number', projectId);
        $('#job_share_modal').modal();
    });
    // job share
    $('#job_share').click(function () {
        let share_project_number = $(this).data('share_project_number');
        let share_receiver_user_id = $('#share_receiver_user_id').val();
        if (!share_receiver_user_id) {
            toastr.error("Please enter the job share code");
        } else {
            $.ajax({
                url: "{{ route('dashboard.job_share') }}",
                method: "POST",
                data: {
                    _token: _token,
                    share_project_number: share_project_number,
                    share_receiver_user_id: share_receiver_user_id
                },
                success: function (res) {
                    toastr.success(res.message);
                    $('#job_share_modal').modal('hide');
                },
                error: function (err) {
                    toastr.error('Server error');
                    console.log(err);
                }
            });
        }
    });

    // get customer portal link
    $('.get_customer_portal_link').click(function () {
        let projectId = $(this).data('id');
        swal({
            title: 'Notify customer of their portal?',
            text: "",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "NO",
                    value: false,
                    visible: true,
                    className: "",
                    closeModal: true,
                },
                confirm: {
                    text: "YES",
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true
                }
            }
        }).then((yes) => {
            if (yes) {
                notifyCustomerPortal = 1;
            }
            $.ajax({
                url: "{{ route('dashboard.get_project_detail') }}",
                method: "POST",
                data: {
                    _token: _token,
                    project_id: projectId,
                },
                success: function (res) {
                    let customer_email = res.data.customer_email;
                    if (!customer_email) {
                        $("#project_id_for_new_customer_email").val(projectId);
                        $("#create_customer_email_modal").modal();
                    } else {
                        getCustomerPortalLink(projectId);
                    }
                }
            });
        });
    });

    // save new customer email
    $("#save_customer_email").click(function () {
        let newCustomerEmail = $("#new_customer_email").val();
        let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        let isEmail = regex.test(newCustomerEmail);

        if (!newCustomerEmail || !isEmail) {
            toastr.error("Please type the valid customer email");
        } else {
            let projectId = $("#project_id_for_new_customer_email").val();
            $.ajax({
                url: "{{ route('dashboard.store_customer_email') }}",
                method: "POST",
                data: {
                    _token: _token,
                    project_id: projectId,
                    customer_email: newCustomerEmail
                },
                success: function (res) {
                    if (res.status === 'success') {
                        $("#create_customer_email_modal").modal('hide');
                        getCustomerPortalLink(projectId);
                    } else {
                        toastr.error('Unknown issue while putting customer email to job record');
                    }
                }
            });
        }
    });


    $(document).ready(function () {
        $('#customer_phone').usPhoneFormat();
        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
    });

</script>