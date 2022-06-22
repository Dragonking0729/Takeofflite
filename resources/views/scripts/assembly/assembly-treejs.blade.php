<script>
    var _token = $("input[name=_token]").val();

    // refresh page after ajax call
    function updatePageByAssemblyId(assembly, items) {
        // update assembly section
        $("#assembly_number").val(assembly.assembly_number);
        $("#assembly_desc").text(assembly.assembly_desc);
        if (assembly.is_folder) {
            $("#open_assembly_costitem").prop('disabled', true);
            $("#folder").prop('checked', true);
        } else {
            $("#folder").prop('checked', false);
            $("#open_assembly_costitem").prop('disabled', false);
        }

        //  update control section
        $(".delete").attr('data-id', assembly.id);
        $(".ok").attr('data-id', assembly.id);
        $(".renumber").attr('data-id', assembly.id);

        // update item table section
        var itemTblHTML = '';
        if (items.length) {
            items.map(item => {
                itemTblHTML += `<tr>
                <td>${item.item_cost_group_number}</td>
                <td>${item.item_number}</td>
                <td>${item.item_desc}</td>
                <td><a href="javascript:;" class="text-danger delete-item" data-id="` + item.id + `"><i class="fa fa-trash"></i></a></td>
                </tr>`;
            });
        }
        $("#item_tbl_body").html(itemTblHTML);

    }


    function getAssemblyById(id) {
        initFlag();
        someBlock.preloader();
        $.ajax({
            url: "{{ url('assembly/get_assembly_by_id') }}",
            method: "POST",
            data: {
                _token: _token,
                page: id
            },
            success: function(res) {
                someBlock.preloader('remove');
                originItemTblHtml = '';
                updated_items = [];
                added_items = [];
                $('#default_section').html(res);
                $("#assembly_tree_modal").modal('hide');
            }
        });
    }


    // open assembly cost item tree
    $(document).on('click', '#open_assembly_costitem', function() {
        $('#assembly_costitem_tree').jstree(true).uncheck_all();
    });

    // assembly list ok
    $("#assembly_tree_ok").click(function() {
        let selected_node_id = $("#assembly_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            getAssemblyById(id);
        } else {
            $("#assembly_tree_modal").modal('hide');
        }
    });

    // double click event to jstree
    $('#assembly_tree').on('dblclick.jstree', function() {
        let selected_node_id = $("#assembly_tree").find(".jstree-clicked").attr('id');
        if (selected_node_id) {
            let id = selected_node_id.replace("_anchor", "");
            getAssemblyById(id);
        } else {
            $("#assembly_tree_modal").modal('hide');
        }
    });

    // item list tree ok
    $("#assembly_costitem_tree_ok").click(function() {
        let selectedNodes = $('#assembly_costitem_tree').jstree("get_bottom_checked", true); // get_top_checked, get_selected, get_undetermined, get_bottom_checked
        if (addBtnFlag) {
            let order = 0;
            $.each(selectedNodes, function() {
                if (this.id.includes('costitem')) {
                    order++;
                    let temp = {
                        id: this.id,
                        group_number: this.parent.split('-')[1],
                        item_number: this.id.split('-')[1],
                        item_desc: this.text.split('-')[1],
                        formula_body: [],
                        item_order: order
                    };
                    let checkExist = added_items.some(el => el.id === temp.id);
                    if (!checkExist) {
                        added_items.push(temp);
                    }
                }
            });
            updateItemTbl(added_items);
        } else {
            let assembly_number = document.getElementById('assembly_number').value;
            let order = $("#item_tbl_body tr").length;
            $.each(selectedNodes, function() {
                if (this.id.includes('costitem')) {
                    order++;
                    let removedString = this.text.split('-')[0];
                    let desc = this.text.replace(removedString, '').replace('-', '');
                    let temp = {
                        id: this.id,
                        assembly_number: assembly_number,
                        group_number: this.parent.split('-')[1],
                        item_number: this.id.split('-')[1],
                        item_desc: desc,
                        formula_body: [],
                        item_order: order
                    };
                    let checkExist = updated_items.some(el => el.id === temp.id);
                    if (!checkExist) {
                        updated_items.push(temp);
                    }
                }
            });
            updateBtnFlag = true;
            updateItemTbl(updated_items);
        }

    });

    // remove from updated/added items
    $(document).on('click', '.delete-temp-item', function() {
        let removeId = $(this).attr('data-id');
        if (addBtnFlag) {
            added_items = added_items.filter(item => {
                return item.id !== removeId;
            });
            updateItemTbl(added_items);
        } else {
            updated_items = updated_items.filter(item => {
                return item.id !== removeId;
            });
            updateItemTbl(updated_items);
        }
    });


    // search & clear tree
    $(document).on('click', '#search_tree', function() {
        let key = $('#search_tree_key').val();
        $("#assembly_tree").jstree(true).search(key);
    });
    $(document).on('click', '#clear_search', function() {
        $('#search_tree_key').val('');
        $("#assembly_tree").jstree(true).clear_search();
    });
    $(document).on('click', '#a_search_tree', function() {
        let key = $('#a_search_tree_key').val();
        $("#assembly_costitem_tree").jstree(true).search(key);
    });
    $(document).on('click', '#a_clear_search', function() {
        $('#a_search_tree_key').val('');
        $("#assembly_costitem_tree").jstree(true).clear_search();
    });


    // initial assembly list & item list tree
    $(document).ready(function() {
        // searchable variables, operators, functions for formula
        $('.select2-variables').select2();
        $('.select2-pre-defined-calc').select2();
        $('.select2-functions').select2();

        let assemblyList = @json($assembly_tree_data);
        $('#assembly_tree').jstree({
            'core': {
                'data': assemblyList
            },
            'search': {
                'show_only_matches': true,
            },
            'plugins' : [ "themes", "search" ]
        });


        let itemList = @json($costitem_tree_data);
        $('#assembly_costitem_tree').jstree({
            'plugins': ['themes', 'checkbox', 'ui', 'search'],
            'core': {
                'check_callback': true,
                'data': itemList
            },
            'search': {
                'show_only_matches': true,
            },
            'checkbox': {
                'keep_selected_style': false
            }
        });
    });

    @if(session('success'))
    toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
    toastr.error("{{ session('error') }}");
    @endif

</script>