<script>
    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // update tree
    function updateTree(treeData) {
        $('#customer_portal_list').jstree({
            'core': {
                'data': treeData
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins': ["themes", "search"]
        });

        $('#customer_portal_list').jstree(true).settings.core.data = treeData;
        $('#customer_portal_list').jstree(true).refresh();
    }

    function hideAllRenderedContent() {
        $('#invoice_toolbar').hide();
        $('#invoice_preview_block').html('');
        $('#invoice_preview_block').hide();

        $('#proposal_toolbar').hide();
        $('#proposal_preview_block').html('');
        $('#proposal_preview_block').hide();

        $('#daily_log_toolbar').hide();
        $('#daily_log_preview_block').html('');
        $('#daily_log_preview_block').hide();

        $('#picture_toolbar').hide();
        $('#picture_preview_block').html('');
        $('#picture_preview_block').hide();

        $('#video_toolbar').hide();
        $('#video_preview_block').html('');
        $('#video_preview_block').hide();

        $('#other_toolbar').hide();
        $('#other_preview_block').html('');
        $('#other_preview_block').hide();
    }

    function updateProposalPage(id) {
        $(".proposal_sign_print_date").hide();

        // approve proposal form html
        let html = `<div class="proposal_approve_form">
<div class="form-group">
<label for="proposal_approve_date">Enter the date of approval</label>
<input type="date" class="form-control" id="proposal_approve_date">
</div>
<div class="form-group">
<label for="proposal_approve_full_name">Enter your full name</label>
<input type="text" class="form-control" id="proposal_approve_full_name">
</div>
<div class="form-group text-center">
<button type="button" class="btn btn-success" id="proposal_approve_submit" data-id="${id}">Approve</button>
</div>
</div>`;

        $("#proposal_preview_content").append(html);
    }

    function renderContent(id, type, url) {
        someBlock.preloader();
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: _token,
                id: id,
                type: type
            },
            success: function (res) {
                someBlock.preloader('remove');

                let $__renderToolBarId = '#' + type + '_toolbar';
                let $__renderBlockId = '#' + type + '_preview_block';
                let $__toolbarPrintClass = '.print_' + type;

                $($__toolbarPrintClass).attr('data-id', id);

                $($__renderToolBarId).show();
                $($__renderBlockId).show();
                $($__renderBlockId).html(res.data.content);

                if (type === 'proposal') {
                    updateProposalPage(id);
                }

                updateTree(res.data.tree_data);

            }
        });
    }

    var refresh = false;

    $('#customer_portal_list').on("select_node.jstree", function (e, data) {
        let url = '{{route("customer.get_preview_content")}}';
        let nodeId = data.node.id;
        let id = nodeId;
        let type = '';

        console.log('nodeId>>>', nodeId);

        if (nodeId.includes('invoice__')) {
            if (nodeId === 'invoice_folder') {
                type = '';
            } else {
                id = nodeId.replace('invoice__', '');
                type = 'invoice';
            }
        } else if (nodeId.includes('proposal__')) {
            if (nodeId === 'proposal_folder') {
                type = '';
            } else {
                id = nodeId.replace('proposal__', '');
                type = 'proposal';
            }
        } else if (nodeId === 'dailylog_folder') {
            type = 'daily_log';
        } else if (nodeId === 'pictures_folder') {
            type = 'picture';
        } else if (nodeId === 'videos_folder') {
            type = 'video';
        } else if (nodeId === 'files_folder') {
            type = 'other';
        }

        if (type) {
            if (!refresh) {
                refresh = true;
                hideAllRenderedContent();
                renderContent(id, type, url);
            } else {
                refresh = false;
            }
        }

    });

    $(document).ready(function () {
        var fullHeight = function () {
            $('.js-fullheight').css('height', $(window).height());
            $(window).resize(function () {
                $('.js-fullheight').css('height', $(window).height());
            });
        };
        fullHeight();

        // assembly item sidebar data
        var customerPortalIemList = @json($customer_portal_item_tree_data);
        $('#customer_portal_list').jstree({
            'core': {
                'data': customerPortalIemList
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins': ["themes", "search"]
        });


        // search & clear tree
        $(document).on('click', '#a_search_tree', function () {
            let key = $('#a_search_tree_key').val();
            $("#customer_portal_list").jstree(true).search(key);
        });
        $(document).on('click', '#a_clear_search', function () {
            $("#customer_portal_list").jstree(true).clear_search();
        });

        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
    });
</script>