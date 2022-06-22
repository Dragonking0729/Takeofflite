<script>
    var _token = $("input[name=_token]").val();
    var someBlock = $('.someBlock');
    var isPreview = false;

    // add commas to a number
    $.fn.customDigits = function () {
        return this.each(function () {
            $(this).text($(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
        })
    };

    // disable edit after locked (pending, approved)
    function disableEdit() {
        console.log('not allow editing');
        $('.select_all').prop("disabled", true);
        $('.show_hide_block input').prop("disabled", true);
        $('.show_hide_block select').prop("disabled", true);
        $('#proposal_top_info').summernote('disable');
        $('#proposal_bottom_info').summernote('disable');
        $("#proposal_items input").prop("disabled", true);
        $("#proposal_items select").prop("disabled", true);
        $("#proposal_items textarea").prop("disabled", true);
        $("#proposal_items button").prop("disabled", true);
        $(".proposal_item_circle").css("pointer-events", "none");
        $('#proposal_list').css("pointer-events", "none");
    }

    // enable edit after unlocked (not sent)
    function enableEdit() {
        console.log('enable editing');
        $('.select_all').prop("disabled", false);
        $('.show_hide_block input').prop("disabled", false);
        $('.show_hide_block select').prop("disabled", false);
        $('#proposal_top_info').summernote('enable');
        $('#proposal_bottom_info').summernote('enable');
        $("#proposal_items input").prop("disabled", false);
        $("#proposal_items select").prop("disabled", false);
        $("#proposal_items textarea").prop("disabled", false);
        $("#proposal_items button").prop("disabled", false);
        $(".proposal_item_circle").css("pointer-events", "all");
        $('#proposal_list').css("pointer-events", "all");
    }

    // update proposal status manually by dropdown
    function updateStatusManually(updatedStatus) {
        let proposalId = $('.select_proposal').val();
        let data = {
            _token: _token,
            proposal_id: proposalId,
            approve_status: updatedStatus,
            is_locked: 0
        };
        if (updatedStatus === 'Not Sent') {
            data.is_locked = 0;
            $('.lock_preview').attr('data-view', 0);
        } else {
            data.is_locked = 1;
            $('.lock_preview').attr('data-view', 1);
        }

        $.ajax({
            url: '{{route("proposal.update_status_manually")}}',
            method: 'POST',
            data: data,
            success: function (res) {
                console.log(res);
                toastr.success(res.message);

                let that = $(".proposal_status_dropdown");
                let oldStatus = that.attr('class').replace('proposal_status_dropdown ', '');
                that.removeClass(oldStatus);
                if (updatedStatus === 'Approved') {
                    if (isPreview) {
                        $('.lock_preview').show();
                        $('.unlock_preview').hide();
                    }
                    that.addClass('approved');
                    disableEdit();
                } else if (updatedStatus === 'Pending Approval') {
                    if (isPreview) {
                        $('.lock_preview').show();
                        $('.unlock_preview').hide();
                    }
                    that.addClass('pending');
                    disableEdit();
                } else {
                    if (isPreview) {
                        $('.unlock_preview').show();
                        $('.lock_preview').hide();
                    }
                    that.addClass('not_sent');
                    enableEdit();
                }
            }
        });
    }

    // sortable proposal line
    $(".proposal_item_circle").on('mousedown', function () {
        $("#proposal_items").sortable({
            update: function (event, ui) {
                let order = $("#proposal_items").sortable("toArray", {
                    attribute: "data-order"
                });
                console.log(order);
                $.ajax({
                    url: "{{ route('proposal.update_proposal_line_order') }}",
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

    // expand/collapse proposal detail
    $(document).on('click', '.proposal_detail_open_close', function () {
        let isOpen = $(this).data('open');
        if (isOpen) {
            $(this).data('open', 0);
            $(this).css('background', 'url("/icons/expand_bottom.svg") no-repeat 0 0 / 25px 25px');
            $(this).parent().parent().find('.proposal_item_right_bottom').hide();
        } else {
            $(this).data('open', 1);
            $(this).css('background', 'url("/icons/expand_top.svg") no-repeat 0 0 / 25px 25px');
            $(this).parent().parent().find('.proposal_item_right_bottom').show();
        }
    });

    // check/uncheck proposal line
    var selectedLines = [];
    $(document).on('click', '.select_proposal_item', function () {
        $('.delete_proposal').removeClass('disabled');
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
            if ($('.select_proposal_item').length === selectedLines.length) {
                $('.select_all').css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            }
        }
        // enable/disable multiple delete proposal button
        let deletable = false;
        $('.select_proposal_item').each(function () {
            if ($(this).data('checked')) {
                deletable = true;
            }
        });
        if (deletable) {
            $('.delete_proposal').removeClass('disabled');
        } else {
            $('.delete_proposal').addClass('disabled');
        }
    });
    // check all proposal line
    $('.select_all').on('click', function () {
        let isChecked = $(this).data('checked');
        selectedLines = [];
        if (isChecked) {
            $(this).data('checked', 0);
            $('.select_proposal_item').each(function () {
                $(this).data('checked', 0);
                $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
            });
            $('.delete_proposal').addClass('disabled');
            $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
        } else {
            $(this).data('checked', 1);
            $('.select_proposal_item').each(function () {
                let lineId = $(this).data('id');
                selectedLines.push(lineId);
                $(this).data('checked', 1);
                $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            });
            $('.delete_proposal').removeClass('disabled');
            $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
        }
    });
    // remove multiple proposal lines
    $('.delete_proposal').click(function () {
        someBlock.preloader();
        $.ajax({
            url: '{{route("proposal.remove_bulk_proposal_items")}}',
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
    // delete single proposal line
    $(document).on('click', '.remove_single_proposal_line', function () {
        let proposalId = $(this).data('id');
        someBlock.preloader();
        $.ajax({
            url: '{{route("proposal.remove_single_proposal_line")}}',
            method: 'POST',
            data: {
                _token: _token,
                proposalId: proposalId,
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

    // update proposal fields quantity, uom, markup percent, title ???, toilet, note
    $(document).on('change', '.proposal_item_number_description, .proposal_item_billing_quantity, .proposal_item_uom, .proposal_unit_price, .proposal_item_markup_percent, .proposal_customer_scope_explanation, .proposal_internal_notes, .proposal_contractor_cost_total, .proposal_markup_dollars, .proposal_customer_price, .proposal_customer_price_per_unit', function () {
        let target = $(this).attr('class').replace('form-control ', '');
        let invId = $(this).data('id');
        let value = $(this).val();

        if (value === '0' && target === 'proposal_item_billing_quantity') {
            toastr.error("Quantity should not be zero.");
            return false;
        }

        if (!value) {
            value = 0;
            $(this).val(value);
        }

        if (target === 'proposal_item_billing_quantity') {
            // let oldContractCost = parseFloat($("#proposal_contract_cost__" + invId).val());
            // let oldContractCostTotal = parseFloat($('.contract_total_cost').html());
            // let oldMarkupDollars = parseFloat($("#proposal_markup_dollars__" + invId).val());
            // let oldMarkupTotal = parseFloat($('.markup_total').html());

            let contractorCost = parseFloat($("#proposal_contract_cost__" + invId).val());
            let unitPrice = Math.round(100 * contractorCost / parseFloat(value)) / 100;
            let customerTotalPrice = parseFloat($("#proposal_customer_price__" + invId).val());
            let customerPricePerUnit = Math.round(100 * customerTotalPrice / parseFloat(value)) / 100;
            $("#proposal_price__" + invId).val(unitPrice);
            $("#proposal_customer_price_per_unit__" + invId).val(customerPricePerUnit);
        } else if (target === 'proposal_item_markup_percent') {
            let oldMarkupDollars = parseFloat($("#proposal_markup_dollars__" + invId).val());
            let oldContractCostTotal = parseFloat($('.contract_total_cost').html());
            let oldMarkupTotal = parseFloat($('.markup_total').html());

            let contractorCost = parseFloat($("#proposal_contract_cost__" + invId).val());
            let qty = parseFloat($("#proposal_quantity__" + invId).val());
            let markupDollars = Math.round(100 * (contractorCost * parseFloat(value) / 100)) / 100;
            let customerTotalPrice = contractorCost + markupDollars;
            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#proposal_markup_dollars__" + invId).val(markupDollars);
            $("#proposal_customer_price_per_unit__" + invId).val(customerPricePerUnit);
            $("#proposal_customer_price__" + invId).val(customerTotalPrice);

            // update total bar values
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);
            $('.customer_price').html(oldContractCostTotal + newMarkupTotal);
        } else if (target === 'proposal_item_number_description') {
            if (!value) {
                toastr.error("Please enter the valid proposal item");
                return false;
            } else {
                let strArray = value.split(" ");
                let proposalItem = strArray[0];
                let proposalDescription = '';
                for (let i = 1; i < strArray.length; i++) {
                    proposalDescription += strArray[i] + ' ';
                }
                proposalDescription = proposalDescription.trim();
                value = {
                    proposal_item_number: proposalItem,
                    proposal_item_description: proposalDescription,
                };
            }
        } else if (target === 'proposal_unit_price') {
            let oldContractCostTotal = parseFloat($('.contract_total_cost').html());
            let oldContractCost = parseFloat($("#proposal_contract_cost__" + invId).val());
            let unitPrice = parseFloat(value);
            let qty = parseFloat($("#proposal_quantity__" + invId).val());
            let contractorCost = Math.round(100 * unitPrice * qty) / 100;
            $("#proposal_contract_cost__" + invId).val(contractorCost);
            let newContractCostTotal = oldContractCostTotal - oldContractCost + contractorCost;
            $('.contract_total_cost').html(newContractCostTotal);

            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let oldMarkupDollars = parseFloat($("#proposal_markup_dollars__" + invId).val());
            let markupPercent = parseFloat($("#proposal_markup__" + invId).val());
            let markupDollars = contractorCost * markupPercent / 100;
            $("#proposal_markup_dollars__" + invId).val(markupDollars);
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let customerTotalPrice = contractorCost + markupDollars;
            $("#proposal_customer_price__" + invId).val(customerTotalPrice);

            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#proposal_customer_price_per_unit__" + invId).val(customerPricePerUnit);

            let customerPriceTotal = newContractCostTotal + newMarkupTotal;
            $('.customer_price').html(customerPriceTotal);
        } else if (target === 'proposal_contractor_cost_total') {
            let contractorCost = parseFloat(value);
            let qty = parseFloat($("#proposal_quantity__" + invId).val());
            let oldUnitPrice = parseFloat($("#proposal_price__" + invId).val());
            let unitPrice = Math.round(100 * contractorCost / qty) / 100;
            $("#proposal_price__" + invId).val(unitPrice);

            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let oldMarkupDollars = parseFloat($("#proposal_markup_dollars__" + invId).val());
            let markupPercent = parseFloat($("#proposal_markup__" + invId).val());
            let markupDollars = 0;
            if (markupPercent) {
                markupDollars = Math.round(contractorCost * markupPercent) / 100;
            }
            $("#proposal_markup_dollars__" + invId).val(markupDollars);

            let customerTotalPrice = contractorCost + markupDollars;
            $("#proposal_customer_price__" + invId).val(customerTotalPrice);

            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#proposal_customer_price_per_unit__" + invId).val(customerPricePerUnit);

            // update total bar values
            let oldContractCostTotal = parseFloat($('.contract_total_cost').html());
            let oldContractCost = oldUnitPrice * qty;
            let newContractCostTotal = oldContractCostTotal - oldContractCost + contractorCost;
            $('.contract_total_cost').html(newContractCostTotal);

            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);


            $('.customer_price').html(newContractCostTotal + newMarkupTotal);
        } else if (target === 'proposal_markup_dollars') {
            let markupDollars = parseFloat(value);
            let contractorCost = parseFloat($("#proposal_contract_cost__" + invId).val());
            let oldMarkupPercent = parseFloat($("#proposal_markup__" + invId).val());
            let markupPercent = markupDollars / contractorCost * 100;
            let oldMarkupDollars = contractorCost * oldMarkupPercent / 100;
            $("#proposal_markup__" + invId).val(markupPercent);

            let oldCustomerPrice = parseFloat($("#proposal_customer_price__" + invId).val());
            let customerTotalPrice = contractorCost + markupDollars;
            $("#proposal_customer_price__" + invId).val(customerTotalPrice);

            let qty = parseFloat($("#proposal_quantity__" + invId).val());
            let customerPricePerUnit = Math.round(100 * customerTotalPrice / qty) / 100;
            $("#proposal_customer_price_per_unit__" + invId).val(customerPricePerUnit);

            // update proposal total bar
            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let oldCustomerPriceTotal = parseFloat($('.customer_price').html());
            let newCustomerTotalPrice = oldCustomerPriceTotal - oldCustomerPrice + customerTotalPrice;
            $('.customer_price').html(newCustomerTotalPrice);
        } else if (target === 'proposal_customer_price') {
            let customerPrice = parseFloat(value);
            // update customer price per unit
            let qty = parseFloat($("#proposal_quantity__" + invId).val());
            let customerPricePerUnit = Math.round(100 * customerPrice / qty) / 100;
            $("#proposal_customer_price_per_unit__" + invId).val(customerPricePerUnit);
            // update markup dollars
            let oldMarkupDollars = parseFloat($("#proposal_markup_dollars__" + invId).val());
            let contractorCost = parseFloat($("#proposal_contract_cost__" + invId).val());
            let oldCustomerPrice = oldMarkupDollars + contractorCost;
            let markupDollars = customerPrice - contractorCost;
            $("#proposal_markup_dollars__" + invId).val(markupDollars);
            // update markup percent
            let markupPercent = '';
            if (contractorCost !== 0) {
                markupPercent = markupDollars / contractorCost * 100;
            }
            $("#proposal_markup__" + invId).val(markupPercent);

            // update proposal total bar values
            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let oldCustomerPriceTotal = parseFloat($('.customer_price').html());
            let newCustomerTotalPrice = oldCustomerPriceTotal - oldCustomerPrice + customerPrice;
            $('.customer_price').html(newCustomerTotalPrice);
        } else if (target === 'proposal_customer_price_per_unit') {
            let customerPricePerUnit = parseFloat(value);
            // update customer total price
            let oldCustomerPrice = parseFloat($("#proposal_customer_price__" + invId).val());
            let qty = parseFloat($("#proposal_quantity__" + invId).val());
            let customerPrice = customerPricePerUnit * qty;
            $("#proposal_customer_price__" + invId).val(customerPrice);
            // update markup dollars
            let oldMarkupDollars = parseFloat($("#proposal_markup_dollars__" + invId).val());
            let contractorCost = parseFloat($("#proposal_contract_cost__" + invId).val());
            let markupDollars = customerPrice - contractorCost;
            $("#proposal_markup_dollars__" + invId).val(markupDollars);
            // update markup percent
            let markupPercent = '';
            if (contractorCost !== 0) {
                markupPercent = markupDollars / contractorCost * 100;
                $("#proposal_markup__" + invId).val(markupPercent);
            }
            // update proposal total bar values
            let oldMarkupTotal = parseFloat($('.markup_total').html());
            let newMarkupTotal = oldMarkupTotal - oldMarkupDollars + markupDollars;
            $('.markup_total').html(newMarkupTotal);

            let oldCustomerPriceTotal = parseFloat($('.customer_price').html());
            let newCustomerTotalPrice = oldCustomerPriceTotal - oldCustomerPrice + customerPrice;
            $('.customer_price').html(newCustomerTotalPrice);
        }

        console.log('changed>>>', invId, target, value);
        if (target !== 'proposal_title_text') {
            $.ajax({
                url: "{{ route('proposal.update_proposal_line_field') }}",
                method: "POST",
                data: {
                    _token: _token,
                    proposal_id: invId,
                    field: target,
                    value: value
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }
    });


    // send proposal email
    $('.proposal_email').click(function () {
        someBlock.preloader();
        $.ajax({
            url: '{{route("proposal.send_proposal_email")}}',
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


    // preview proposal
    $('.preview_proposal').click(function () {
        isPreview = true;
        $('.preview_proposal').hide();
        $('.show_hide_block').hide();
        $('.view_proposal').show();

        let lockStatus = $('.lock_preview').attr('data-view');
        if (lockStatus === '1') {
            $('.lock_preview').show();
        } else {
            $('.unlock_preview').show();
        }

        let showDetailNumber = $('#show_detail_number').is(':checked');

        let topInfoHtml = $("#proposal_top_info").summernote('code');
        let bottomInfoHtml = $("#proposal_bottom_info").summernote('code');

        $('#proposal_preview_top_info').html(topInfoHtml);
        $('#proposal_preview_bottom_info').html(bottomInfoHtml);

        // total price
        let totalPrice = parseFloat($(".customer_price").html());
        $('.proposal_preview_total_price span').html(totalPrice);
        // add commas to a number
        $('.proposal_preview_total_price span').customDigits();

        let html = '';
        $(".proposal_item").each(function () {
            let proposalId = $(this).data('order');

            let proposalItem = document.getElementById("proposal_item__" + proposalId).value;
            let proposalDescription = '';
            if (!showDetailNumber) {
                let strArray = proposalItem.split(" ");
                for (let i = 1; i < strArray.length; i++) {
                    proposalDescription += strArray[i] + ' ';
                }
            } else {
                proposalDescription = proposalItem;
            }

            let proposalQty = document.getElementById("proposal_quantity__" + proposalId).value;
            let proposalUOM = document.getElementById("proposal_uom__" + proposalId).value;
            let proposalUnitPrice = document.getElementById("proposal_price__" + proposalId).value;
            let proposalCustomerPrice = document.getElementById("proposal_customer_price__" + proposalId).value;
            let proposalExplanatory = document.getElementById("proposal_customer_scope_explanation__" + proposalId).value;

            html += `<tr>
                    <td>${proposalDescription}</td>
                    <td>${proposalExplanatory}</td>
            </tr>`;
        });

        $('#proposal_preview_body').html(html);

        $('#proposal_top_info_block').hide();
        $('#proposal_bottom_info_block').hide();
        $('#proposal_items_block').hide();
        $('#proposal_preview_block').show();
    });

    // exit preview proposal mode
    $('.view_proposal').click(function () {
        isPreview = false;
        $('.preview_proposal').show();
        $('.show_hide_block').show();
        $('.view_proposal').hide();

        $('.lock_preview').hide();
        $('.unlock_preview').hide();

        $('#proposal_top_info_block').show();
        $('#proposal_bottom_info_block').show();
        $('#proposal_items_block').show();
        $('#proposal_preview_block').hide();
    });

    // unlock preview - un publish
    $('.lock_preview').click(function () {
        $('.lock_preview').attr('data-view', 0);
        let proposalId = $(this).data('id');

        {{--someBlock.preloader();--}}
        if (proposalId) {
            $.ajax({
                url: '{{route("proposal.update_lock")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    proposal_id: proposalId,
                    is_locked: 0,
                    preview_content: ''
                },
                success: function (res) {
                    console.log(res);
                    someBlock.preloader('remove');
                    $('.lock_preview').hide();
                    $('.unlock_preview').show();
                    // update proposal status to Not Sent
                    let oldStatus = $(".proposal_status_dropdown").attr('class').replace('proposal_status_dropdown ', '');
                    $(".proposal_status_dropdown").removeClass(oldStatus);
                    $(".proposal_status_dropdown").val('Not Sent');
                    $(".proposal_status_dropdown").addClass('not_sent');
                    toastr.success(res.message);
                    enableEdit();
                }
            });
        }
    });

    // lock preview - publish
    $('.unlock_preview').click(function () {
        $('.lock_preview').attr('data-view', 1);
        let proposalId = $(this).data('id');
        let previewContent = $("#proposal_preview_block").html();
        // console.log('preview content>>>', previewContent);

        {{--someBlock.preloader();--}}
        if (proposalId) {
            $.ajax({
                url: '{{route("proposal.update_lock")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    proposal_id: proposalId,
                    is_locked: 1,
                    preview_content: previewContent
                },
                success: function (res) {
                    console.log(res);
                    someBlock.preloader('remove');
                    $('.unlock_preview').hide();
                    $('.lock_preview').show();
                    // update proposal status to Pending Approval
                    let oldStatus = $(".proposal_status_dropdown").attr('class').replace('proposal_status_dropdown ', '');
                    $(".proposal_status_dropdown").removeClass(oldStatus);
                    $(".proposal_status_dropdown").val('Pending Approval');
                    $(".proposal_status_dropdown").addClass('pending');
                    toastr.success(res.message);
                    disableEdit();
                }
            });
        }
    });


    // create new proposal
    $('.create_new_proposal').click(function () {
        $("#create_new_proposal_modal").modal();
    });

    // select proposal
    $('.select_proposal').on('change', function () {
        let proposalId = $(this).val();
        window.location.href = '{{url("proposal")}}' + '/' + projectId + '/' + proposalId;
    });

    // remove proposal
    $('.remove_proposal').on('click', function () {
        let proposalId = $(this).data('id');
        console.log(proposalId);
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
                    $.ajax({
                        url: '{{route("proposal.remove_proposal")}}',
                        method: 'POST',
                        data: {
                            _token: _token,
                            project_id: projectId,
                            proposal_id: proposalId,
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
                url: '{{route("proposal.get_document_text")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    id: id,
                },
                success: function (res) {
                    if (res.status === 'success') {
                        let text = res.data;
                        $('#proposal_top_info').summernote('code', text);
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
                url: '{{route("proposal.get_document_text")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    id: id,
                },
                success: function (res) {
                    if (res.status === 'success') {
                        let text = res.data;
                        $('#proposal_bottom_info').summernote('code', text);
                    }
                }
            });
        }
    });

    // print proposal
    $(document).on('click', '.print_proposal', function () {
        $('.preview_proposal').trigger('click');
        setTimeout(function () {
            $("#proposal_preview_block").printThis();
        }, 1000);
    });

    // update proposal status
    $(".proposal_status_dropdown").change(function () {
        let updatedStatus = $(this).val();
        updateStatusManually(updatedStatus);
    });

</script>