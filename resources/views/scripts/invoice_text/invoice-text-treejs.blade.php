<script>
    function getInvoiceTextById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_text/get_invoice_text_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function (data) {
                someBlock.preloader('remove');
                document.getElementById('add_invoice_text_button').dataset.page = id;
                $('#default_section').html(data);
                $('#text').summernote({
                    height: 100,
                });
            }
        });
    }

    const $_invoice_text_tree_modal = $("#invoice_text_tree_modal");
    const $_invoice_text_tree = $("#invoice_text_tree");

    // show tree
    $(document).on("click", ".open_invoice_text_tree", function () {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('invoice_text/get_invoice_text_tree') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                $_invoice_text_tree_modal.jstree({
                    'core': {
                        'data': data
                    },
                    'search': {
                        'show_only_matches': true,
                    },
                    'plugins': ["themes", "search"]
                });
                $_invoice_text_tree_modal.jstree(true).settings.core.data = data;
                $_invoice_text_tree_modal.jstree(true).refresh();
                $('#search_tree_key').val('');
            }
        });
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function () {
        let key = $('#search_tree_key').val();
        $_invoice_text_tree_modal.jstree(true).search(key);
    });

    $(document).on('click', '#clear_search', function () {
        $_invoice_text_tree_modal.jstree(true).clear_search();
        $('#search_tree_key').val('');
    });

    // tree node ok
    $("#tree_ok").click(function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_invoice_text_tree.modal('hide');
            getInvoiceTextById(id);
        } else {
            $_invoice_text_tree.modal('hide');
        }
    });

    // double click event to jstree
    $_invoice_text_tree_modal.on('dblclick.jstree', function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_invoice_text_tree.modal('hide');
            getInvoiceTextById(id);
        } else {
            $_invoice_text_tree.modal('hide');
        }
    });
</script>