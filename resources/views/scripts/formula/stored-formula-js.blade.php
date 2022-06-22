<script>
    // get data after add/update/delete
    function get_data(page) {
        $.ajax({
            url: "<?php echo e(url('formula/get_data')); ?>",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function(res) {
                tags = res.data.formula_params;
                $('#default_section').html(res.data.view_data);
                $('.select2-pre-defined-calc').select2();
                $('.select2-variables').select2();
                $('.select2-functions').select2();
            }
        });
    }

    // fetch pagination data
    function fetch_data(page) {
        someBlock.preloader();
        $.ajax({
            url: "<?php echo e(url('formula/fetch')); ?>",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function(res) {
                someBlock.preloader('remove');
                tags = res.data.formula_params;
                $('#default_section').html(res.data.view_data);
                $('.select2-pre-defined-calc').select2();
                $('.select2-variables').select2();
                $('.select2-functions').select2();
            }
        });
    }

    function updateBtnClicked() {
        $("#update_button").prop("disabled", false);
    }

    function updateCancelBtnClicked() {
        $("#update_button").prop("disabled", true);
    }

    function addBtnClicked() {
        $("#formula_name").val('');
        $("#update_button").hide();
        $("#back").hide();
        $("#next_prev_section").hide();
        $("#no_exist_formula").hide();
        $("#add_formula").hide();
        $("#delete").hide();
        $(".open_save_formula_modal").hide();
        $("#no_exist_stored_formula").hide();

        $("#save_new_formula").show();
        $("#cancel_add").show();
        $("#main_area").show();

        tags = [];
        addTags(tags.length);
    }

    function addCancelBtnClicked() {
        $("#update_button").show();
        $("#back").show();
        $("#next_prev_section").show();
        $("#no_exist_formula").show();
        $("#add_formula").show();
        $("#delete").show();
        $(".open_save_formula_modal").show();
        $("#no_exist_stored_formula").show();

        $("#save_new_formula").hide();
        $("#cancel_add").hide();
    }

    function saveNewFormula(page) {
        let formula_name = $("#formula_name").val();
        if (!formula_name) {
            toastr.error("Please enter the formula name.");
        } else {
            someBlock.preloader();
            $.ajax({
                url: "{{ url('costitem/store_formula') }}",
                method: "POST",
                data: {
                    _token: _token,
                    calculation_name: formula_name,
                    formula_body: JSON.stringify(tags)
                },
                success: function(data) {
                    someBlock.preloader('remove');
                    if (data.status === 'success') {
                        toastr.success(data.message);
                        get_data(page);
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    }


    const url = <?php echo json_encode(route('formula.store')); ?>; // url
    const _token = $("input[name=_token]").val();
    let someBlock = $('.someBlock');


    // delete
    $(document).on('click', '.delete', function(e) {
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
                        success: function(data) {
                            someBlock.preloader('remove');
                            if (data.status === 'success')
                                toastr.success(data.message);
                            else
                                toastr.error(data.message);
                            if (page === '1') {
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
    $(document).on('click', '.prev', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        fetch_data(page);
    });

    // next page
    $(document).on('click', '.next', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        fetch_data(page);
    });

    // visible update button
    $(document).on('change paste input', '#formula_name, #variables, #pre_defined_calc', function() {
        $("#update_button").prop("disabled", false);
    });

    // update stored formula
    $(document).on('click', '#update_button', function() {
        let id = $(this).data('id');
        let formulaName = $('#formula_name').val();
        let page = document.getElementById('update_button').dataset.page;
        someBlock.preloader();
        $.ajax({
            url: "<?php echo e(url('formula/update')); ?>",
            type: 'POST',
            data: {
                id: id,
                calculation_name: formulaName,
                formula_body: JSON.stringify(tags),
                _token: _token
            },
            success: function(data) {
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

    // add new stored formula
    $(document).on('click', '#add_formula', function() {
        addBtnClicked();
    });
    $(document).on('click', '#cancel_add', function() {
        let page = $(this).data('page');
        // addCancelBtnClicked(page);
        fetch_data(page);
    });
    $(document).on('click', '#save_new_formula', function() {
        let page = $(this).data('page');
        saveNewFormula(page);
    });

    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // open storing formula modal
    $(document).on("click", '.open_save_formula_modal', function() {
        if (!tags.length) {
            toastr.error('No exists formula');
        } else {
            $('#save_formula_modal').modal('show');
        }
    });
    // save as predefined calculation
    $("#save_formula").click(function() {
        let calculationName = $("#calculation_name").val();
        if (!calculationName) {
            toastr.error("Please enter the calculation name.");
        } else {
            let formula_body = tags;
            someBlock.preloader();
            $.ajax({
                url: "<?php echo e(url('costitem/store_formula')); ?>",
                method: "POST",
                data: {
                    _token: _token,
                    calculation_name: calculationName,
                    formula_body: JSON.stringify(formula_body)
                },
                success: function(data) {
                    someBlock.preloader('remove');
                    $("#calculation_name").val('');
                    if (data.status === 'success') {
                        toastr.success(data.message);
                        $("#save_formula_modal").modal('hide');
                        let option =
                            `<option value="${data.result.id}" data-formula_body='${data.result.formula_body}'>${data.result.calculation_name}</option>`;
                        $('.select2-pre-defined-calc').append(option);
                        updateCancelBtnClicked();
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });
    $("#close_store_formula_modal").click(function() {
        $("#calculation_name").val('');
    });
</script>
