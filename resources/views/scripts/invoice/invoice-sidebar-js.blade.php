<script>
            @if(!empty($page_info) && $page_info['project_name'])
    var projectName = "{{$page_info['project_name']}}";
    $('#dropdownMenuLink').html(projectName);
            @endif
    var projectId = '<?php echo $page_info['project_id']; ?>';

    let invoiceExist = '{{count($invoice_list)}}';

    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    var uom = @json($uom);

    function generateInvoiceItem(invoiceLineId, invoiceItem, selectedUOM, markupPercent, invText, notes) {
        let uomOptionHTML = '';
        uom.forEach(function (option) {
            let selected = option === selectedUOM ? 'selected' : '';
            uomOptionHTML += `<option value="${option}" ${selected}>${option}</option>;`;
        });

        let invoiceItemHTML = `<div class="invoice_item" data-order="${invoiceLineId}">
                    <div class="invoice_item_left">
                        <span class="select_invoice_item" data-checked="0" data-id="${invoiceLineId}"></span>
                        <span class="invoice_item_circle"></span>
                        <span class="invoice_detail_open_close" data-open="0"></span>
                    </div>
                    <div class="invoice_item_right">
                        <div class="invoice_item_right_top">
                            <div class="item">
                                <label for="invoice_item__${invoiceLineId}">invoice Item</label>
                                <input type="text" class="form-control invoice_item_number_description"
                                       id="invoice_item__${invoiceLineId}"
                                       data-id="${invoiceLineId}" disabled
                                       value="${invoiceItem}">
                            </div>
                            <div class="invoice_qty_uom_price_cost">
                                <div class="invoice_qty">
                                    <label for="invoice_quantity__${invoiceLineId}">Quantity</label>
                                    <input type="text" class="form-control invoice_item_billing_quantity"
                                           data-id="${invoiceLineId}" id="invoice_quantity__${invoiceLineId}"
                                           value="1.00">
                                </div>
                                <div class="invoice_uom">
                                    <label for="invoice_uom__${invoiceLineId}">UOM</label>
                                    <select class="form-control invoice_item_uom" id="invoice_uom__${invoiceLineId}"
                                            data-id="${invoiceLineId}">
                                        ${uomOptionHTML}
                                    </select>
                                </div>
                                <div class="invoice_price">
                                    <label for="invoice_price__${invoiceLineId}">Unit Price</label>
                                    <input type="text" class="form-control invoice_unit_price"
                                           id="invoice_price__${invoiceLineId}"
                                           value="0.00" data-id="${invoiceLineId}">
                                </div>
                                <div class="invoice_contract_cost">
                                    <label for="invoice_contract_cost__${invoiceLineId}">Contractor Cost</label>
                                    <input type="text" class="form-control invoice_contractor_cost_total"
                                           id="invoice_contract_cost__${invoiceLineId}"
                                           data-id="${invoiceLineId}"
                                           value="0.00">
                                </div>
                            </div>
                            <div class="invoice_markup">
                                <div>
                                    <label for="invoice_markup__${invoiceLineId}">Markup Percent</label>
                                    <input type="text" class="form-control invoice_item_markup_percent"
                                           id="invoice_markup__${invoiceLineId}"
                                           data-id="${invoiceLineId}"
                                           value="${markupPercent}">
                                </div>
                                <div>
                                    <label for="invoice_markup_dollars__${invoiceLineId}">Markup Dollars</label>
                                    <input type="text" class="form-control invoice_markup_dollars"
                                           id="invoice_markup_dollars__${invoiceLineId}"
                                           data-id="${invoiceLineId}"
                                           value="0.00">
                                </div>
                            </div>
                            <div class="invoice_customer">
                                <div>
                                    <label for="invoice_customer_price_per_unit__${invoiceLineId}">Customer Price
                                        Per Unit</label>
                                    <input type="text" class="form-control invoice_customer_price_per_unit"
                                           id="invoice_customer_price_per_unit__${invoiceLineId}"
                                           data-id="${invoiceLineId}"
                                           value="0.00">
                                </div>
                                <div>
                                    <label for="invoice_customer_price__${invoiceLineId}">Customer Total
                                        Price</label>
                                    <input type="text" class="form-control invoice_customer_price"
                                           data-id="${invoiceLineId}"
                                           id="invoice_customer_price__${invoiceLineId}"
                                           value="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="invoice_item_right_bottom">
                            <div class="invoice_textarea">
                                <div class="invoice_title">
                                    <textarea class="form-control invoice_title_text" rows="2"
                                              placeholder="Enter a long title if needed - NOT SHOWN ON invoice"></textarea>
                                </div>
                                <div class="invoice_toilet">
                                    <textarea class="form-control invoice_customer_scope_explanation" rows="2"
                                              data-id="${invoiceLineId}"
                                              id="invoice_customer_scope_explanation__${invoiceLineId}"
                                              placeholder="Enter a detailed description - THIS IS SHOWN ON INVOICE">${invText}</textarea>
                                </div>
                                <div class="invoice_notes">
                                    <textarea class="form-control invoice_internal_notes" rows="2"
                                              data-id="${invoiceLineId}" id="invoice_note__${invoiceLineId}"
                                              placeholder="Notes">${notes}</textarea>
                                </div>
                                <span class="remove_single_invoice_line" data-id="${invoiceLineId}"
                                      title="Remove this invoice line"></span>
                                <span class="invoice_attach"></span>
                            </div>
                        </div>
                    </div>
                </div>`;

        $("#invoice_items").append(invoiceItemHTML);
    }

    function addPreviewInvoiceLine(invDescription, uom, invText) {
        let html = `<div class="my-3">
                <div class="d-flex justify-content-between">
                    <div>${invDescription}</div>
                    <div>1.00</div>
                    <div>${uom}</div>
                    <div>0.00</div>
                    <div>0.00</div>
                </div>
                <div>
                    <div>${invText}</div>
                </div>
            </div>`;

        $('#invoice_preview_body').append(html);
    }

    function updateinvoiceInfo(val, position) {
        let invoiceId = $('.select_invoice').val();
        $.ajax({
            url: '{{route("invoice.update_invoice_info")}}',
            method: 'POST',
            data: {
                _token: _token,
                project_id: projectId,
                invoice_id: invoiceId,
                val: val,
                position: position
            },
            success: function (res) {
                console.log(res);
            }
        });
    }

    // update invoice sidebar status
    $('#sidebarCollapse').on('click', function () {
        let sidebarStatus = $(this).data('sidebar_status') ? 0 : 1;
        $(this).data('sidebar_status', sidebarStatus);
        $('#sidebar').toggleClass('active');
        $(this).toggleClass('open');
        $.ajax({
            url: "{{ route('invoice.update_invoice_sidebar_status') }}",
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

    $('#invoice_list').on("select_node.jstree", function (e, data) {
        if (data.node.id.includes('invoice_item')) {
            let leafTxt = data.node.text;
            let invoiceItem = leafTxt.replace('-', ' ');
            let invoice_item_id = data.node.id.replace('invoice_item-', '');
            let invoiceId = $('.select_invoice').val();
            let createNewInvoice = 0;

            if (!invoiceId) {
                createNewInvoice = 1;
            }
            // someBlock.preloader();
            $.ajax({
                url: '{{route("invoice.add_invoice_line_by_tree")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    project_id: projectId,
                    invoice_id: invoiceId,
                    invoice_item_id: invoice_item_id,
                    create_new_invoice: createNewInvoice
                },
                success: function (res) {
                    console.log(res);
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        let isNewinvoice = res.data.is_new_invoice;
                        let invoice_id = res.data.invoice_id;
                        let markupPercent = res.data.markup_percent;
                        let invoiceLineId = res.data.invoice_line_id;
                        let invDescription = res.data.invoice_description;
                        let selectedUOM = res.data.uom;
                        let invText = res.data.invoice_text;
                        let notes = res.data.invoice_note;

                        if (isNewinvoice) {
                            let html = `<option value="${invoice_id}">invoice #${invoice_id}</option>`;
                            $('.select_invoice').prepend(html);
                            $('.remove_invoice').data('id', invoice_id);

                            $('.unlock_preview').data('id', invoice_id);
                            $('.lock_preview').data('id', invoice_id);

                            $("#invoice_top_info_block").show();
                            $("#invoice_bottom_info_block").show();

                            // enable toolbar buttons
                            $('.select_all').prop("disabled", false);
                            $('.invoice_email').prop("disabled", false);
                            $('.print_invoice').prop("disabled", false);
                            $('.delete_invoice').prop("disabled", false);
                            $('.preview_invoice').prop("disabled", false);
                            $('.view_invoice').prop("disabled", false);
                            $('.unlock_preview').prop("disabled", false);
                            $('.lock_preview').prop("disabled", false);
                            $('.show_hide_fields').prop("disabled", false);
                            $('.remove_invoice').prop("disabled", false);
                            $('.show_hide_block').show();
                            $('#invoice_top_info_block').show();
                            $('#invoice_bottom_info_block').show();
                        }

                        addPreviewInvoiceLine(invDescription, selectedUOM, invText);
                        generateInvoiceItem(invoiceLineId, invoiceItem, selectedUOM, markupPercent, invText, notes);
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        }
    });

    $(document).ready(function () {

        if (invoiceExist === '0') {
            $("#invoice_top_info_block").hide();
            $("#invoice_bottom_info_block").hide();
        }
        // update invoice total bar date
        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        let yyyy = today.getFullYear();
        let hh = today.getHours();
        let min = today.getMinutes();
        let ampm = (hh >= 12) ? "PM" : "AM";
        let dt = mm + '/' + dd + '/' + yyyy + '  ' + hh + ':' + min + ' ' + ampm;
        $('.invoice_date').html(dt);

        // summer note
        $('#invoice_top_info').summernote({
            height: 50,
        });
        $('#invoice_bottom_info').summernote({
            height: 50,
        });
        $("#invoice_top_info").on("summernote.change", function (e) {   // callback as jquery custom event
            updateinvoiceInfo($("#invoice_top_info").summernote('code'), 'top_info_text');
        });
        $("#invoice_bottom_info").on("summernote.change", function (e) {   // callback as jquery custom event
            updateinvoiceInfo($("#invoice_bottom_info").summernote('code'), 'bottom_info_text');
        });

        var fullHeight = function () {

            $('.js-fullheight').css('height', $(window).height());
            $(window).resize(function () {
                $('.js-fullheight').css('height', $(window).height());
            });

        };
        fullHeight();

        // assembly item sidebar data
        var invoiceIemList = @json($invoice_item_tree_data);
        $('#invoice_list').jstree({
            'core': {
                'data': invoiceIemList
            },
            'search': {
                'show_only_matches': true,
            },
            types: {
                "child": {
                    "icon": "fa fa-plus"
                }
            },
            plugins: ["theme", "types", 'search']
        });

        // search & clear tree
        $(document).on('click', '#a_search_tree', function () {
            let key = $('#a_search_tree_key').val();
            $("#invoice_list").jstree(true).search(key);
        });
        $(document).on('click', '#a_clear_search', function () {
            $("#invoice_list").jstree(true).clear_search();
        });

        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
    });
</script>