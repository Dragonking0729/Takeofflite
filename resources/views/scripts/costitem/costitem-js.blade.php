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

    function showHideConversionFactorArea() {
        let takeoff_uom = $("#takeoff_uom").val();
        if (takeoff_uom === $("#labor_uom").val()) {
            $("#labor_conversion_factor").val("1.0000");
            $("#labor_conversion_factor_area").hide();
        } else {
            $("#labor_conversion_factor_area").css('display', 'flex');
        }
        if (takeoff_uom === $("#material_uom").val()) {
            $("#material_conversion_factor").val("1.0000");
            $("#material_conversion_factor_area").hide();
        } else {
            $("#material_conversion_factor_area").css('display', 'flex');
        }
        if (takeoff_uom === $("#subcontract_uom").val()) {
            $("#subcontract_conversion_factor").val("1.0000");
            $("#subcontract_conversion_factor_area").hide();
        } else {
            $("#subcontract_conversion_factor_area").css('display', 'flex');
        }
    }

    // fetch pagination data
    function fetch_data(page) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('costitem/fetch') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function(res) {
                someBlock.preloader('remove');
                tags = res.data.formula_params;
                console.log('fetched tags...', tags);
                $('#default_cost_item_section').html(res.data.view_data);
                $('.select2-pre-defined-calc').select2();
                $('.select2-variables').select2();
                $('.select2-functions').select2();
                $('.select2-invoice').select2();
                $('.select2-proposal').select2();
                document.getElementById("add_costitem_button").dataset.page = page;
                showHideConversionFactorArea();
            }
        });
    }

    //  update cost item
    function updateCostItem() {
        let useLabor = $('.check_use_labor').data('checked');
        let useMaterial = $('.check_use_material').data('checked');
        let useSub = $('.check_use_sub').data('checked');
        if (!useLabor && !useMaterial && !useSub) {
            toastr.error('At least one cost type must be selected');
        } else if ($('#takeoff_uom').val() === " ") {
            toastr.error("Please select takeoff unit of measure");
        } else if ($('#labor_uom').val() === " " ||
            $('#material_uom').val() === " " ||
            $('#subcontract_uom').val() === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else {
            let data = {
                id: $("#update").attr('data-id'),
                item_desc: $("#item_desc").val(),
                item_notes: $("#item_notes").val(),
                takeoff_uom: $("#takeoff_uom").val(),
                labor_uom: $("#labor_uom").val(),
                material_uom: $("#material_uom").val(),
                subcontract_uom: $("#subcontract_uom").val(),
                labor_conversion_factor: $("#labor_conversion_factor").val(),
                material_conversion_factor: $("#material_conversion_factor").val(),
                subcontract_conversion_factor: $("#subcontract_conversion_factor").val(),
                material_waste_factor: $("#material_waste_factor").val(),
                labor_price: $("#labor_price").val(),
                material_price: $("#material_price").val(),
                subcontract_price: $("#subcontract_price").val(),
                labor_conversion_toggle_status: $("#labor_conversion_toggle_status").val(),
                material_conversion_toggle_status: $("#material_conversion_toggle_status").val(),
                subcontract_conversion_toggle_status: $("#subcontract_conversion_toggle_status").val(),
                lowes_sku: $("#lowes_sku").val(),
                lowes_price: $("#lowes_price").val(),
                home_depot_sku: $("#home_depot_sku").val(),
                home_depot_price: $("#home_depot_price").val(),
                whitecap_sku: $("#whitecap_sku").val(),
                whitecap_price: $("#whitecap_price").val(),
                bls_number: $("#bls_number").val(),
                bls_price: $("#bls_price").val(),
                grainger_number: $("#grainger_number").val(),
                grainger_price: $("#grainger_price").val(),
                wcyw_number: $("#wcyw_number").val(),
                wcyw_price: $("#wcyw_price").val(),
                item_invoice: $("#item_invoice").val(),
                item_proposal: $("#item_proposal").val(),
                use_labor: useLabor,
                use_material: useMaterial,
                use_sub: useSub
            };
            // get formula
            data['formula_params'] = JSON.stringify(tags);
            if (data.conversion_factor <= 0) {
                toastr.error("Conversion factor should be over zero");
            } else if (!data.item_desc) {
                toastr.error("Please type item description");
            } else {
                let page = document.getElementById('update').dataset.page;
                someBlock.preloader();
                $.ajax({
                    url: "{{ url('costitem/update_costitem') }}",
                    type: 'POST',
                    data: {
                        data: data,
                        _token: _token
                    },
                    success: function(data) {
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
    const url = <?php echo json_encode(route('costitem.store')); ?>; // url
    const someBlock = $('.someBlock');
    let origin_costitem_number;
    let origin_costgroup_number;
    let addCostItemFlag = false;
    let activeUpdateFlag = false;


    // prev page
    $(document).on('click', '.prev', function(e) {
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
                        updateCostItem();
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
    $(document).on('click', '.next', function(e) {
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
                        updateCostItem();
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


    $(document).on('click', '#add', function() {
        $("#default_cost_item_section").hide();
        $("#add_cost_item_section").show();
        $("#add_cost_item").trigger("reset");
        addCostItemFlag = true;
    });

    // update cancel
    $(document).on('click', '#cancel', function() {
        updateCancelBtnClicked();
    });

    // add cancel
    $(document).on('click', '.cancel', function() {
        // revert checkbox
        $('.a_check_use_labor').attr('src', unCheckboxPath);
        $('.a_check_use_labor').data('checked', 0);
        $('.a_check_use_material').attr('src', unCheckboxPath);
        $('.a_check_use_material').data('checked', 0);
        $('.a_check_use_sub').attr('src', unCheckboxPath);
        $('.a_check_use_sub').data('checked', 0);

        $("#default_cost_item_section").show();
        $("#add_cost_item_section").hide();
        $("#add").prop('disabled', false);
        addCostItemFlag = false;
        updateCancelBtnClicked();
    });

    // visible update button
    $(document).on('keyup change paste input', ':input, textarea, select', function(e) {
        if (!$(e.target).is("#search_tree_key") && !$(e.target).is("#create_search_tree_key") && !$(e.target)
            .is("#calculation_name") &&
            !$(e.target).parents('#test_formula_body').length) {
            updateBtnClicked();
        }
    });

    // add data
    $(document).on('click', '.save', function() {
        let useLabor = $('.a_check_use_labor').data('checked');
        let useMaterial = $('.a_check_use_material').data('checked');
        let useSub = $('.a_check_use_sub').data('checked');

        if (!$("#acost_group").val()) {
            toastr.error("Please select cost group");
        } else if (!$("#aitem_number").val()) {
            toastr.error("Please type item number");
        } else if (!$("#aitem_desc").val()) {
            toastr.error("Please type item description");
        } else if ($('#atakeoff_uom').val() === " ") {
            toastr.error("Please select takeoff unit of measure");
        } else if ($('#alabor_uom').val() === " " ||
            $('#amaterial_uom').val() === " " ||
            $('#asubcontract_uom').val() === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else if ($("#alabor_conversion_factor").val() <= 0 ||
            $("#amaterial_conversion_factor").val() <= 0 ||
            $("#asubcontract_conversion_factor").val() <= 0) {
            toastr.error("Conversion factor should be over zero");
        } else if (!useLabor && !useMaterial && !useSub) {
            toastr.error('At least one cost type must be selected');
        } else {
            // let page = document.getElementById('add_costitem_button').dataset.page;
            let formData = $("#add_cost_item").serializeArray();
            // get formula
            formData.push({
                name: 'formula_params',
                value: JSON.stringify(newTags)
            });
            formData.push({
                name: 'use_labor',
                value: useLabor
            });
            formData.push({
                name: 'use_material',
                value: useMaterial
            });
            formData.push({
                name: 'use_sub',
                value: useSub
            });
            someBlock.preloader();
            $.ajax({
                url: "",
                method: "POST",
                data: formData,
                success: function(data) {
                    someBlock.preloader('remove');
                    if (data.status === 'error') {
                        toastr.error(data.message);
                    } else {
                        toastr.success(data.message);
                        addCostItemFlag = false;

                        // revert checkbox
                        $('.a_check_use_labor').attr('src', unCheckboxPath);
                        $('.a_check_use_labor').data('checked', 0);
                        $('.a_check_use_material').attr('src', unCheckboxPath);
                        $('.a_check_use_material').data('checked', 0);
                        $('.a_check_use_sub').attr('src', unCheckboxPath);
                        $('.a_check_use_sub').data('checked', 0);

                        $("#default_cost_item_section").show();
                        $("#add").prop('disabled', false);
                        $("#delete").prop('disabled', false);
                        $("#renumber").prop('disabled', false);
                        $("#add_cost_item_section").hide();
                        $("#cancel").hide();
                        $("#update").hide();
                        $("#back").show();
                        $('#costitem_tree').jstree(true).settings.core.data = JSON.parse(data
                            .tree_data);
                        $('#costitem_tree').jstree(true).refresh();
                        fetch_data(data.page);
                    }
                }
            });
        }
    });

    // update
    $(document).on('click', '#update', function() {
        updateCostItem();
    });


    // delete
    $(document).on('click', '#delete', function(e) {
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
                    success: function(res) {
                        someBlock.preloader('remove');
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            $('#default_cost_item_section').html(res.data.view_data);
                            $('#costitem_tree').jstree(true).settings.core.data = JSON
                                .parse(res.data.tree_data);
                            $('#costitem_tree').jstree(true).refresh();
                            if (page === 1) {
                                fetch_data(page);
                            } else {
                                fetch_data(page - 1);
                            }
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    fail: function(e) {
                        someBlock.preloader('remove');
                        console.log(e);
                    }
                });
            }
        });
    });

    // renumber button click
    $(document).on('click', '#renumber', function() {
        origin_costitem_number = $('#item_number').val();
        origin_costgroup_number = $('#costgroup').val();
        let group_number = $('#costgroup').data('group_number');

        $('#costgroup').val(group_number);
        $('#costgroup').attr('type', 'number');

        $('div.form-group').hide();
        $('div h5').hide();
        $('.cost_item_block:nth-child(2)').css("border", "none");
        $('.cost_item_block:nth-child(3)').css("border", "none");
        $('.cost_item_block:nth-child(4)').css("border", "none");
        $("#add").hide();
        $('#pagination_btn').hide();
        $("#delete").hide();
        $("#renumber").hide();
        $('.card-footer').hide();
        $("#default_ctrl_btn_group").css("display", "none !important");

        $("#item_number_group").show();
        $("#item_number").prop('disabled', false);
        $("#costgroup_div").show();
        $("#costgroup").prop('disabled', false);

        $("#renumber_confirm_cancel_button").show();
        $("#renumber_confirm_button").show();
    });

    // confirm renumbering
    $(document).on('click', '#renumber_confirm_button', function() {
        let id = $(this).data('id');
        let updated_costitem_number = $('#item_number').val();
        let updated_costgroup_number = $('#costgroup').val();

        let page = document.getElementById('renumber_confirm_button').dataset.page;

        someBlock.preloader();
        $.ajax({
            url: "{{ url('costitem/renumbering') }}",
            type: 'POST',
            data: {
                id: id,
                updated_costitem_number: updated_costitem_number,
                updated_costgroup_number: updated_costgroup_number,
                costgroup_number: origin_costgroup_number,
                _token: _token
            },
            success: function(data) {
                someBlock.preloader('remove');
                if (data.status === 'success')
                    swal("Good job!", data.message, data.status);
                else
                    swal("Oops!", data.message, data.status);

                $('div.form-group').show();
                $('div h5').show();
                $('.cost_item_block:nth-child(2)').css("border", "2px solid");
                $('.cost_item_block:nth-child(3)').css("border", "2px solid");

                $("#delete").show();
                $("#delete").prop('disabled', false);

                $('#pagination_btn').show();

                $("#renumber").show();
                $("#renumber").prop('disabled', false);

                $('.card-footer').show();

                $("#item_number_group").show();
                $("#item_number").prop('disabled', true);
                $("#costgroup").prop('disabled', true);
                $("#add").prop('disabled', false);

                $("#renumber_confirm_cancel_button").hide();
                $("#renumber_confirm_button").hide();

                if (data.status === 'success') {
                    $('#costitem_tree').jstree(true).settings.core.data = JSON.parse(data
                    .tree_data);
                    $('#costitem_tree').jstree(true).refresh();
                }

                fetch_data(page);
            }
        });
    });

    // cancel renumbering
    $(document).on('click', '#renumber_confirm_cancel_button', function() {
        $('#costgroup').attr('type', 'text');
        $('div.form-group').show();
        $('div h5').show();
        $('.cost_item_block:nth-child(2)').css("border", "2px solid");
        $('.cost_item_block:nth-child(3)').css("border", "2px solid");
        $('.cost_item_block:nth-child(4)').css("border", "2px solid");

        $("#add").show();

        $("#delete").show();
        $("#delete").prop('disabled', false);
        $("#renumber").show();

        $('#pagination_btn').show();

        $("#renumber").prop('disabled', false);

        $('.card-footer').show();

        $("#item_number_group").show();
        $("#item_number").prop('disabled', true);
        $("#costgroup").prop('disabled', true);
        $("#add").prop('disabled', false);

        $("#renumber_confirm_cancel_button").hide();
        $("#renumber_confirm_button").hide();

        $('#item_number').val(origin_costitem_number);
        $('#costgroup').val(origin_costgroup_number);
    });


    /**
     * Add cost item dropdown - atakeoff_uom, alabor_uom, amaterial_uom, asubcontract_uom
     */
    $(document).on('change', '#atakeoff_uom', function() {
        let atakeoff_uom = $("#atakeoff_uom").val();
        if (atakeoff_uom === " ") {
            toastr.error("Takeoff unit of measure cannot be blank")
        } else {
            let alabor_uom = $("#alabor_uom").val();
            let amaterial_uom = $("#amaterial_uom").val();
            let asubcontract_uom = $("#asubcontract_uom").val();
            // update conversion factor takeoff uom text
            $('.atakeoff_uom_toggle_text').html(atakeoff_uom);
            $('.alabor_uom_toggle_text').html(alabor_uom);
            $('.amaterial_uom_toggle_text').html(amaterial_uom);
            $('.asubcontract_uom_toggle_text').html(asubcontract_uom);
            if (atakeoff_uom === alabor_uom) {
                $("#alabor_conversion_factor").val("1.0000");
                $("#alabor_conversion_factor_area").hide();
            } else {
                $("#alabor_conversion_factor_area").css('display', 'flex');
            }
            if (atakeoff_uom === amaterial_uom) {
                $("#amaterial_conversion_factor").val("1.0000");
                $("#amaterial_conversion_factor_area").hide();
            } else {
                $("#amaterial_conversion_factor_area").css('display', 'flex');
            }
            if (atakeoff_uom === asubcontract_uom) {
                $("#asubcontract_conversion_factor").val("1.0000");
                $("#asubcontract_conversion_factor_area").hide();
            } else {
                $("#asubcontract_conversion_factor_area").css('display', 'flex');
            }
        }
    });
    $(document).on('change', '#alabor_uom', function() {
        let unit = $("#alabor_uom").val();
        if (unit === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else {
            $('#alabor_price_unit').html('per ' + unit);
            let alabor_uom = $("#alabor_uom").val();
            // update conversion factor labor uom text
            $('.alabor_uom_toggle_text').html(alabor_uom);
            if (alabor_uom === $("#atakeoff_uom").val()) {
                $("#alabor_conversion_factor").val("1.0000");
                $("#alabor_conversion_factor_area").hide();
            } else {
                $("#alabor_conversion_factor_area").css('display', 'flex');
            }
        }
    });
    $(document).on('change', '#amaterial_uom', function() {
        let unit = $("#amaterial_uom").val();
        if (unit === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else {
            $('#amaterial_price_unit').html('per ' + unit);
            let amaterial_uom = $("#amaterial_uom").val();
            // update conversion factor material uom text
            $('.amaterial_uom_toggle_text').html(amaterial_uom);
            if (amaterial_uom === $("#atakeoff_uom").val()) {
                $("#amaterial_conversion_factor").val("1.0000");
                $("#amaterial_conversion_factor_area").hide();
            } else {
                $("#amaterial_conversion_factor_area").css('display', 'flex');
            }
        }
    });
    $(document).on('change', '#asubcontract_uom', function() {
        let unit = $("#asubcontract_uom").val();
        if (unit === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else {
            $('#asubcontract_price_unit').html('per ' + unit);
            let asubcontract_uom = $("#asubcontract_uom").val();
            // update conversion factor subcontract uom text
            $('.asubcontract_uom_toggle_text').html(asubcontract_uom);
            if (asubcontract_uom === $("#atakeoff_uom").val()) {
                $("#asubcontract_conversion_factor").val("1.0000");
                $("#asubcontract_conversion_factor_area").hide();
            } else {
                $("#asubcontract_conversion_factor_area").css('display', 'flex');
            }
        }
    });

    // update conversion labor toggle status
    $(document).on("click", '#alabor_conversion_toggle_on', function() {
        $(this).removeClass('btn-outline-secondary');
        $(this).addClass('btn-success');
        $('#alabor_conversion_toggle_off').removeClass('btn-success');
        $('#alabor_conversion_toggle_off').addClass('btn-outline-secondary');
        $('#alabor_conversion_toggle_status').val(1);
    });
    $(document).on("click", '#alabor_conversion_toggle_off', function() {
        $(this).removeClass('btn-outline-secondary');
        $(this).addClass('btn-success');
        $('#alabor_conversion_toggle_on').removeClass('btn-success');
        $('#alabor_conversion_toggle_on').addClass('btn-outline-secondary');
        $('#alabor_conversion_toggle_status').val(0);
    });
    // update conversion material toggle status
    $(document).on("click", '#amaterial_conversion_toggle_on', function() {
        $(this).removeClass('btn-outline-secondary');
        $(this).addClass('btn-success');
        $('#amaterial_conversion_toggle_off').removeClass('btn-success');
        $('#amaterial_conversion_toggle_off').addClass('btn-outline-secondary');
        $('#amaterial_conversion_toggle_status').val(1);
    });
    $(document).on("click", '#amaterial_conversion_toggle_off', function() {
        $(this).removeClass('btn-outline-secondary');
        $(this).addClass('btn-success');
        $('#amaterial_conversion_toggle_on').removeClass('btn-success');
        $('#amaterial_conversion_toggle_on').addClass('btn-outline-secondary');
        $('#amaterial_conversion_toggle_status').val(0);
    });
    // update conversion subcontract toggle status
    $(document).on("click", '#asubcontract_conversion_toggle_on', function() {
        $(this).removeClass('btn-outline-secondary');
        $(this).addClass('btn-success');
        $('#subcontract_conversion_toggle_off').removeClass('btn-success');
        $('#subcontract_conversion_toggle_off').addClass('btn-outline-secondary');
        $('#subcontract_conversion_toggle_status').val(1);
    });
    $(document).on("click", '#asubcontract_conversion_toggle_off', function() {
        $(this).removeClass('btn-outline-secondary');
        $(this).addClass('btn-success');
        $('#asubcontract_conversion_toggle_on').removeClass('btn-success');
        $('#asubcontract_conversion_toggle_on').addClass('btn-outline-secondary');
        $('#asubcontract_conversion_toggle_status').val(0);
    });


    /**
     * Update cost item dropdown - takeoff_uom, labor_uom, material_uom, subcontract_uom
     */
    $(document).on('change', '#takeoff_uom', function() {
        let takeoff_uom = $("#takeoff_uom").val();
        if (takeoff_uom === " ") {
            toastr.error("Takeoff unit of measure cannot be blank")
        } else {
            // update conversion factor takeoff uom text
            $('.takeoff_uom_toggle_text').html(takeoff_uom);
            if (takeoff_uom === $("#labor_uom").val()) {
                $("#labor_conversion_factor").val("1.0000");
                $("#labor_conversion_factor_area").hide();
            } else {
                $("#labor_conversion_factor_area").css('display', 'flex');
            }
            if (takeoff_uom === $("#material_uom").val()) {
                $("#material_conversion_factor").val("1.0000");
                $("#material_conversion_factor_area").hide();
            } else {
                $("#material_conversion_factor_area").css('display', 'flex');
            }
            if (takeoff_uom === $("#subcontract_uom").val()) {
                $("#subcontract_conversion_factor").val("1.0000");
                $("#subcontract_conversion_factor_area").hide();
            } else {
                $("#subcontract_conversion_factor_area").css('display', 'flex');
            }
        }
    });
    $(document).on('change', '#labor_uom', function() {
        // update price unit
        let unit = $("#labor_uom").val();
        if (unit === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else {
            $('#labor_price_unit').html('per ' + unit);
            let labor_uom = $("#labor_uom").val();
            // update conversion factor labor uom text
            $('.labor_uom_toggle_text').html(labor_uom);
            // update conversion factor area
            if (labor_uom === $("#takeoff_uom").val()) {
                $("#labor_conversion_factor").val("1.0000");
                $("#labor_conversion_factor_area").hide();
            } else {
                $("#labor_conversion_factor_area").css('display', 'flex');
            }
        }

    });
    $(document).on('change', '#material_uom', function() {
        // update price unit
        let unit = $("#material_uom").val();
        if (unit === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else {
            $('#material_price_unit').html('per ' + unit);
            let material_uom = $("#material_uom").val();
            // update conversion factor material uom text
            $('.material_uom_toggle_text').html(material_uom);
            // update conversion factor area
            if (material_uom === $("#takeoff_uom").val()) {
                $("#material_conversion_factor").val("1.0000");
                $("#material_conversion_factor_area").hide();
            } else {
                $("#material_conversion_factor_area").css('display', 'flex');
            }
        }
    });
    $(document).on('change', '#subcontract_uom', function() {
        // update price unit
        let unit = $("#subcontract_uom").val();
        if (unit === " ") {
            toastr.error("Unit of measure cannot be blank");
        } else {
            $('#subcontract_price_unit').html('per ' + unit);
            let subcontract_uom = $("#subcontract_uom").val();
            // update conversion factor subcontract uom text
            $('.subcontract_uom_toggle_text').html(subcontract_uom);
            // update conversion factor area
            if (subcontract_uom === $("#takeoff_uom").val()) {
                $("#subcontract_conversion_factor").val("1.0000");
                $("#subcontract_conversion_factor_area").hide();
            } else {
                $("#subcontract_conversion_factor_area").css('display', 'flex');
            }
        }
    });

    // update conversion labor toggle status
    $(document).on("click", '#labor_conversion_toggle_on', function() {
        let conversionToggleStatus = $('#labor_conversion_toggle_status').val();
        if (conversionToggleStatus === "0") {
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-success');
            $('#labor_conversion_toggle_off').removeClass('btn-success');
            $('#labor_conversion_toggle_off').addClass('btn-outline-secondary');
            // update conversion factor
            let originConversionFactor = $('#labor_conversion_factor').val();
            let updatedConversionFactor = (1 / parseFloat(originConversionFactor) * 10000) / 10000;
            $('#labor_conversion_factor').val(updatedConversionFactor);
            $('#labor_conversion_toggle_status').val(1);
            updateBtnClicked();
        }
    });
    $(document).on("click", '#labor_conversion_toggle_off', function() {
        let conversionToggleStatus = $('#labor_conversion_toggle_status').val();
        if (conversionToggleStatus === "1") {
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-success');
            $('#labor_conversion_toggle_on').removeClass('btn-success');
            $('#labor_conversion_toggle_on').addClass('btn-outline-secondary');
            // update conversion factor
            let originConversionFactor = $('#labor_conversion_factor').val();
            let updatedConversionFactor = (1 / parseFloat(originConversionFactor) * 10000) / 10000;
            $('#labor_conversion_factor').val(updatedConversionFactor);
            $('#labor_conversion_toggle_status').val(0);
            updateBtnClicked();
        }
    });
    // update conversion material toggle status
    $(document).on("click", '#material_conversion_toggle_on', function() {
        let conversionToggleStatus = $('#material_conversion_toggle_status').val();
        if (conversionToggleStatus === "0") {
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-success');
            $('#material_conversion_toggle_off').removeClass('btn-success');
            $('#material_conversion_toggle_off').addClass('btn-outline-secondary');
            // update conversion factor
            let originConversionFactor = $('#material_conversion_factor').val();
            let updatedConversionFactor = (1 / parseFloat(originConversionFactor) * 10000) / 10000;
            $('#material_conversion_factor').val(updatedConversionFactor);
            $('#material_conversion_toggle_status').val(1);
            updateBtnClicked();
        }
    });
    $(document).on("click", '#material_conversion_toggle_off', function() {
        let conversionToggleStatus = $('#material_conversion_toggle_status').val();
        if (conversionToggleStatus === "1") {
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-success');
            $('#material_conversion_toggle_on').removeClass('btn-success');
            $('#material_conversion_toggle_on').addClass('btn-outline-secondary');
            // update conversion factor
            let originConversionFactor = $('#material_conversion_factor').val();
            let updatedConversionFactor = (1 / parseFloat(originConversionFactor) * 10000) / 10000;
            $('#material_conversion_factor').val(updatedConversionFactor);
            $('#material_conversion_toggle_status').val(0);
            updateBtnClicked();
        }
    });
    // update conversion subcontract toggle status
    $(document).on("click", '#subcontract_conversion_toggle_on', function() {
        let conversionToggleStatus = $('#subcontract_conversion_toggle_status').val();
        if (conversionToggleStatus === "0") {
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-success');
            $('#subcontract_conversion_toggle_off').removeClass('btn-success');
            $('#subcontract_conversion_toggle_off').addClass('btn-outline-secondary');
            // update conversion factor
            let originConversionFactor = $('#subcontract_conversion_factor').val();
            let updatedConversionFactor = (1 / parseFloat(originConversionFactor) * 10000) / 10000;
            $('#subcontract_conversion_factor').val(updatedConversionFactor);
            $('#subcontract_conversion_toggle_status').val(1);
            updateBtnClicked();
        }
    });
    $(document).on("click", '#subcontract_conversion_toggle_off', function() {
        let conversionToggleStatus = $('#subcontract_conversion_toggle_status').val();
        if (conversionToggleStatus === "1") {
            $(this).removeClass('btn-outline-secondary');
            $(this).addClass('btn-success');
            $('#subcontract_conversion_toggle_on').removeClass('btn-success');
            $('#subcontract_conversion_toggle_on').addClass('btn-outline-secondary');
            // update conversion factor
            let originConversionFactor = $('#subcontract_conversion_factor').val();
            let updatedConversionFactor = (1 / parseFloat(originConversionFactor) * 10000) / 10000;
            $('#subcontract_conversion_factor').val(updatedConversionFactor);
            $('#subcontract_conversion_toggle_status').val(0);
            updateBtnClicked();
        }
    });

    // use labor, material, sub checkboxes
    const checkboxPath = "{{ asset('icons/check.png') }}";
    const unCheckboxPath = "{{ asset('icons/uncheck.png') }}";
    $(document).on('click', '.check_use_labor, .check_use_material, .check_use_sub', function() {
        let isChecked = $(this).data('checked');
        if (isChecked) {
            $(this).attr('src', unCheckboxPath);
            $(this).data('checked', 0);
        } else {
            $(this).attr('src', checkboxPath);
            $(this).data('checked', 1);
        }
        updateBtnClicked();
    });
    $(document).on('click', '.a_check_use_labor, .a_check_use_material, .a_check_use_sub', function() {
        let isChecked = $(this).data('checked');
        if (isChecked) {
            $(this).attr('src', unCheckboxPath);
            $(this).data('checked', 0);
        } else {
            $(this).attr('src', checkboxPath);
            $(this).data('checked', 1);
        }
    });

    // open storing formula modal
    $(document).on("click", '.open_save_formula_modal', function() {
        if (addCostItemFlag) {
            if (!newTags.length) {
                toastr.error('No exists formula');
            } else {
                $('#save_formula_modal').modal('show');
            }
        } else {
            if (!tags.length) {
                toastr.error('No exists formula');
            } else {
                $('#save_formula_modal').modal('show');
            }
        }
    });
    // store formula
    $("#save_formula").click(function() {
        let calculationName = $("#calculation_name").val();
        let formula_body = '';
        if (addCostItemFlag) {
            formula_body = newTags;
        } else {
            formula_body = tags;
        }
        if (!calculationName) {
            toastr.error("Please enter the calculation name.");
        } else {
            someBlock.preloader();
            $.ajax({
                url: "{{ url('costitem/store_formula') }}",
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
                        console.log(data);
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

    // show/hide advanced section
    $(document).on('click', '.show_hide_advanced_section', function() {
        $('.advanced_section').toggle(500);
    });

    // draggable modal
    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // resizable modal
    let $costitem_treeview = $('#costitem_treeview');
    $costitem_treeview.find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });


    let $create_costitem_treeview = $('#create_costitem_treeview');
    $create_costitem_treeview.find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });
</script>
