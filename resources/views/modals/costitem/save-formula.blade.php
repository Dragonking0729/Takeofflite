<!-- save formula modal -->
<div class="modal fade" id="save_formula_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Save as predefined calculation</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="form-group row">
                    <label for="calculation_name" class="col-sm-4 col-form-label">Calculation Name:&nbsp;</label>
                    <div class="col-sm-8 my-auto">
                        <input type="text" class="form-control" id="calculation_name" name="calculation_name">
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="close_store_formula_modal">Cancel</button>
                <button type="button" class="btn btn-success" id="save_formula">Save</button>
            </div>

        </div>
    </div>
</div>