<script>
    function getFormulaById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('formula/get_formula_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function (res) {
                someBlock.preloader('remove');
                tags = res.data.formula_params;
                $('#default_section').html(res.data.view_data);
                $('.select2-pre-defined-calc').select2();
                $('.select2-variables').select2();
                $('.select2-functions').select2();
            }
        });
    }

    const $_stored_formula_tree_modal = $("#stored_formula_tree_modal");
    const $_stored_formula_tree = $("#stored_formula_tree");

    // show tree
    $(document).on("click", ".open_tree", function () {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('formula/get_stored_formula_tree') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                $_stored_formula_tree_modal.jstree({
                    'core': {
                        'data': data
                    },
                    'search': {
                        'show_only_matches': true,
                    },
                    'plugins': ["themes", "search"]
                });
                $_stored_formula_tree_modal.jstree(true).settings.core.data = data;
                $_stored_formula_tree_modal.jstree(true).refresh();
                $('#search_tree_key').val('');
            }
        });
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function () {
        let key = $('#search_tree_key').val();
        $_stored_formula_tree_modal.jstree(true).search(key);
    });

    $(document).on('click', '#clear_search', function () {
        $_stored_formula_tree_modal.jstree(true).clear_search();
        $('#search_tree_key').val('');
    });

    // tree node ok
    $("#tree_ok").click(function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_stored_formula_tree.modal('hide');
            getFormulaById(id);
        } else {
            $_stored_formula_tree.modal('hide');
        }
    });

    // double click event to jstree
    $_stored_formula_tree_modal.on('dblclick.jstree', function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_stored_formula_tree.modal('hide');
            getFormulaById(id);
        } else {
            $_stored_formula_tree.modal('hide');
        }
    });

    $(document).ready(function () {
        $('.select2-pre-defined-calc').select2();
        $('.select2-variables').select2();
        $('.select2-functions').select2();
    });
</script>