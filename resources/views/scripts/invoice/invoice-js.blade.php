<script>
    var _token = $("input[name=_token]").val();
    var someBlock = $('.someBlock');

    // add commas to a number
    $.fn.customDigits = function () {
        return this.each(function () {
            $(this).text($(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
        })
    };

    // sortable invoice line
    $(".invoice_item_circle").on('mousedown', function () {
        $("#invoice_items").sortable({
            update: function (event, ui) {
                let order = $("#invoice_items").sortable("toArray", {
                    attribute: "data-order"
                });
                console.log(order);
                $.ajax({
                    url: "{{ route('invoice.update_invoice_line_order') }}",
                    method: "POST",
                    data: {
                        _token: _token,
                        order: order
                    },
                    success: function (data) {
                        console.log(data);
                    }
                });
            }
        });
    });

    // expand/collapse invoice detail
    $(document).on('click', '.invoice_detail_open_close', function () {
        let isOpen = $(this).data('open');
        if (isOpen) {
            $(this).data('open', 0);
            $(this).css('background', 'url("/icons/expand_bottom.svg") no-repeat 0 0 / 25px 25px');
            $(this).parent().parent().find('.invoice_item_right_bottom').hide();
        } else {
            $(this).data('open', 1);
            $(this).css('background', 'url("/icons/expand_top.svg") no-repeat 0 0 / 25px 25px');
            $(this).parent().parent().find('.invoice_item_right_bottom').show();
        }
    });

    // check/uncheck invoice line
    var selectedLines = [];
    $(document).on('click', '.select_invoice_item', function () {
        $('.delete_invoice').removeClass('disabled');
        let isChecked = $(this).data('checked');
        if (isChecked) {
            $(this).data('checked', 0);
            let lineId = $(this).data('id');
            let index = selectedLines.indexOf(lineId);
            if (index > -1) {
                selectedLines.splice(index, 1);
            }
            $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
            $('.select_all').css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
        } else {
            $(this).data('checked', 1);
            let lineId = $(this).data('id');
            if (!selectedLines.includes(lineId)) {
                selectedLines.push(lineId);
            }
            $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            if ($('.select_invoice_item').length === selectedLines.length) {
                $('.select_all').css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            }
        }
        // enable/disable multiple delete invoice button
        let deletable = false;
        $('.select_invoice_item').each(function () {
            if ($(this).data('checked')) {
                deletable = true;
            }
        });
        if (deletable) {
            $('.delete_invoice').removeClass('disabled');
        } else {
            $('.delete_invoice').addClass('disabled');
        }
    });
    // check all invoice line
    $('.select_all').on('click', function () {
        let isChecked = $(this).data('checked');
        selectedLines = [];
        if (isChecked) {
            $(this).data('checked', 0);
            $('.select_invoice_item').each(function () {
                $(this).data('checked', 0);
                $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
            });
            $('.delete_invoice').addClass('disabled');
            $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
        } else {
            $(this).data('checked', 1);
            $('.select_invoice_item').each(function () {
                let lineId = $(this).data('id');
                selectedLines.push(lineId);
                $(this).data('checked', 1);
                $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            });
            $('.delete_invoice').removeClass('disabled');
            $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
        }
    });
    // remove multiple invoice lines
    $('.delete_invoice').click(function () {
        someBlock.preloader();
        $.ajax({
            url: '{{route("invoice.remove_bulk_invoice_items")}}',
            method: 'POST',
            data: {
                _token: _token,
                selectedLines: selectedLines,
            },
            success: function (res) {
                if (res.status === 'success') {
                    window.location.reload();
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });
    // delete single invoice line
    $(document).on('click', '.remove_single_invoice_line', function () {
        let invoiceId = $(this).data('id');
        someBlock.preloader();
        $.ajax({
            url: '{{route("invoice.remove_single_invoice_line")}}',
            method: 'POST',
            data: {
                _token: _token,
                invoiceId: invoiceId,
            },
            success: function (res) {
                if (res.status === 'success') {
                    window.location.reload();
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });

    // update invoice fields quantity, uom, markup percent, title ???, toilet, note
    $(document).on('change', '.invoice_item_number_description, .invoice_item_billing_quantity, .invoice_item_uom, .invoice_unit_price, .invoice_item_markup_percent, .invoice_customer_scope_explanation, .invoice_internal_notes, .invoice_contractor_cost_total, .invoice_markup_dollars, .invoice_customer_price, .invoice_customer_price_per_unit', function () {
        let target = $(this).attr('class').replace('form-control ', '');
        let invId = $(this).data('id');
        let value = $(this).val();

        if (value === '0' && target === 'invoice_item_billing_quantity') {
            toastr.error("Quantity should not be zero.");
            return false;
        }

        if (!value) {
            value = 0;
            $(this).val(value);
        }

        if (target === 'invoice_item_billing_quantity') {
            let contractorCost = parseFloat($("#invoice_contract_cost__" + invId).val());
            let unitPrice = Math.round(100 * contractorCost / parseFloat(value)) / 100;
            let customerTotalPrice = parseFloat($("#invoice_customer_price__" + invId).val());
            let customerPricePerUnit = Math.round(100 * customerTotalPrice / parseFloat(value)) / 100;
            $("#invoice_price__" + invId).val(unitPrice);
            $("#invoice_customer_price_per_unit__" + invId).val(customerPricePerUnit);
        } else if (target === 'invoice_item_markup_percent') {
            let oldMarkupDollars = $("#invoice_markup_dollars__" + invId).val();
            let oldContractCostTotal = parseFloat($('.contract_total_cost').html());
            let oldMarkupTotal = parseFloat($('.markup_total').html());

            let contractorCost = parseFloat($("#invoice_contract_cost__" + invId).val());
            let qty = parseFloat($("#invoice_quantity__" + invId).val());
            let markupDollars = Math.round(100 * (contractorCost * parseFloat(value) / 100)) / 100;
            let customerTotalPrice = contractorCost + markupDollars;
            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#invoice_markup_dollars__" + invId).val(markupDollars);
            $("#invoice_customer_price_per_unit__" + invId).val(customerPricePerUnit);
            $("#invoice_customer_price__" + invId).val(customerTotalPrice);

            // update total bar values
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);
            $('.customer_price').html(oldContractCostTotal + newMarkupTotal);
        } else if (target === 'invoice_item_number_description') {
            if (!value) {
                toastr.error("Please enter the valid invoice item");
                return false;
            } else {
                let strArray = value.split(" ");
                let invoiceItem = strArray[0];
                let invoiceDescription = '';
                for (let i = 1; i < strArray.length; i++) {
                    invoiceDescription += strArray[i] + ' ';
                }
                invoiceDescription = invoiceDescription.trim();
                value = {
                    invoice_item_number: invoiceItem,
                    invoice_item_description: invoiceDescription,
                };
            }
        } else if (target === 'invoice_unit_price') {
            let oldContractCostTotal = parseFloat($('.contract_total_cost').html());
            let oldContractCost = parseFloat($("#invoice_contract_cost__" + invId).val());
            let unitPrice = parseFloat(value);
            let qty = parseFloat($("#invoice_quantity__" + invId).val());
            let contractorCost = Math.round(100 * unitPrice * qty) / 100;
            $("#invoice_contract_cost__" + invId).val(contractorCost);
            let newContractCostTotal = oldContractCostTotal - oldContractCost + contractorCost;
            $('.contract_total_cost').html(newContractCostTotal);

            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let oldMarkupDollars = parseFloat($("#invoice_markup_dollars__" + invId).val());
            let markupPercent = parseFloat($("#invoice_markup__" + invId).val());
            let markupDollars = contractorCost * markupPercent / 100;
            $("#invoice_markup_dollars__" + invId).val(markupDollars);
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let customerTotalPrice = contractorCost + markupDollars;
            $("#invoice_customer_price__" + invId).val(customerTotalPrice);

            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#invoice_customer_price_per_unit__" + invId).val(customerPricePerUnit);

            let customerPriceTotal = newContractCostTotal + newMarkupTotal;
            $('.customer_price').html(customerPriceTotal);
        } else if (target === 'invoice_contractor_cost_total') {
            let contractorCost = parseFloat(value);
            let qty = parseFloat($("#invoice_quantity__" + invId).val());
            let oldUnitPrice = parseFloat($("#invoice_price__" + invId).val());
            let unitPrice = Math.round(100 * contractorCost / qty) / 100;
            $("#invoice_price__" + invId).val(unitPrice);

            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let oldMarkupDollars = parseFloat($("#invoice_markup_dollars__" + invId).val());
            let markupPercent = parseFloat($("#invoice_markup__" + invId).val());
            let markupDollars = 0;
            if (markupPercent) {
                markupDollars = Math.round(contractorCost * markupPercent) / 100;
            }
            $("#invoice_markup_dollars__" + invId).val(markupDollars);

            let customerTotalPrice = contractorCost + markupDollars;
            $("#invoice_customer_price__" + invId).val(customerTotalPrice);

            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#invoice_customer_price_per_unit__" + invId).val(customerPricePerUnit);

            // update total bar values
            let oldContractCostTotal = parseFloat($('.contract_total_cost').html());
            let oldContractCost = oldUnitPrice * qty;
            let newContractCostTotal = oldContractCostTotal - oldContractCost + contractorCost;
            $('.contract_total_cost').html(newContractCostTotal);

            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);


            $('.customer_price').html(newContractCostTotal + newMarkupTotal);
        } else if (target === 'invoice_markup_dollars') {
            let markupDollars = parseFloat(value);
            let contractorCost = parseFloat($("#invoice_contract_cost__" + invId).val());
            let oldMarkupPercent = parseFloat($("#invoice_markup__" + invId).val());
            let markupPercent = markupDollars / contractorCost * 100;
            let oldMarkupDollars = contractorCost * oldMarkupPercent / 100;
            $("#invoice_markup__" + invId).val(markupPercent);

            let oldCustomerPrice = parseFloat($("#invoice_customer_price__" + invId).val());
            let customerTotalPrice = contractorCost + markupDollars;
            $("#invoice_customer_price__" + invId).val(customerTotalPrice);

            let qty = parseFloat($("#invoice_quantity__" + invId).val());
            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#invoice_customer_price_per_unit__" + invId).val(customerPricePerUnit);

            // update invoice total bar
            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let oldCustomerPriceTotal = parseFloat($('.customer_price').html());
            let newCustomerTotalPrice = oldCustomerPriceTotal - oldCustomerPrice + customerTotalPrice;
            $('.customer_price').html(newCustomerTotalPrice);
        } else if (target === 'invoice_customer_price') {
            let customerPrice = parseFloat(value);
            // update customer price per unit
            let qty = parseFloat($("#invoice_quantity__" + invId).val());
            let customerPricePerUnit = Math.round(100 * customerPrice / qty) / 100;
            $("#invoice_customer_price_per_unit__" + invId).val(customerPricePerUnit);
            // update markup dollars
            let oldMarkupDollars = parseFloat($("#invoice_markup_dollars__" + invId).val());
            let contractorCost = parseFloat($("#invoice_contract_cost__" + invId).val());
            let oldCustomerPrice = oldMarkupDollars + contractorCost;
            let markupDollars = customerPrice - contractorCost;
            $("#invoice_markup_dollars__" + invId).val(markupDollars);
            // update markup percent
            let markupPercent = '';
            if (contractorCost !== 0) {
                markupPercent = markupDollars / contractorCost * 100;
                $("#invoice_markup__" + invId).val(markupPercent);
            }

            // update invoice total bar values
            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let oldCustomerPriceTotal = parseFloat($('.customer_price').html());
            let newCustomerTotalPrice = oldCustomerPriceTotal - oldCustomerPrice + customerPrice;
            $('.customer_price').html(newCustomerTotalPrice);
        } else if (target === 'invoice_customer_price_per_unit') {
            let customerPricePerUnit = parseFloat(value);
            // update customer total price
            let oldCustomerPrice = parseFloat($("#invoice_customer_price__" + invId).val());
            let qty = parseFloat($("#invoice_quantity__" + invId).val());
            let customerPrice = customerPricePerUnit * qty;
            $("#invoice_customer_price__" + invId).val(customerPrice);
            // update markup dollars
            let oldMarkupDollars = parseFloat($("#invoice_markup_dollars__" + invId).val());
            let contractorCost = parseFloat($("#invoice_contract_cost__" + invId).val());
            let markupDollars = customerPrice - contractorCost;
            $("#invoice_markup_dollars__" + invId).val(markupDollars);
            // update markup percent
            let markupPercent = '';
            if (contractorCost !== 0) {
                markupPercent = markupDollars / contractorCost * 100;
                $("#invoice_markup__" + invId).val(markupPercent);
            }

            // update invoice total bar values
            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let oldCustomerPriceTotal = parseFloat($('.customer_price').html());
            let newCustomerTotalPrice = oldCustomerPriceTotal - oldCustomerPrice + customerPrice;
            $('.customer_price').html(newCustomerTotalPrice);
        }

        console.log('changed>>>', invId, target, value);
        if (target !== 'invoice_title_text') {
            $.ajax({
                url: "{{ route('invoice.update_invoice_line_field') }}",
                method: "POST",
                data: {
                    _token: _token,
                    invoice_id: invId,
                    field: target,
                    value: value
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }
    });


    // send invoice email
    $('.invoice_email').click(function () {
        someBlock.preloader();
        $.ajax({
            url: '{{route("invoice.send_invoice_email")}}',
            method: 'POST',
            data: {
                _token: _token,
                project_id: projectId,
            },
            success: function (res) {
                someBlock.preloader('remove');
                if (res.status === 'success') {
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    });


    // preview invoice
    $('.preview_invoice').click(function () {
        $('.preview_invoice').hide();
        $('.show_hide_block').hide();
        $('.view_invoice').show();
        let showDetailNumber = $('#show_detail_number').is(':checked');

        let lockStatus = $('.lock_preview').attr('data-view');
        if (lockStatus === '1') {
            $('.lock_preview').show();
        } else {
            $('.unlock_preview').show();
        }

        // total price
        let totalPrice = parseFloat($(".customer_price").html());
        $('.invoice_preview_total_price span').html(totalPrice);
        // add commas to a number
        $('.invoice_preview_total_price span').customDigits();

        let html = '';
        $(".invoice_item").each(function () {
            let invoiceId = $(this).data('order');

            let invoiceItem = document.getElementById("invoice_item__" + invoiceId).value;
            let invoiceDescription = '';
            if (!showDetailNumber) {
                let strArray = invoiceItem.split(" ");
                for (let i = 1; i < strArray.length; i++) {
                    invoiceDescription += strArray[i] + ' ';
                }
            } else {
                invoiceDescription = invoiceItem;
            }

            let invoiceQty = document.getElementById("invoice_quantity__" + invoiceId).value;
            let invoiceUOM = document.getElementById("invoice_uom__" + invoiceId).value;
            let invoiceUnitPrice = document.getElementById("invoice_price__" + invoiceId).value;
            let invoiceCustomerPrice = document.getElementById("invoice_customer_price__" + invoiceId).value;
            let invoiceExplanatory = document.getElementById("invoice_customer_scope_explanation__" + invoiceId).value;

            html += `<tr>
                    <td>${invoiceDescription}</td>
                    <td>${invoiceExplanatory}</td>
            </tr>`;
        });

        $('#invoice_preview_body').html(html);

        $('#invoice_top_info_block').hide();
        $('#invoice_bottom_info_block').hide();
        $('#invoice_items_block').hide();
        $('#invoice_preview_block').show();
    });

    // exit preview invoice mode
    $('.view_invoice').click(function () {
        $('.preview_invoice').show();
        $('.show_hide_block').show();
        $('.view_invoice').hide();

        $('.lock_preview').hide();
        $('.unlock_preview').hide();

        $('#invoice_top_info_block').show();
        $('#invoice_bottom_info_block').show();
        $('#invoice_items_block').show();
        $('#invoice_preview_block').hide();
    });


    // unlock preview - un publish
    $('.lock_preview').click(function () {
        $('.lock_preview').attr('data-view', 0);
        let invoiceId = $(this).data('id');

        {{--someBlock.preloader();--}}
        if (invoiceId) {
            $.ajax({
                url: '{{route("invoice.update_lock")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    invoice_id: invoiceId,
                    is_locked: 0,
                    preview_content: ''
                },
                success: function (res) {
                    console.log(res);
                    someBlock.preloader('remove');
                    $('.lock_preview').hide();
                    $('.unlock_preview').show();
                    toastr.success(res.message);
                }
            });
        }
    });

    // lock preview - publish
    $('.unlock_preview').click(function () {
        $('.lock_preview').attr('data-view', 1);
        let invoiceId = $(this).data('id');
        let previewContent = $("#invoice_preview_block").html();

        {{--someBlock.preloader();--}}
        if (invoiceId) {
            $.ajax({
                url: '{{route("invoice.update_lock")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    invoice_id: invoiceId,
                    is_locked: 1,
                    preview_content: previewContent
                },
                success: function (res) {
                    console.log(res);
                    someBlock.preloader('remove');
                    $('.unlock_preview').hide();
                    $('.lock_preview').show();
                    toastr.success(res.message);
                }
            });
        }
    });

    // create new invoice
    $('.create_new_invoice').click(function () {
        $("#create_new_invoice_modal").modal();
    });

    // select invoice
    $('.select_invoice').on('change', function () {
        let invoiceId = $(this).val();
        window.location.href = '{{url("invoice")}}' + '/' + projectId + '/' + invoiceId;
    });

    // remove invoice
    $('.remove_invoice').on('click', function () {
        let invoiceId = $(this).data('id');
        console.log(invoiceId);
        swal({
            title: 'Are you sure you want to delete?',
            text: "If you delete this, it will be gone forever.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    someBlock.preloader();
                    someBlock.preloader();
                    $.ajax({
                        url: '{{route("invoice.remove_invoice")}}',
                        method: 'POST',
                        data: {
                            _token: _token,
                            project_id: projectId,
                            invoice_id: invoiceId,
                        },
                        success: function (res) {
                            someBlock.preloader('remove');
                            toastr.success(res.message);
                            window.location.reload();
                        }
                    });
                }
            });
    });

    // update top info text with document text
    $("#document_text_top_dropdown").change(function () {
        let id = $(this).val();
        if (id) {
            $.ajax({
                url: '{{route("invoice.get_document_text")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    id: id,
                },
                success: function (res) {
                    if (res.status === 'success') {
                        let text = res.data;
                        $('#invoice_top_info').summernote('code', text);
                    }
                }
            });
        }
    });

    // update bottom info text with document text
    $("#document_text_bottom_dropdown").change(function () {
        let id = $(this).val();
        if (id) {
            $.ajax({
                url: '{{route("invoice.get_document_text")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    id: id,
                },
                success: function (res) {
                    if (res.status === 'success') {
                        let text = res.data;
                        $('#invoice_bottom_info').summernote('code', text);
                    }
                }
            });
        }
    });

    // print invoice
    $(document).on('click', '.print_invoice', function () {
        $('.preview_invoice').trigger('click');
        setTimeout(function () {
            $("#invoice_preview_block").printThis();
        }, 1000);
    });

</script>