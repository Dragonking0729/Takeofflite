<script>
            @if(!empty($page_info) && $page_info['project_name'])
    var projectName = "{{$page_info['project_name']}}";
    $('#dropdownMenuLink').html(projectName);
            @endif
    var projectId = '<?php echo $page_info['project_id']; ?>';

    let proposalExist = '{{count($proposal_list)}}';

    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    var uom = @json($uom);

    function generateProposalItem(proposalLineId, proposalItem, selectedUOM, markupPercent, invText, notes) {
        let uomOptionHTML = '';
        uom.forEach(function (option) {
            let selected = option === selectedUOM ? 'selected' : '';
            uomOptionHTML += `<option value="${option}" ${selected}>${option}</option>;`;
        });

        let proposalItemHTML = `<div class="proposal_item" data-order="${proposalLineId}">
                    <div class="proposal_item_left">
                        <button type="button" class="btn select_proposal_item"
                                data-id="${proposalLineId}" data-checked="0"></button>
                        <span class="proposal_item_circle"></span>
                        <span class="proposal_detail_open_close" data-open="0"></span>
                    </div>
                    <div class="proposal_item_right">
                        <div class="proposal_item_right_top">
                            <div class="item">
                                <label for="proposal_item__${proposalLineId}">proposal Item</label>
                                <input type="text" class="form-control proposal_item_number_description"
                                       id="proposal_item__${proposalLineId}"
                                       data-id="${proposalLineId}" disabled
                                       value="${proposalItem}">
                            </div>
                            <div class="proposal_qty_uom_price_cost">
                                <div class="proposal_qty">
                                    <label for="proposal_quantity__${proposalLineId}">Quantity</label>
                                    <input type="text" class="form-control proposal_item_billing_quantity"
                                           data-id="${proposalLineId}" id="proposal_quantity__${proposalLineId}"
                                           value="1.00">
                                </div>
                                <div class="proposal_uom">
                                    <label for="proposal_uom__${proposalLineId}">UOM</label>
                                    <select class="form-control proposal_item_uom" id="proposal_uom__${proposalLineId}"
                                            data-id="${proposalLineId}">
                                        ${uomOptionHTML}
                                    </select>
                                </div>
                                <div class="proposal_price">
                                    <label for="proposal_price__${proposalLineId}">Unit Price</label>
                                    <input type="text" class="form-control proposal_unit_price"
                                           id="proposal_price__${proposalLineId}"
                                           value="0.00" data-id="${proposalLineId}">
                                </div>
                                <div class="proposal_contract_cost">
                                    <label for="proposal_contract_cost__${proposalLineId}">Contractor Cost</label>
                                    <input type="text" class="form-control proposal_contractor_cost_total"
                                           id="proposal_contract_cost__${proposalLineId}"
                                           data-id="${proposalLineId}"
                                           value="0.00">
                                </div>
                            </div>
                            <div class="proposal_markup">
                                <div>
                                    <label for="proposal_markup__${proposalLineId}">Markup Percent</label>
                                    <input type="text" class="form-control proposal_item_markup_percent"
                                           id="proposal_markup__${proposalLineId}"
                                           data-id="${proposalLineId}"
                                           value="${markupPercent}">
                                </div>
                                <div>
                                    <label for="proposal_markup_dollars__${proposalLineId}">Markup Dollars</label>
                                    <input type="text" class="form-control proposal_markup_dollars"
                                           id="proposal_markup_dollars__${proposalLineId}"
                                           data-id="${proposalLineId}"
                                           value="0.00">
                                </div>
                            </div>
                            <div class="proposal_customer">
                                <div>
                                    <label for="proposal_customer_price_per_unit__${proposalLineId}">Customer Price
                                        Per Unit</label>
                                    <input type="text" class="form-control proposal_customer_price_per_unit"
                                           id="proposal_customer_price_per_unit__${proposalLineId}"
                                           data-id="${proposalLineId}"
                                           value="0.00">
                                </div>
                                <div>
                                    <label for="proposal_customer_price__${proposalLineId}">Customer Total
                                        Price</label>
                                    <input type="text" class="form-control proposal_customer_price"
                                           data-id="${proposalLineId}"
                                           id="proposal_customer_price__${proposalLineId}"
                                           value="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="proposal_item_right_bottom">
                            <div class="proposal_textarea">
                                <div class="proposal_title">
                                    <textarea class="form-control proposal_title_text" rows="2"
                                              placeholder="Enter a long title if needed - NOT SHOWN ON PROPOSAL"></textarea>
                                </div>
                                <div class="proposal_toilet">
                                    <textarea class="form-control proposal_customer_scope_explanation" rows="2"
                                              data-id="${proposalLineId}"
                                              id="proposal_customer_scope_explanation__${proposalLineId}"
                                              placeholder="Enter a detailed description - THIS IS SHOWN ON PROPOSAL">${invText}</textarea>
                                </div>
                                <div class="proposal_notes">
                                    <textarea class="form-control proposal_internal_notes" rows="2"
                                              data-id="${proposalLineId}" id="proposal_note__${proposalLineId}"
                                              placeholder="Notes">${notes}</textarea>
                                </div>
                              <button type="button" class="btn btn-sm btn-link mr-2 remove_single_proposal_line"
                                      data-id="${proposalLineId}" title="Remove this proposal">
                                </button>
                                <span class="proposal_attach"></span>
                            </div>
                        </div>
                    </div>
                </div>`;

        $("#proposal_items").append(proposalItemHTML);
    }

    function addPreviewProposalLine(invDescription, selectedUOM, invText) {
        let html = `<div class="my-3">
                <div class="d-flex justify-content-between">
                    <div>${invDescription}</div>
                    <div>1.00</div>
                    <div>${selectedUOM}</div>
                    <div>0.00</div>
                    <div>0.00</div>
                </div>
                <div>
                    <div>${invText}</div>
                </div>
            </div>`;

        $('#proposal_preview_body').append(html);
    }

    function updateProposalInfo(val, position) {
        let proposalId = $('.select_proposal').val();
        $.ajax({
            url: '{{route("proposal.update_proposal_info")}}',
            method: 'POST',
            data: {
                _token: _token,
                project_id: projectId,
                proposal_id: proposalId,
                val: val,
                position: position
            },
            success: function (res) {
                console.log(res);
            }
        });
    }

    // update proposal sidebar status
    $('#sidebarCollapse').on('click', function () {
        let sidebarStatus = $(this).data('sidebar_status') ? 0 : 1;
        $(this).data('sidebar_status', sidebarStatus);
        $('#sidebar').toggleClass('active');
        $(this).toggleClass('open');
        $.ajax({
            url: "{{ route('proposal.update_proposal_sidebar_status') }}",
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

    $('#proposal_list').on("select_node.jstree", function (e, data) {
        if (data.node.id.includes('proposal_item')) {
            let leafTxt = data.node.text;
            let proposalItem = leafTxt.replace('-', ' ');
            let proposal_item_id = data.node.id.replace('proposal_item-', '');
            let proposalId = $('.select_proposal').val();
            let createNewProposal = 0;

            if (!proposalId) {
                createNewProposal = 1;
            }
            // someBlock.preloader();
            $.ajax({
                url: '{{route("proposal.add_proposal_line_by_tree")}}',
                method: 'POST',
                data: {
                    _token: _token,
                    project_id: projectId,
                    proposal_id: proposalId,
                    proposal_item_id: proposal_item_id,
                    create_new_proposal: createNewProposal
                },
                success: function (res) {
                    console.log(res);
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        let isNewProposal = res.data.is_new_proposal;
                        let proposal_id = res.data.proposal_id;
                        let markupPercent = res.data.markup_percent;
                        let proposalLineId = res.data.proposal_line_id;
                        let invDescription = res.data.proposal_description;
                        let selectedUOM = res.data.uom;
                        let invText = res.data.proposal_text;
                        let notes = res.data.proposal_note;

                        if (isNewProposal) {
                            let html = `<option value="${proposal_id}">Proposal #${proposal_id}</option>`;
                            $('.select_proposal').prepend(html);
                            $('.remove_proposal').data('id', proposal_id);

                            $("#proposal_top_info_block").show();
                            $("#proposal_bottom_info_block").show();

                            $('.unlock_preview').data('id', proposal_id);
                            $('.lock_preview').data('id', proposal_id);

                            // enable toolbar buttons
                            $('.select_all').prop("disabled", false);
                            $('.proposal_email').prop("disabled", false);
                            $('.print_proposal').prop("disabled", false);
                            $('.delete_proposal').prop("disabled", false);
                            $('.preview_proposal').prop("disabled", false);
                            $('.view_proposal').prop("disabled", false);
                            $('.unlock_preview').prop("disabled", false);
                            $('.lock_preview').prop("disabled", false);
                            $('.show_hide_fields').prop("disabled", false);
                            $('.remove_proposal').prop("disabled", false);
                            $('.proposal_status_dropdown').prop("disabled", false);
                            $('.show_hide_block').show();
                            $('#proposal_top_info_block').show();
                            $('#proposal_bottom_info_block').show();

                        }

                        addPreviewProposalLine(invDescription, selectedUOM, invText);
                        generateProposalItem(proposalLineId, proposalItem, selectedUOM, markupPercent, invText, notes);
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        }
    });

    $(document).ready(function () {

        if (proposalExist === '0') {
            $("#proposal_top_info_block").hide();
            $("#proposal_bottom_info_block").hide();
        }
        // update proposal total bar date
        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        let yyyy = today.getFullYear();
        let dt = mm + '/' + dd + '/' + yyyy;
        $('.proposal_date').html(dt);

        // summer note
        $('#proposal_top_info').summernote({
            height: 50,
        });
        $('#proposal_bottom_info').summernote({
            height: 50,
        });
        $("#proposal_top_info").on("summernote.change", function (e) {   // callback as jquery custom event
            updateProposalInfo($("#proposal_top_info").summernote('code'), 'top_info_text');
        });
        $("#proposal_bottom_info").on("summernote.change", function (e) {   // callback as jquery custom event
            updateProposalInfo($("#proposal_bottom_info").summernote('code'), 'bottom_info_text');
        });

        var fullHeight = function () {

            $('.js-fullheight').css('height', $(window).height());
            $(window).resize(function () {
                $('.js-fullheight').css('height', $(window).height());
            });

        };
        fullHeight();

        // assembly item sidebar data
        var proposalIemList = @json($proposal_item_tree_data);
        $('#proposal_list').jstree({
            'core': {
                'data': proposalIemList
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
            $("#proposal_list").jstree(true).search(key);
        });
        $(document).on('click', '#a_clear_search', function () {
            $("#proposal_list").jstree(true).clear_search();
        });

        // if proposal is approved or pending status, disable edit
        let proposalStatus = '{{$proposal_texts ? $proposal_texts->approve_status : ''}}';
        if (proposalStatus === 'Approved' || proposalStatus === 'Pending Approval') {
            disableEdit();
        }

        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
    });
</script>