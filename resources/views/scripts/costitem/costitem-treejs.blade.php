<script>
    function getCostItemById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('costitem/get_costitem_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
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
                document.getElementById("add_costitem_button").dataset.page=id;
                showHideConversionFactorArea();
            }
        });
    }

    // create_cost_item tree ok
    $(document).on("click", "#create_costitem_tree_ok", function() {
        let selected_group_id = $("#create_costitem_tree").find(".jstree-clicked").attr('aria-level');
        if (selected_group_id) {
            if (selected_group_id === '2') {
                let text = $("#create_costitem_tree").find(".jstree-clicked").text();
                let cost_group_number = text.split("-")[0];
                $("#acost_group").val(cost_group_number);
                $("#acostgroup").val(cost_group_number);
                $("#create_costitem_treeview").modal('hide');
            }
        } else {
            $("#create_costitem_treeview").modal('hide');
        }
    });

    // double click event to jstree
    $("#create_costitem_tree").on('dblclick.jstree', function() {
        let selected_group_id = $("#create_costitem_tree").find(".jstree-clicked").attr('aria-level');
        if (selected_group_id) {
            if (selected_group_id === '2') {
                let text = $("#create_costitem_tree").find(".jstree-clicked").text();
                let cost_group_number = text.split("-")[0];
                $("#acostgroup").val(cost_group_number);
                $("#acost_group").val(cost_group_number);
                $("#create_costitem_treeview").modal('hide');
            }
        } else {
            $("#create_costitem_treeview").modal('hide');
        }
    });

    // costitem tree ok
    $("#costitem_tree_ok").click(function() {
        let selected_node_id = $("#costitem_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes("costitem")) {
                let id = selected_node_id.replace("_anchor", "").replace("costitem-", "");
                $("#costitem_treeview").modal('hide');
                getCostItemById(id);
            } else {
                toastr.error('Please select cost item. This is cost group...');
            }
        } else {
            $("#costitem_treeview").modal('hide');
        }
    });

    // double click event to jstree
    $('#costitem_tree').on('dblclick.jstree', function() {
        let selected_node_id = $("#costitem_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes("costitem")) {
                let id = selected_node_id.replace("_anchor", "").replace("costitem-", "");
                $("#costitem_treeview").modal('hide');
                getCostItemById(id);
            }
        } else {
            $("#costitem_treeview").modal('hide');
        }
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function() {
        let key = $('#search_tree_key').val();
        $("#costitem_tree").jstree(true).search(key);
    });
    $(document).on('click', '#clear_search', function() {
        $('#search_tree_key').val('');
        $("#costitem_tree").jstree(true).clear_search();
    });
    $(document).on('click', '#create_search_tree', function() {
        let key = $('#create_search_tree_key').val();
        $("#create_costitem_tree").jstree(true).search(key);
    });
    $(document).on('click', '#create_clear_search', function() {
        $('#create_search_tree_key').val('');
        $("#create_costitem_tree").jstree(true).clear_search();
    });


    $(document).ready(function() {
        // searchable variables, pre-defined calculations for formula
        $('.select2-pre-defined-calc').select2();
        $('.select2-variables').select2();
        $('.select2-functions').select2();
        $('.select2-invoice').select2();
        $('.select2-proposal').select2();
        {{--var costItemTreeData = JSON.parse('{!! $costitem_tree !!}');--}}
        let costItemTreeData = JSON.parse(@json($costitem_tree));
        $('#costitem_tree').jstree({
            'core': {
                'data': costItemTreeData
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins' : [ "themes", "search" ]
        });

        let costGroupTreeData = JSON.parse(@json($costgroup_tree));
        $('#create_costitem_tree').jstree({
            'core': {
                'data': costGroupTreeData
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins' : [ "themes", "search" ]
        });
        showHideConversionFactorArea();
    });

</script>