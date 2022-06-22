<script>
    function getProposalItemById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('proposal_item/get_proposalitem_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function(res) {
                someBlock.preloader('remove');
                $('#default_proposal_item_section').html(res.data.view_data);
                document.getElementById("add_proposalitem_button").dataset.page=id;
            }
        });
    }

    // create proposal item tree ok
    $(document).on("click", "#create_proposalitem_tree_ok", function() {
        let selected_group_id = $("#create_proposalitem_tree").find(".jstree-clicked").attr('aria-level');
        if (selected_group_id) {
            if (selected_group_id === '2') {
                let text = $("#create_proposalitem_tree").find(".jstree-clicked").text();
                let group_number = text.split("-")[0];
                $("#aproposal_group").val(group_number);
                $("#aproposalgroup").val(group_number);
                $("#create_proposalitem_treeview").modal('hide');
            }
        } else {
            $("#create_proposalitem_treeview").modal('hide');
        }
    });

    // double click event to jstree
    $("#create_proposalitem_tree").on('dblclick.jstree', function() {
        let selected_group_id = $("#create_proposalitem_tree").find(".jstree-clicked").attr('aria-level');
        if (selected_group_id) {
            if (selected_group_id === '2') {
                let text = $("#create_proposalitem_tree").find(".jstree-clicked").text();
                let group_number = text.split("-")[0];
                $("#aproposal_group").val(group_number);
                $("#aproposalgroup").val(group_number);
                $("#create_proposalitem_treeview").modal('hide');
            }
        } else {
            $("#create_proposalitem_treeview").modal('hide');
        }
    });

    // proposalitem tree ok
    $("#proposalitem_tree_ok").click(function() {
        let selected_node_id = $("#proposalitem_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes("proposalitem")) {
                let id = selected_node_id.replace("_anchor", "").replace("proposalitem-", "");
                $("#proposalitem_treeview").modal('hide');
                getProposalItemById(id);
            } else {
                toastr.error('Please select proposal item. This is proposal group...');
            }
        } else {
            $("#proposalitem_treeview").modal('hide');
        }
    });

    // double click event to jstree
    $('#proposalitem_tree').on('dblclick.jstree', function() {
        let selected_node_id = $("#proposalitem_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes("proposalitem")) {
                let id = selected_node_id.replace("_anchor", "").replace("proposalitem-", "");
                $("#proposalitem_treeview").modal('hide');
                getProposalItemById(id);
            }
        } else {
            $("#proposalitem_treeview").modal('hide');
        }
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function() {
        let key = $('#search_tree_key').val();
        $("#proposalitem_tree").jstree(true).search(key);
    });
    $(document).on('click', '#clear_search', function() {
        $('#search_tree_key').val('');
        $("#proposalitem_tree").jstree(true).clear_search();
    });
    $(document).on('click', '#create_search_tree', function() {
        let key = $('#create_search_tree_key').val();
        $("#create_proposalitem_tree").jstree(true).search(key);
    });
    $(document).on('click', '#create_clear_search', function() {
        $('#create_search_tree_key').val('');
        $("#create_proposalitem_tree").jstree(true).clear_search();
    });


    $(document).ready(function() {
        // searchable variables, pre-defined calculations for formula
        {{--var proposalitemTreeData = JSON.parse('{!! $proposalitem_tree !!}');--}}
        let proposalItemTreeData = JSON.parse(@json($proposalitem_tree));
        $('#proposalitem_tree').jstree({
            'core': {
                'data': proposalItemTreeData
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins' : [ "themes", "search" ]
        });

        let proposalGroupTreeData = JSON.parse(@json($proposalgroup_tree));
        $('#create_proposalitem_tree').jstree({
            'core': {
                'data': proposalGroupTreeData
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins' : [ "themes", "search" ]
        });
    });

</script>