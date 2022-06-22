<script>
    window.onbeforeunload = confirmExit;

    function confirmExit() {
        if (!activeUpdateFlag)
            return undefined;
        else
            return "Changes have not been saved. Would you like to save them now?";
    }

    function updateCancelBtnClicked() {
        $("#add").prop('disabled', false);
        $("#delete").prop('disabled', false);
        $("#renumber").prop('disabled', false);
        $("#back").show();

        $("#cancel").hide();
        $("#update").hide();
        activeUpdateFlag = false;
    }

    function updateBtnClicked() {
        $("#add").prop('disabled', true);
        $("#delete").prop('disabled', true);
        $("#renumber").prop('disabled', true);
        $("#back").hide();
        $("#save").hide();

        $("#cancel").show();
        $("#update").show();
        activeUpdateFlag = true;
    }

    // fetch pagination data
    function fetch_data(page) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_item/fetch') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function (res) {
                someBlock.preloader('remove');
                $('#default_invoice_item_section').html(res.data.view_data);
                document.getElementById("add_invoiceitem_button").dataset.page = page;
            }
        });
    }

    //  update invoice item
    function updateinvoiceItem() {
        if ($('#takeoff_uom').val() === " ") {
            toastr.error("Please select takeoff unit of measure");
        }else {
            let data = {
                id: $("#update").attr('data-id'),
                item_desc: $("#item_desc").val(),
                takeoff_uom: $("#takeoff_uom").val(),
                markup_percent: $("#markup_percent").val(),
                explanatory_text: $("#explanatory_text").val(),
                internal_notes: $("#internal_notes").val(),
            };
            if (!data.item_desc) {
                toastr.error("Please type item description");
            } else {
                let page = document.getElementById('update').dataset.page;
                someBlock.preloader();
                $.ajax({
                    url: "{{ url('invoice_item/update_invoiceitem') }}",
                    type: 'POST',
                    data: {
                        data: data,
                        _token: _token
                    },
                    success: function (data) {
                        someBlock.preloader('remove');
                        if (data.status === 'success') {
                            toastr.success(data.message);
                            updateCancelBtnClicked();
                            fetch_data(page);
                        } else {
                            toastr.error(data.message);
                        }
                    }
                });
            }
        }

    }


    const _token = $("input[name=_token]").val();
    const url = <?php echo json_encode(route('invoice_item.store')); ?>; // url
    const someBlock = $('.someBlock');
    let origin_invoiceitem_number;
    let origin_invoicegroup_number;
    let addinvoiceItemFlag = false;
    let activeUpdateFlag = false;


    // prev page
    $(document).on('click', '.prev', function (e) {
        e.preventDefault();
        if (activeUpdateFlag) {
            swal({
                title: 'Changes have not been saved. Would you like to save them now?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((confirmed) => {
                    if (confirmed) {
                        updateinvoiceItem();
                    } else {
                        updateCancelBtnClicked();
                        let page = $(this).attr('href').split('page=')[1];
                        fetch_data(page);
                    }
                });
        } else {
            let page = $(this).attr('href').split('page=')[1];
            fetch_data(page);
        }
    });

    // next page
    $(document).on('click', '.next', function (e) {
        e.preventDefault();
        if (activeUpdateFlag) {
            swal({
                title: 'Changes have not been saved. Would you like to save them now?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((confirmed) => {
                    if (confirmed) {
                        updateinvoiceItem();
                    } else {
                        updateCancelBtnClicked();
                        let page = $(this).attr('href').split('page=')[1];
                        fetch_data(page);
                    }
                });
        } else {
            let page = $(this).attr('href').split('page=')[1];
            fetch_data(page);
        }
    });


    $(document).on('click', '#add', function () {
        $("#default_invoice_item_section").hide();
        $("#add_invoice_item_section").show();
        $("#add_invoice_item").trigger("reset");
        addinvoiceItemFlag = true;
    });

    // update cancel
    $(document).on('click', '#cancel', function () {
        updateCancelBtnClicked();
    });

    // add cancel
    $(document).on('click', '.cancel', function () {
        $("#default_invoice_item_section").show();
        $("#add_invoice_item_section").hide();
        $("#add").prop('disabled', false);
        addinvoiceItemFlag = false;
        updateCancelBtnClicked();
    });

    // visible update button
    $(document).on('keyup change paste input', ':input, textarea, select', function (e) {
        if (!$(e.target).is("#search_tree_key") && !$(e.target).is("#create_search_tree_key") && !$(e.target).is("#calculation_name")
            && !$(e.target).parents('#test_formula_body').length) {
            updateBtnClicked();
        }
    });

    // add data
    $(document).on('click', '.save', function () {
        if (!$("#ainvoice_group").val()) {
            toastr.error("Please select invoice group");
        } else if (!$("#aitem_number").val()) {
            toastr.error("Please type item number");
        } else if (!$("#aitem_desc").val()) {
            toastr.error("Please type item description");
        } else if ($('#atakeoff_uom').val() === " ") {
            toastr.error("Please select takeoff unit of measure");
        } else {
            let page = document.getElementById('add_invoiceitem_button').dataset.page;
            let formData = $("#add_invoice_item").serializeArray();
            // get formula
            someBlock.preloader();
            $.ajax({
                url: "",
                method: "POST",
                data: formData,
                success: function (data) {
                    someBlock.preloader('remove');
                    if (data.status === 'error') {
                        toastr.error(data.message);
                    } else {
                        toastr.success(data.message);
                        addinvoiceItemFlag = false;
                        $("#default_invoice_item_section").show();
                        $("#add").prop('disabled', false);
                        $("#delete").prop('disabled', false);
                        $("#renumber").prop('disabled', false);
                        $("#add_invoice_item_section").hide();
                        $("#cancel").hide();
                        $("#update").hide();
                        $("#back").show();
                        $('#invoiceitem_tree').jstree(true).settings.core.data = JSON.parse(data.tree_data);
                        $('#invoiceitem_tree').jstree(true).refresh();
                        fetch_data(page);
                    }
                }
            });
        }
    });

    // update
    $(document).on('click', '#update', function () {
        updateinvoiceItem();
    });


    // delete
    $(document).on('click', '#delete', function (e) {
        e.preventDefault();
        swal({
            title: 'Are you sure you want to delete?',
            text: "If you delete this, it will be gone forever.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                let id = $(this).data('id');
                let page = document.getElementById('delete').dataset.page;
                someBlock.preloader();
                $.ajax({
                    url: url + '/' + id,
                    type: 'DELETE',
                    data: {
                        id: id,
                        _token: _token
                    },
                    success: function (res) {
                        someBlock.preloader('remove');
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            $('#default_invoice_item_section').html(res.data.view_data);
                            $('#invoiceitem_tree').jstree(true).settings.core.data = JSON.parse(res.data.tree_data);
                            $('#invoiceitem_tree').jstree(true).refresh();
                            if (page === 1) {
                                fetch_data(page);
                            } else {
                                fetch_data(page - 1);
                            }
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    fail: function (e) {
                        someBlock.preloader('remove');
                        console.log(e);
                    }
                });
            }
        });
    });

    // renumber button click
    $(document).on('click', '#renumber', function () {
        origin_invoiceitem_number = $('#item_number').val();
        origin_invoicegroup_number = $('#invoicegroup').val();
        let group_number = $('#invoicegroup').data('group_number');

        $('#invoicegroup').val(group_number);
        $('#invoicegroup').attr('type', 'number');

        $('div.form-group').hide();
        $('div h5').hide();
        $("#add").hide();
        $('#pagination_btn').hide();
        $("#delete").hide();
        $("#renumber").hide();
        $('.card-footer').hide();
        $("#default_ctrl_btn_group").css("display", "none !important");

        $(".advanced_section").hide();

        $("#item_number_group").show();
        $("#item_number").prop('disabled', false);
        $("#invoicegroup_div").show();
        $("#invoicegroup").prop('disabled', false);

        $("#renumber_confirm_cancel_button").show();
        $("#renumber_confirm_button").show();
    });

    // confirm renumbering
    $(document).on('click', '#renumber_confirm_button', function () {
        let id = $(this).data('id');
        let updated_invoiceitem_number = $('#item_number').val();
        let updated_invoicegroup_number = $('#invoicegroup').val();

        let page = document.getElementById('renumber_confirm_button').dataset.page;

        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_item/renumbering') }}",
            type: 'POST',
            data: {
                id: id,
                updated_invoiceitem_number: updated_invoiceitem_number,
                updated_invoicegroup_number: updated_invoicegroup_number,
                invoicegroup_number: origin_invoicegroup_number,
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                if (data.status === 'success')
                    swal("Good job!", data.message, data.status);
                else
                    swal("Oops!", data.message, data.status);

                $('div.form-group').show();
                $('div h5').show();

                $("#delete").show();
                $("#delete").prop('disabled', false);

                $('#pagination_btn').show();

                $("#renumber").show();
                $("#renumber").prop('disabled', false);

                $('.card-footer').show();

                $("#item_number_group").show();
                $("#item_number").prop('disabled', true);
                $("#invoicegroup").prop('disabled', true);
                $("#add").prop('disabled', false);

                $(".advanced_section").show();

                $("#renumber_confirm_cancel_button").hide();
                $("#renumber_confirm_button").hide();

                if (data.status === 'success') {
                    $('#invoiceitem_tree').jstree(true).settings.core.data = JSON.parse(data.tree_data);
                    $('#invoiceitem_tree').jstree(true).refresh();
                }

                fetch_data(page);
            }
        });
    });

    // cancel renumbering
    $(document).on('click', '#renumber_confirm_cancel_button', function () {
        $('#invoicegroup').attr('type', 'text');
        $('div.form-group').show();
        $('div h5').show();

        $("#add").show();

        $("#delete").show();
        $("#delete").prop('disabled', false);
        $("#renumber").show();

        $('#pagination_btn').show();

        $("#renumber").prop('disabled', false);

        $('.card-footer').show();

        $("#item_number_group").show();
        $("#item_number").prop('disabled', true);
        $("#invoicegroup").prop('disabled', true);
        $("#add").prop('disabled', false);

        $(".advanced_section").show();

        $("#renumber_confirm_cancel_button").hide();
        $("#renumber_confirm_button").hide();

        $('#item_number').val(origin_invoiceitem_number);
        $('#invoicegroup').val(origin_invoicegroup_number);
    });


    /**
     * Add invoice item dropdown - atakeoff_uom, alabor_uom, amaterial_uom, asubcontract_uom
     */
    $(document).on('change', '#atakeoff_uom', function () {
        let atakeoff_uom = $("#atakeoff_uom").val();
        if (atakeoff_uom === " ") {
            toastr.error("Takeoff unit of measure cannot be blank")
        }
    });


    /**
     * Update invoice item dropdown - takeoff_uom, labor_uom, material_uom, subcontract_uom
     */
    $(document).on('change', '#takeoff_uom', function () {
        let takeoff_uom = $("#takeoff_uom").val();
        if (takeoff_uom === " ") {
            toastr.error("Takeoff unit of measure cannot be blank")
        }
    });


    // draggable modal
    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // resizable modal
    let $invoiceitem_treeview = $('#invoiceitem_treeview');
    $invoiceitem_treeview.find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });


    let $create_invoiceitem_treeview = $('#create_invoiceitem_treeview');
    $create_invoiceitem_treeview.find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

</script>