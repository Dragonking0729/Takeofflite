<script>
    function getProposalTextById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('proposal_text/get_proposal_text_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function (data) {
                someBlock.preloader('remove');
                document.getElementById('add_proposal_text_button').dataset.page = id;
                $('#default_section').html(data);
                $('#text').summernote({
                    height: 100,
                });
            }
        });
    }

    const $_proposal_text_tree_modal = $("#proposal_text_tree_modal");
    const $_proposal_text_tree = $("#proposal_text_tree");

    // show tree
    $(document).on("click", ".open_proposal_text_tree", function () {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('proposal_text/get_proposal_text_tree') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                $_proposal_text_tree_modal.jstree({
                    'core': {
                        'data': data
                    },
                    'search': {
                        'show_only_matches': true,
                    },
                    'plugins': ["themes", "search"]
                });
                $_proposal_text_tree_modal.jstree(true).settings.core.data = data;
                $_proposal_text_tree_modal.jstree(true).refresh();
                $('#search_tree_key').val('');
            }
        });
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function () {
        let key = $('#search_tree_key').val();
        $_proposal_text_tree_modal.jstree(true).search(key);
    });

    $(document).on('click', '#clear_search', function () {
        $_proposal_text_tree_modal.jstree(true).clear_search();
        $('#search_tree_key').val('');
    });

    // tree node ok
    $("#tree_ok").click(function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_proposal_text_tree.modal('hide');
            getProposalTextById(id);
        } else {
            $_proposal_text_tree.modal('hide');
        }
    });

    // double click event to jstree
    $_proposal_text_tree_modal.on('dblclick.jstree', function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_proposal_text_tree.modal('hide');
            getProposalTextById(id);
        } else {
            $_proposal_text_tree.modal('hide');
        }
    });
</script>