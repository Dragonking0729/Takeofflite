<script>
    var _token = $("input[name=_token]").val();
    var url = <?php echo json_encode(route('invoice_group.store')); ?>; // url
    let someBlock = $('.someBlock');

    // show add section
    $(document).on('click', '.add', function () {
        $("#default_section").hide();
        $("#add_section").show();
        $("#update_invoicegroup_button").prop("disabled", true);
        $("#add_invoice_group").trigger("reset");
    });

    // hide add section
    $(document).on('click', '.add_cancel', function () {
        $("#add_section").hide();
        $("#default_section").show();
    });

    // delete
    $(document).on('click', '.delete', function (e) {
        swal({
            title: 'Are you sure you want to delete?',
            text: "If you delete this, it will be gone forever.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    let id = $(this).data('id');
                    let page = this.dataset.page;
                    someBlock.preloader();
                    $.ajax({
                        url: url + '/' + id,
                        type: 'DELETE',
                        data: {
                            id: id,
                            _token: _token
                        },
                        success: function (data) {
                            someBlock.preloader('remove');
                            if (data.status === 'success')
                                swal("Good job!", data.message, data.status);
                            else
                                swal("Oops!", data.message, data.status);

                            if (page === '1') {
                                fetch_data(page);
                            } else {
                                fetch_data(page - 1);
                            }

                        }
                    });
                }
            });
    });

    // prev page
    $(document).on('click', '.prev', function (e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        fetch_data(page);
    });

    // next page
    $(document).on('click', '.next', function (e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        fetch_data(page);
    });

    // visible update button
    $(document).on('change paste input', '#folder, #desc', function (e) {
        $("#update_invoicegroup_button").prop("disabled", false);
    });


    // update invoicegroup desc or folder
    $(document).on('click', '#update_invoicegroup_button', function (e) {
        let id = $(this).data('id');
        let desc = $('#desc').val();
        let is_folder = $('#folder').is(':checked') ? 1 : 0;
        let page = this.dataset.page;

        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_group/update_desc_folder') }}",
            type: 'POST',
            data: {
                id: id,
                afolder: is_folder,
                adesc: desc,
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                if (data.status === 'success')
                    swal("Good job!", data.message, data.status);
                else
                    swal("Oops!", data.message, data.status);
                $("#update_invoicegroup_button").prop("disabled", true);
                fetch_data(page);
            }
        });
    });

    // renumber button click
    let origin_invoicegroup;
    $(document).on('click', '#renumber', function (e) {
        origin_invoicegroup = $('#invoicegroup').val();
        $('#desc_line').hide();
        $('#folder_line').hide();
        $('#next_prev_section').hide();
        $('#invoicegroup').prop("disabled", false);
        $("#update_invoicegroup_button").prop("disabled", true);
        $("#default_ctrl_btn_group").hide();
        $("#card_footer").hide();
        $("#open_folder").hide();
        $("#confirm_renumber_section").show();
    });

    // confirm reunumbering
    $(document).on('click', '#renumber_confirm_button', function () {
        let id = $(this).data('id');
        let updated_invoicegroup_number = $('#invoicegroup').val();
        let page = this.dataset.page;

        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_group/renumbering') }}",
            type: 'POST',
            data: {
                id: id,
                updated_invoicegroup_number: updated_invoicegroup_number,
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                if (data.status === 'success')
                    swal("Good job!", data.message, data.status);
                else
                    swal("Oops!", data.message, data.status);

                $('#desc_line').show();
                $('#folder_line').show();
                $('#next_prev_section').show();
                $('#invoicegroup').prop("disabled", true);
                $("#default_ctrl_btn_group").show();
                $("#card_footer").show();
                $("#open_folder").show();
                $("#confirm_renumber_section").hide();
                fetch_data(page);
            }
        });
    });

    // cancel renumbering
    $(document).on('click', '#renumber_confirm_cancel_button', function () {
        $('#desc_line').show();
        $('#folder_line').show();
        $('#next_prev_section').show();
        $('#invoicegroup').prop("disabled", true);
        $("#default_ctrl_btn_group").show();
        $("#confirm_renumber_section").hide();
        $("#card_footer").show();
        $("#open_folder").show();
        $('#invoicegroup').val(origin_invoicegroup);
    });


    // fetch pagination data
    function fetch_data(page) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_group/fetch') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function (data) {
                someBlock.preloader('remove');
                $('#default_section').html(data);
            }
        });
    }


    // resizable modal
    let $modal = $('#treeview');
    $modal.find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw',
            alsoResize: ".modal-body, .modal-footer"
        })
        .draggable({
            handle: '.modal-header'
        });

    // draggable modal
    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });


    // add data
    $(document).ready(function () {
        $("#add_invoice_group").validate({
            rules: {
                ainvoicegroup: "required",
            },
            messages: {
                ainvoicegroup: "Please enter invoice group"
            }
        });

        $('#add_invoicegroup_button').click(function () {
            let page = this.dataset.page;
            if ($("#add_invoice_group").valid()) {
                someBlock.preloader();
                $.ajax({
                    url: "",
                    method: "POST",
                    data: $("#add_invoice_group").serialize(),
                    success: function (data) {
                        someBlock.preloader('remove');
                        if (data.status === 'success')
                            swal("Good job!", data.message, data.status);
                        else
                            swal("Oops!", data.message, data.status);
                        $("#add_section").hide();
                        $("#default_section").show();
                        fetch_data(page);
                    }
                });
            }
        });
    });
</script>