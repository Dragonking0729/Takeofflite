<script>
    function getQuestionById(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('question/get_question_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function (data) {
                someBlock.preloader('remove');
                document.getElementById('add_question_button').dataset.page = id;
                $('#default_section').html(data);
            }
        });
    }

    const $_question_tree_modal = $("#question_tree_modal");
    const $_question_tree = $("#question_tree");

    // show tree
    $(document).on("click", ".open_question_tree", function () {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('question/get_question_tree') }}",
            method: "POST",
            data: {
                _token: _token
            },
            success: function (data) {
                someBlock.preloader('remove');
                $_question_tree_modal.jstree({
                    'core': {
                        'data': data
                    },
                    'search': {
                        'show_only_matches': true,
                    },
                    'plugins': ["themes", "search"]
                });
                $_question_tree_modal.jstree(true).settings.core.data = data;
                $_question_tree_modal.jstree(true).refresh();
                $('#search_tree_key').val('');
            }
        });
    });

    // search & clear tree
    $(document).on('click', '#search_tree', function () {
        let key = $('#search_tree_key').val();
        $_question_tree_modal.jstree(true).search(key);
    });

    $(document).on('click', '#clear_search', function () {
        $_question_tree_modal.jstree(true).clear_search();
        $('#search_tree_key').val('');
    });

    // tree node ok
    $("#tree_ok").click(function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_question_tree.modal('hide');
            getQuestionById(id);
        } else {
            $_question_tree.modal('hide');
        }
    });

    // double click event to jstree
    $_question_tree_modal.on('dblclick.jstree', function () {
        let selected_node_id = $(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            $_question_tree.modal('hide');
            getQuestionById(id);
        } else {
            $_question_tree.modal('hide');
        }
    });
</script>