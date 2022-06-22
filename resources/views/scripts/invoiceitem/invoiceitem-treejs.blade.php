<script>
    function getInvoiceItemById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_item/get_invoiceitem_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function(res) {
                someBlock.preloader('remove');
                $('#default_invoice_item_section').html(res.data.view_data);
                document.getElementById("add_invoiceitem_button").dataset.page=id;
            }
        });
    }

    // create invoice item tree ok
    $(document).on("click", "#create_invoiceitem_tree_ok", function() {
        let selected_group_id = $("#create_invoiceitem_tree").find(".jstree-clicked").attr('aria-level');
        if (selected_group_id) {
            if (selected_group_id === '2') {
                let text = $("#create_invoiceitem_tree").find(".jstree-clicked").text();
                let group_number = text.split("-")[0];
                $("#ainvoice_group").val(group_number);
                $("#ainvoicegroup").val(group_number);
                $("#create_invoiceitem_treeview").modal('hide');
            }
        } else {
            $("#create_invoiceitem_treeview").modal('hide');
        }
    });

    // double click event to jstree
    $("#create_invoiceitem_tree").on('dblclick.jstree', function() {
        let selected_group_id = $("#create_invoiceitem_tree").find(".jstree-clicked").attr('aria-level');
        if (selected_group_id) {
            if (selected_group_id === '2') {
                let text = $("#create_invoiceitem_tree").find(".jstree-clicked").text();
                let group_number = text.split("-")[0];
                $("#ainvoice_group").val(group_number);
                $("#ainvoicegroup").val(group_number);
                $("#create_invoiceitem_treeview").modal('hide');
            }
        } else {
            $("#create_invoiceitem_treeview").modal('hide');
        }
    });

    // invoiceitem tree ok
    $("#invoiceitem_tree_ok").click(function() {
        let selected_node_id = $("#invoiceitem_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes("invoiceitem")) {
                let id = selected_node_id.replace("_anchor", "").replace("invoiceitem-", "");
                $("#invoiceitem_treeview").modal('hide');
                getInvoiceItemById(id);
            } else {
                toastr.error('Please select invoice item. This is invoice group...');
            }
        } else {
            $("#invoiceitem_treeview").modal('hide');
        }
    });

    // double click event to jstree
    $('#invoiceitem_tree').on('dblclick.jstree', function() {
        let selected_node_id = $("#invoiceitem_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes("invoiceitem")) {
                let id = selected_node_id.replace("_anchor", "").replace("invoiceitem-", "");
                $("#invoiceitem_treeview").modal('hide');
                getInvoiceItemById(id);
            }
        } else {
            $("#invoiceitem_treeview").modal('hide');
        }
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function() {
        let key = $('#search_tree_key').val();
        $("#invoiceitem_tree").jstree(true).search(key);
    });
    $(document).on('click', '#clear_search', function() {
        $('#search_tree_key').val('');
        $("#invoiceitem_tree").jstree(true).clear_search();
    });
    $(document).on('click', '#create_search_tree', function() {
        let key = $('#create_search_tree_key').val();
        $("#create_invoiceitem_tree").jstree(true).search(key);
    });
    $(document).on('click', '#create_clear_search', function() {
        $('#create_search_tree_key').val('');
        $("#create_invoiceitem_tree").jstree(true).clear_search();
    });


    $(document).ready(function() {
        // searchable variables, pre-defined calculations for formula
                {{--var invoiceitemTreeData = JSON.parse('{!! $invoiceitem_tree !!}');--}}
        let invoiceItemTreeData = JSON.parse(@json($invoiceitem_tree));
        $('#invoiceitem_tree').jstree({
            'core': {
                'data': invoiceItemTreeData
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins' : [ "themes", "search" ]
        });

        let invoiceGroupTreeData = JSON.parse(@json($invoicegroup_tree));
        $('#create_invoiceitem_tree').jstree({
            'core': {
                'data': invoiceGroupTreeData
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins' : [ "themes", "search" ]
        });
    });

</script>