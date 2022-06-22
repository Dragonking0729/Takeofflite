<script>
    function getCostgroupById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('costgroup/get_costgroup_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function(data) {
                someBlock.preloader('remove');
                $('#default_section').html(data);
                $("#treeview").modal('hide');
                document.getElementById("add_costgroup_button").dataset.page=id;
            }
        });
    }

    // show tree
    $(document).on("click", ".open_folder", function() {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('costgroup/get_costgroup_tree') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function(data) {
                someBlock.preloader('remove');
                $('#tree').jstree({
                    'core': {
                        'data': data
                    },
                    'search': {
                        'show_only_matches': true,
                    },
                    'plugins' : [ "themes", "search" ]
                });

                $('#tree').jstree(true).settings.core.data = data;
                $('#tree').jstree(true).refresh();
            }
        });
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function() {
        let key = $('#search_tree_key').val();
        $("#tree").jstree(true).search(key);
    });
    $(document).on('click', '#clear_search', function() {
        $("#tree").jstree(true).clear_search();
        $('#search_tree_key').val('');
    });

    // double click event to jstree
    $('#tree').on('dblclick.jstree', function() {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes('anchor')) {
                let id = selected_node_id.replace("_anchor", "");
                getCostgroupById(id);
            }
        }
    });

    // tree node ok
    $("#tree_ok").click(function() {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            if (selected_node_id.includes('folder')) {
                // nothing
                let id = selected_node_id.replace("_anchor", "").replace("folder-", "");
                getCostgroupById(id);
            } else {
                let id = selected_node_id.replace("_anchor", "").replace("group-", "");
                getCostgroupById(id);
            }
        } else {
            $("#treeview").modal('hide');
        }
    });
</script>