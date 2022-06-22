<!-- Question tree -->
<div class="modal fade" id="question_tree">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Choose Question</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="d-flex py-1">
                    <input class="form-control form-control-sm mr-1" name="search_tree_key" id="search_tree_key">
                    <button type="button" class="btn btn-outline-secondary btn-sm mr-1 search_tree" id="search_tree"></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm clear_search" id="clear_search"></button>
                </div>
                <div id="question_tree_modal"></div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="tree_ok">Ok</button>
            </div>

        </div>
    </div>
</div>