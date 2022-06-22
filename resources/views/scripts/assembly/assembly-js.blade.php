<script>
    var _token = $("input[name=_token]").val();
    var url = <?php echo json_encode(route('assembly.store')); ?>; // url
    let someBlock = $('.someBlock');

    var addBtnFlag = false;
    var updateBtnFlag = false;
    var renumberBtnFlag = false;

    var updated_items = [];
    var added_items = [];

    var originItemTblHtml = '';
    var originAssemblyDesc = $("#assembly_desc").val();

    function initFlag() {
        addBtnFlag = false;
        updateBtnFlag = false;
        renumberBtnFlag = false;
    }

    // update item table section
    function updateItemTbl(items) {
        if (updateBtnFlag && originItemTblHtml === '') {
            originItemTblHtml = $("#item_tbl_body").html()
        }

        let itemTblHTML = addBtnFlag ? '' : originItemTblHtml;
        let order = 0;
        if (items.length) {
            if (updateBtnFlag) {
                items.map(item => {
                    order++;
                    itemTblHTML += `<tr class="table-secondary" data-order="${order}">
                        <td style="width: 40px;">
                            <input type="checkbox" class="select-assembly-item" data-id="${item.id}">
                        </td>
                        <td>${item.group_number}</td>
                        <td>${item.item_number}</td>
                        <td>${item.item_desc}</td>
                        <td>
                        <div class="d-flex justify-content-center px-3">
                            <div><a href="javascript:;" class="text-danger mr-3 delete-temp-item" data-id="${item.id}"><i class="fa fa-trash"></i></a></div>
                            <div>
                                <a title="Open item formula" class="btn calculator-icon a_formula_modal_open" id="formula_body__${item.id}" data-id="${item.id}" data-formula_body="[]"></a>
                                <a class="btn formula-exist-icon" title="Formula exist" id="formula-exist-icon__${item.id}" style="display: none;"></a>
                            </div>
                        </div>
                        </td>
                        </tr>`;
                });


                // ordering when update
                // <div><a class="btn grab-hand" title="Update order"></a><a class="btn up-down-item" title="Update order"></a></div>
            } else {
                items.map(item => {
                    itemTblHTML += `<tr data-order="${item.item_order}">
                        <td style="width: 40px;">
                            <input type="checkbox" class="a_select-assembly-item" data-id="${item.id}">
                        </td>
                        <td>${item.group_number}</td>
                        <td>${item.item_number}</td>
                        <td>${item.item_desc}</td>
                        <td>
                        <div class="d-flex justify-content-center px-3">
                            <div><a href="javascript:;" class="text-danger mr-3 delete-temp-item" data-id="${item.id}"><i class="fa fa-trash"></i></a></div>
                            <div>
                                <a title="Open item formula" class="btn calculator-icon a_formula_modal_open" id="formula_body__${item.id}" data-id="${item.id}" data-formula_body="[]"></a>
                                <a class="btn formula-exist-icon" title="Formula exist" id="formula-exist-icon__${item.id}" style="display: none;"></a>
                            </div>
                            <div><a class="btn a_grab-hand" title="Update order"></a><a class="btn a_up-down-item" title="Update order"></a></div>
                        </div>
                        </td>
                        </tr>`;
                });
            }
        }

        if (addBtnFlag) {
            $("#a_item_tbl_body").html(itemTblHTML);
        } else {
            $("#item_tbl_body").html(itemTblHTML);
            if (updateBtnFlag)
                updateAvailable();
        }

        $("#assembly_costitem_tree_modal").modal('hide');
    }

    // form submit
    function post(path, params, page, method = 'post') {
        //        const form = document.createElement('form');
        //        form.method = method;
        //        form.action = path;
        //
        //        for (const key in params) {
        //            if (params.hasOwnProperty(key)) {
        //                const hiddenField = document.createElement('input');
        //                hiddenField.type = 'hidden';
        //                hiddenField.name = key;
        //                hiddenField.value = params[key];
        //                form.appendChild(hiddenField);
        //            }
        //        }
        //
        //        if (method === 'post') {
        //            const hiddenField = document.createElement('input');
        //            hiddenField.type = 'hidden';
        //            hiddenField.name = '_token';
        //            hiddenField.value = _token;
        //            form.appendChild(hiddenField);
        //        }
        //
        //        document.body.appendChild(form);
        //        form.submit();
        //        form.remove();

        params['_token'] = _token;

        someBlock.preloader();
        $.ajax({
            url: path,
            method: method,
            data: params,
            success: function(data) {
                someBlock.preloader('remove');
                if (data.status === 'success') {
                    toastr.success(data.message);

                    let assembly_tree_data = data.tree_data.assembly_tree_data;
                    let costitem_tree_data = data.tree_data.costitem_tree_data;

                    $('#assembly_tree').jstree(true).settings.core.data = assembly_tree_data;
                    $('#assembly_tree').jstree(true).refresh();

                    $('#assembly_costitem_tree').jstree(true).settings.core.data = costitem_tree_data;
                    $('#assembly_costitem_tree').jstree(true).refresh();

                    fetch_data(page);
                } else {
                    toastr.error(data.message);
                }

            }
        });
    }

    // fetch pagination data
    function fetch_data(page) {
        someBlock.preloader();

        const scrollPos = document.getElementById("asseblyContainer").scrollTop;

        $.ajax({
            url: "{{ url('assembly/fetch') }}",
            method: "POST",
            data: {
                _token: _token,
                page: page
            },
            success: function(data) {
                someBlock.preloader('remove');
                originItemTblHtml = '';
                updated_items = [];
                added_items = [];
                $('#default_section').html(data);
                document.getElementById("asseblyContainer").scrollTop = scrollPos || 0;;
            }
        });
    }

    // upate page after add button clicked
    function addAvailalbe() {
        added_items = [];
        addBtnFlag = true;
        $("#assembly_number").hide();
        $("#a_assembly_number").show();

        $("#open_assembly").hide();

        $("#folder").hide();
        $("#a_folder").show();

        $("#remove_selected_items_div").hide();
        $("#a_remove_selected_items_div").show();

        $("#is_qv").hide();
        $("#a_qv").show();

        $("#assembly_desc").hide();
        $("#a_assembly_desc").show();

        $("#items").hide();
        $("#a_items").show();

        $('.prev').hide();
        $('.next').hide();
        $('.add_btn').hide();
        $('.delete').hide();
        $('.renumber').hide();

        if ($("#a_folder").is(":checked"))
            $("#open_assembly_costitem").prop('disabled', true);
        else
            $("#open_assembly_costitem").prop('disabled', false);

        $("#back").hide();
        $(".cancel").show();
        $(".ok").show();
    }

    // cancel add
    function cancelAdd() {
        added_items = [];
        addBtnFlag = false;

        $("#assembly_number").show();
        $("#a_assembly_number").hide();

        $("#open_assembly").show();

        $("#folder").show();
        $("#a_folder").hide();

        $("#remove_selected_items_div").show();
        $("#a_remove_selected_items_div").hide();

        $("#is_qv").show();
        $("#a_qv").hide();

        $("#assembly_desc").show();
        $("#a_assembly_desc").hide();

        $("#items").show();
        $("#a_items").hide();

        $('.prev').show();
        $('.next').show();
        $('.add_btn').show();
        $('.delete').show();
        $('.renumber').show();

        if ($("#folder").is(":checked"))
            $("#open_assembly_costitem").prop('disabled', true);
        else
            $("#open_assembly_costitem").prop('disabled', false);

        $("#back").show();
        $(".cancel").hide();
        $(".ok").hide();
    }

    // add assembly
    function addAssembly(page) {
        let assembly_number = $("#a_assembly_number").val();
        let folder = $("#a_folder").is(":checked") ? 1 : 0;
        let is_qv = $("#a_qv").is(":checked") ? 1 : 0;
        let assembly_desc = $("#a_assembly_desc").val();
        if (assembly_number) {
            let data = {
                assembly_number: assembly_number,
                is_folder: folder,
                is_qv: is_qv,
                assembly_desc: assembly_desc,
                items: added_items
            };
            // console.log(data);
            let url = "{{ url('assembly/add_assembly') }}";
            post(url, data, page);
        } else {
            toastr.error("Please enter assembly number");
        }
    }

    // update available
    function updateAvailable() {
        updateBtnFlag = true;

        $('.prev').hide();
        $('.next').hide();
        $('.add_btn').hide();
        $('.delete').hide();
        $('.renumber').hide();

        $('#back').hide();
        $('.ok').show();
        $('.cancel').show();
    }

    // cancel update
    function cancelUpdate() {
        updateBtnFlag = false;
        updated_items = [];

        $('#assembly_desc').val(originAssemblyDesc);

        $('.prev').show();
        $('.next').show();
        $('.add_btn').show();
        $('.delete').show();
        $('.renumber').show();

        $('#back').show();
        $('.ok').hide();
        $('.cancel').hide();
    }

    // update assembly desc and items
    function updateAssembly(page) {
        let assembly_desc = $("#assembly_desc").val();
        let id = $('.ok').attr('data-id');
        let data = {
            id: id,
            assembly_desc: assembly_desc,
            items: updated_items
        };
        let url = "{{ url('assembly/update_assembly') }}";
        post(url, data, page);
    }

    // renumber available
    function renumberAvailable() {
        renumberBtnFlag = true;

        $('.prev').hide();
        $('.next').hide();
        $('.add_btn').hide();
        $('.delete').hide();
        $('.renumber').hide();
        $('#open_assembly_costitem').hide();

        $('#assembly_number').prop('disabled', false);

        $('#open_assembly').prop('disabled', true);
        $('#folder').prop('disabled', true);
        $('#is_qv').prop('disabled', true);
        $('#assembly_desc').prop('disabled', true);

        $('#items').hide();
        $('#back').hide();
        $('.ok').show();
        $('.cancel').show();
    }

    // cancel renumber
    function cancelRenumber() {
        renumberBtnFlag = false;

        $('.prev').show();
        $('.next').show();
        $('.add_btn').show();
        $('.delete').show();
        $('.renumber').show();
        $('#open_assembly_costitem').show();

        $('#assembly_number').prop('disabled', true);

        $('#open_assembly').prop('disabled', false);
        $('#folder').prop('disabled', false);
        $('#is_qv').prop('disabled', false);
        $('#assembly_desc').prop('disabled', false);

        $('#items').show();
        $('#back').show();
        $('.ok').hide();
        $('.cancel').hide();
    }

    // renumber assembly
    function updateRenumber(page) {
        let assembly_number = $("#assembly_number").val();
        let id = $(document).find('.renumber').attr('data-id');
        if (assembly_number) {
            let data = {
                id: id,
                assembly_number: assembly_number
            };
            let url = "{{ url('assembly/update_assembly_number') }}";
            post(url, data, page);
        } else {
            toastr.error('Please enter assembly number');
        }
    }


    // prev page
    $(document).on('click', '.prev', function(e) {
        e.preventDefault();
        initFlag();
        let page = $(this).attr('href').split('page=')[1];
        fetch_data(page);
    });

    // next page
    $(document).on('click', '.next', function(e) {
        e.preventDefault();
        initFlag();
        let page = $(this).attr('href').split('page=')[1];
        fetch_data(page);
    });


    // add button click
    $(document).on('click', '.add_btn', function() {
        initFlag();
        addAvailalbe();
    });

    // delete assembly
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
                    let data = {
                        id: id
                    };
                    let url = "{{ url('assembly/delete_assembly') }}";

                    if (page === 1) {
                        post(url, data, page);
                    } else {
                        post(url, data, page - 1);
                    }
                }
            });
    });

    // delete assembly item
    $(document).on('click', '.delete-item', function() {
        swal({
                title: `Are you sure you want to delete?`,
                text: "If you delete this, it will be gone forever.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    let that = $(this);
                    let id = $(this).attr('data-id');
                    let item_delete_url = "{{ url('assembly/delete_assembly_item') }}";
                    someBlock.preloader();
                    $.ajax({
                        url: item_delete_url,
                        type: 'POST',
                        data: {
                            _token: _token,
                            id: id
                        },
                        success: function(res) {
                            someBlock.preloader('remove');
                            if (res.status === 'success') {
                                that.closest('tr').remove();
                                toastr.success(res.message);
                            } else {
                                toastr.error(res.message);
                            }
                        }
                    });
                }
            });
    });

    // renumber button click
    $(document).on('click', '.renumber', function(e) {
        initFlag();
        renumberAvailable();
    });

    // update insert item button according to checkbox when add
    $(document).on('change', '#a_folder', function() {
        if (addBtnFlag) {
            if ($("#a_folder").is(":checked")) {
                $("#open_assembly_costitem").prop('disabled', true);
                $("#a_qv").prop('checked', false);
                $("#a_qv").prop('disabled', true);
                $("#a_item_tbl_body").html('');
                added_items = [];
            } else {
                $("#open_assembly_costitem").prop('disabled', false);
                $("#a_qv").prop('disabled', false);
            }
        }
    });

    // update assembly desc
    $(document).on('change paste input', '#assembly_desc', function() {
        if (!originAssemblyDesc)
            originAssemblyDesc = $('#assembly_desc').val();
        initFlag();
        updateAvailable();
    });

    // cancel button
    $(document).on('click', '.cancel', function() {
        if (addBtnFlag) {
            cancelAdd();
        } else if (updateBtnFlag) {
            cancelUpdate();
            updateItemTbl(updated_items);
        } else if (renumberBtnFlag) {
            cancelRenumber();
        }
    });

    // ok button
    $(document).on('click', '.ok', function() {
        let page = document.getElementById('add_update').dataset.page;
        if (addBtnFlag) {
            addAssembly(page);
        } else if (updateBtnFlag) {
            updateAssembly(page);
        } else if (renumberBtnFlag) {
            updateRenumber(page);
        }
    });

    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // resizable modal
    let assembly_costitem_tree_modal = $('#assembly_costitem_tree_modal');
    assembly_costitem_tree_modal.find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

    let assembly_tree_modal = $('#assembly_tree_modal');
    assembly_tree_modal.find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

    // update is_qv according to checkbox
    $(document).on('change', '#is_qv', function() {
        let page = document.getElementById('add_update').dataset.page;
        let is_qv = $("#is_qv").is(":checked");
        swal({
                title: `Do you want to continue?`,
                text: "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((res) => {
                if (res) {
                    let id = $('.ok').attr('data-id');
                    let url = "{{ url('assembly/update_qv_field') }}";
                    someBlock.preloader();
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _token: _token,
                            assembly_id: id
                        },
                        success: function(data) {
                            someBlock.preloader('remove');
                            if (data.status === 'success') {
                                toastr.success(data.message);
                            } else {
                                $("#is_qv").prop('checked', !is_qv);
                                toastr.error(data.message);
                            }
                        }
                    });
                } else {
                    $("#is_qv").prop('checked', !is_qv);
                }
            });

    });

    // update insert item button according to checkbox
    $(document).on('change', '#folder', function() {
        let page = document.getElementById('add_update').dataset.page;
        if ($("#folder").is(":checked")) {
            //            console.log('ungroup ---> grouping...')
            swal({
                    title: `Do you want to continue?`,
                    text: "",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((grouping) => {
                    if (grouping) {
                        let id = $('.ok').attr('data-id');
                        let data = {
                            id: id
                        };
                        let url = "{{ url('assembly/grouping_assembly') }}";
                        post(url, data, page);
                    } else {
                        $('#folder').prop('checked', false);
                    }
                });
        } else {
            //            console.log('group ---> ungrouping....')
            swal({
                    title: `Do you want to continue?`,
                    text: "",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((ungrouping) => {
                    if (ungrouping) {
                        let id = $('.ok').attr('data-id');
                        let data = {
                            id: id
                        };
                        let url = "{{ url('assembly/ungrouping_assembly') }}";
                        post(url, data, page);
                    } else {
                        $('#folder').prop('checked', true);
                        cancelUpdate();
                    }
                });
        }
    });

    // sortable assembly item
    $(document).on('mousedown', '.grab-hand, .up-down-item', function() {
        $("#item_tbl_body").sortable({
            update: function(event, ui) {
                let order = $("#item_tbl_body").sortable("toArray", {
                    attribute: "data-order"
                });
                {{-- console.log(order); --}}
                {{-- if (updateBtnFlag) { --}}
                {{-- for (let i = 0; i < updated_items.length; i++) { --}}
                {{-- updated_items[i].item_order = order[i]; --}}
                {{-- } --}}
                {{-- console.log(updated_items); --}}
                {{-- } else { --}}
                {{-- $.ajax({ --}}
                {{-- url: "{{ url('assembly/update_assembly_item_order') }}", --}}
                {{-- method: "POST", --}}
                {{-- data: { --}}
                {{-- _token: _token, --}}
                {{-- order: order --}}
                {{-- }, --}}
                {{-- success: function (data) { --}}
                {{-- console.log(data); --}}
                {{-- } --}}
                {{-- }); --}}
                {{-- } --}}

                $.ajax({
                    url: "{{ url('assembly/update_assembly_item_order') }}",
                    method: "POST",
                    data: {
                        _token: _token,
                        order: order
                    },
                    success: function(data) {
                        console.log(data);
                    }
                });
            }
        });
    });

    // sortable assembly item WHEN ADD
    $(document).on('mousedown', '.a_grab-hand, .a_up-down-item', function() {
        $("#a_item_tbl_body").sortable({
            update: function(event, ui) {
                let order = $("#a_item_tbl_body").sortable("toArray", {
                    attribute: "data-order"
                });
                for (let i = 0; i < added_items.length; i++) {
                    added_items[i].item_order = order[i];
                }
            }
        });
    });

    // open storing formula modal
    $(document).on("click", '.open_save_formula_modal', function() {
        if (!tags.length) {
            toastr.error('No exists formula');
        } else {
            $('#save_formula_modal').modal('show');
        }
    });

    // store formula
    $("#save_formula").click(function() {
        let calculationName = $("#calculation_name").val();

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
                    formula_body: JSON.stringify(tags)
                },
                success: function(data) {
                    someBlock.preloader('remove');
                    $("#calculation_name").val('');
                    if (data.status === 'success') {
                        toastr.success(data.message);
                        $("#save_formula_modal").modal('hide');
                        let option =
                            `<option value="${data.result.id}" data-formula_body='${data.result.formula_body}'>${data.result.calculation_name}</option>`;
                        $('#pre_defined_calc').append(option);
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    // select all assembly item
    $(document).on('click', '.select-all-assembly-item', function() {
        let status = $(this).is(':checked');
        $('.select-assembly-item').prop('checked', status);
    });
    $(document).on('click', '.select-assembly-item', function() {
        let totalCheckboxCnt = $('.select-assembly-item').length;
        let numberOfChecked = $('.select-assembly-item:checked').length;
        if (totalCheckboxCnt === numberOfChecked) {
            $('.select-all-assembly-item').prop('checked', true);
        } else {
            $('.select-all-assembly-item').prop('checked', false);
        }
    });
    // remove selected assembly item
    $(document).on('click', '.remove_selected_items_icon', function() {
        let numberOfChecked = $('.select-assembly-item:checked').length;
        if (numberOfChecked) {
            if (updateBtnFlag) {
                console.log(updated_items);
                $('.select-assembly-item:checked').each(function() {
                    let itemId = $(this).attr('data-id');
                    updated_items = updated_items.filter(item => {
                        return item.id !== itemId;
                    });
                });
                if (!updated_items.length) {
                    cancelUpdate();
                }
                updateItemTbl(updated_items);
            } else {
                let checkedItemIds = [];
                $('.select-assembly-item:checked').each(function() {
                    let itemId = $(this).attr('data-id');
                    checkedItemIds.push(itemId);
                });
                let item_delete_url = "{{ url('assembly/bulk_delete_assembly_items') }}";
                someBlock.preloader();
                $.ajax({
                    url: item_delete_url,
                    type: 'POST',
                    data: {
                        _token: _token,
                        checked_item_ids: checkedItemIds
                    },
                    success: function(res) {
                        someBlock.preloader('remove');
                        if (res.status === 'success') {
                            $('.select-assembly-item:checked').each(function() {
                                $(this).closest('tr').remove();
                            });
                            toastr.success(res.message);
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        }

    });

    // select all assembly item when add
    $(document).on('click', '.a_select-all-assembly-item', function() {
        let status = $(this).is(':checked');
        $('.a_select-assembly-item').prop('checked', status);
    });
    $(document).on('click', '.a_select-assembly-item', function() {
        let totalCheckboxCnt = $('.a_select-assembly-item').length;
        let numberOfChecked = $('.a_select-assembly-item:checked').length;
        if (totalCheckboxCnt === numberOfChecked) {
            $('.a_select-all-assembly-item').prop('checked', true);
        } else {
            $('.a_select-all-assembly-item').prop('checked', false);
        }
    });
    $(document).on('click', '.a_remove_selected_items_icon', function() {
        let numberOfChecked = $('.a_select-assembly-item:checked').length;
        if (numberOfChecked) {
            $('.a_select-assembly-item:checked').each(function() {
                let itemId = $(this).attr('data-id');
                added_items = added_items.filter(item => {
                    return item.id !== itemId;
                });
            });
            updateItemTbl(added_items);
        }
    });
</script>
