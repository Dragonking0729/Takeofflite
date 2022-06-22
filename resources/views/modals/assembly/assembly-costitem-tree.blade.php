<!-- assembly > cost group > cost item tree -->
<div class="modal fade" id="assembly_costitem_tree_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Item List</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="d-flex py-1">
                    <input class="form-control form-control-sm mr-1" name="a_search_tree_key" id="a_search_tree_key">
                    <button type="button" class="btn btn-outline-secondary btn-sm mr-1 search_tree" id="a_search_tree">
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm clear_search" id="a_clear_search">
                    </button>
                </div>
                <div class="d-flex justify-content-around py-1">
                    <button type="button" title="Expand All" class="btn btn-outline-secondary btn-sm tree_expand"
                            onclick="$('#assembly_costitem_tree').jstree('open_all');"></button>
                    <button type="button" title="Collapse All" class="btn btn-outline-secondary btn-sm tree_collapse"
                            onclick="$('#assembly_costitem_tree').jstree('close_all');"></button>
                </div>
                <div id="assembly_costitem_tree"></div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="assembly_costitem_tree_ok">Ok</button>
            </div>

        </div>
    </div>
</div>