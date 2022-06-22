<script>
    // get data after add/update/delete
    function get_data(page) {
        $.ajax({
            url: "{{ url('invoice_text/getdata') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function (data) {
                $('#default_section').html(data);
                document.getElementById("add_invoice_text_button").dataset.page = page;
                $('#text').summernote({
                    height: 100,
                });
            }
        });
    }

    // fetch pagination data
    function fetch_data(page) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_text/fetch') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function (data) {
                someBlock.preloader('remove');
                document.getElementById("add_invoice_text_button").dataset.page = page;
                $('#default_section').html(data);
                $('#text').summernote({
                    height: 100,
                });
            }
        });
    }

    // add invoice text as default
    function addInvoiceText() {
        let page = document.getElementById('add_invoice_text_button').dataset.page;
        let value = $("#atext").summernote('code');
        let formData = $("#add_invoice_text").serializeArray();
        let text = {
            name: 'atext',
            value: value
        };
        formData.push(text);

        someBlock.preloader();
        $.ajax({
            url: "",
            method: "POST",
            data: formData,
            success: function (data) {
                someBlock.preloader('remove');
                if (data.status === 'success') {
                    $("#add_section").hide();
                    $("#default_section").show();
                    get_data(page);
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            }
        });
    }


    const url = <?php echo json_encode(route('invoice_text.store')); ?>; // url
    const _token = $("input[name=_token]").val();
    let someBlock = $('.someBlock');


    // show add section
    $(document).on('click', '.add', function () {
        $("#default_section").hide();
        $("#add_section").show();
        $("#update_button").prop("disabled", true);
        $("#add_invoice_text").trigger("reset");
    });

    // hide add section
    $(document).on('click', '.add_cancel', function () {
        $("#add_section").hide();
        $("#default_section").show();
    });

    // delete
    $(document).on('click', '.delete', function (e) {
        e.preventDefault();
        swal({
            title: 'Are you sure you want to delete?',
            text: "If you delete this, it will be gone forever.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    let page = document.getElementById('delete').dataset.page;
                    let id = $(this).data('id');
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
                                toastr.success(data.message);
                            else
                                toastr.error(data.message);
                            if (page === 1) {
                                get_data(page);
                            } else {
                                get_data(page - 1);
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
    $(document).on('change paste input', '#title', function () {
        $("#update_button").prop("disabled", false);
    });
    $(document).on("summernote.change summernote.paste summernote.input", "#text", function (e) {
        $("#update_button").prop("disabled", false);
    });

    // update invoice text
    $(document).on('click', '#update_button', function () {
        let id = $(this).data('id');
        let text = $("#text").summernote('code');
        let title = $('#title').val();
        let page = document.getElementById('update_button').dataset.page;
        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_text/update') }}",
            type: 'POST',
            data: {
                id: id,
                title: title,
                text: text,
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                if (data.status === 'success') {
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
                $("#update_button").prop("disabled", true);
                get_data(page);
            }
        });
    });


    // add data
    $('#add_invoice_text_button').click(function () {
        if ($("#add_invoice_text").valid()) {
            addInvoiceText();
        }
    });


    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    $(document).ready(function () {
        $('#text').summernote({
            height: 100,
        });
        $('#atext').summernote({
            height: 100,
        });
        $("#add_invoice_text").validate({
            rules: {
                atitle: "required"
            },
            messages: {
                atitle: "Please enter title"
            }
        });
    });
</script>