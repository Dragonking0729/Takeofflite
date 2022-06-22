<script>
    function getAddOnById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('add_on/get_addon_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function (data) {
                someBlock.preloader('remove');
                document.getElementById('add_addons_button').dataset.page = id;
                $('#default_section').html(data);
            }
        });
    }

    const $_add_on_tree_modal = $("#add_on_tree_modal");
    const $_add_on_tree = $("#add_on_tree");

    // show tree
    $(document).on("click", ".open_add_on_tree", function () {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('add_on/get_addon_tree') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                $_add_on_tree_modal.jstree({
                    'core': {
                        'data': data
                    },
                    'search': {
                        'show_only_matches': true,
                    },
                    'plugins': ["themes", "search"]
                });
                $_add_on_tree_modal.jstree(true).settings.core.data = data;
                $_add_on_tree_modal.jstree(true).refresh();
                $('#search_tree_key').val('');
            }
        });
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function () {
        let key = $('#search_tree_key').val();
        $_add_on_tree_modal.jstree(true).search(key);
    });

    $(document).on('click', '#clear_search', function () {
        $_add_on_tree_modal.jstree(true).clear_search();
        $('#search_tree_key').val('');
    });

    // tree node ok
    $("#tree_ok").click(function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_add_on_tree.modal('hide');
            getAddOnById(id);
        } else {
            $_add_on_tree.modal('hide');
        }
    });

    // double click event to jstree
    $_add_on_tree_modal.on('dblclick.jstree', function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_add_on_tree.modal('hide');
            getAddOnById(id);
        } else {
            $_add_on_tree.modal('hide');
        }
    });
</script>