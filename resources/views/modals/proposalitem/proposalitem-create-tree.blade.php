<div class="modal fade" id="create_proposalitem_treeview">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Choose Proposal group</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="d-flex py-1">
                    <input class="form-control form-control-sm mr-1" name="create_search_tree_key"
                           id="create_search_tree_key">
                    <button type="button" class="btn btn-outline-secondary btn-sm mr-1 search_tree"
                            id="create_search_tree"></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm clear_search"
                            id="create_clear_search"></button>
                </div>
                <div class="d-flex justify-content-around py-1">
                    <button type="button" title="Expand All" class="btn btn-outline-secondary btn-sm tree_expand"
                            onclick="$('#create_proposalitem_tree').jstree('open_all');"></button>
                    <button type="button" title="Collapse All" class="btn btn-outline-secondary btn-sm tree_collapse"
                            onclick="$('#create_proposalitem_tree').jstree('close_all');"></button>
                </div>
                <div id="create_proposalitem_tree"></div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="create_proposalitem_tree_ok">Ok</button>
            </div>

        </div>
    </div>
</div>