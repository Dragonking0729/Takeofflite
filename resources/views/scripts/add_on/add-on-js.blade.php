<script>
    // get data after add/update/delete
    function get_data(page) {
        $.ajax({
            url: "{{ url('add_on/getdata') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function (data) {
                $('#default_section').html(data);
                document.getElementById("add_addons_button").dataset.page = page;
            }
        });
    }

    // fetch pagination data
    function fetch_data(page) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('add_on/fetch') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function (data) {
                someBlock.preloader('remove');
                document.getElementById("add_addons_button").dataset.page = page;
                $('#default_section').html(data);
            }
        });
    }

    // add
    function addAddons() {
        let page = document.getElementById('add_addons_button').dataset.page;
        someBlock.preloader();
        $.ajax({
            url: "",
            method: "POST",
            data: $("#add_addons").serialize(),
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


    const url = <?php echo json_encode(route('add_on.store')); ?>; // url
    const _token = $("input[name=_token]").val();
    let someBlock = $('.someBlock');


    // show add section
    $(document).on('click', '.add', function () {
        $("#default_section").hide();
        $("#add_section").show();
        $("#update_button").prop("disabled", true);
        $("#add_addons").trigger("reset");
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
    $(document).on('change paste input', '#add_on_name, #add_on_calc_method, #add_on_calc_value, #add_on_calc_category', function () {
        $("#update_button").prop("disabled", false);
    });

    // update
    $(document).on('click', '#update_button', function () {
        let id = $(this).data('id');
        let add_on_name = $('#add_on_name').val();
        let add_on_calc_method = $('#add_on_calc_method').val();
        let add_on_calc_value = $('#add_on_calc_value').val();
        let add_on_calc_category = $('#add_on_calc_category').val();
        let page = document.getElementById('update_button').dataset.page;
        if (!add_on_calc_value || !add_on_name) {
            toastr.error("Please enter the input values");
        } else {
            someBlock.preloader();
            $.ajax({
                url: "{{ url('add_on/update') }}",
                type: 'POST',
                data: {
                    id: id,
                    addon_name: add_on_name,
                    addon_method: add_on_calc_method,
                    addon_category: add_on_calc_category,
                    addon_value: add_on_calc_value,
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
        }
    });


    // add data
    $('#add_addons_button').click(function () {
        if ($("#add_addons").valid()) {
            addAddons();
        }
    });


    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    $(document).ready(function () {
        $("#add_addons").validate({
            rules: {
                aadd_on_name: "required"
            },
            messages: {
                aadd_on_name: "Please enter add-on name"
            }
        });
    });
</script>