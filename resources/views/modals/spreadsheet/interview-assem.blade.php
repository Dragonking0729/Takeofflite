<!-- interview assembly formula modal -->
<div class="modal fade" id="assembly_formula_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="assembly_interview_modal_title">Formula</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <input type="hidden" id="ss_assembly_item_id">
                <div id="assembly_formula_body">
                    <div class="form-group row">
                        <label for="temp_id1" class="col-sm-4 text-right">Label </label>
                        <div class="col-sm-8">
                            <div class="d-flex my-auto">
                                <input type="text" class="form-control" id="temp_id1" name="temp_id1">
                                <i class="get_measuring_by_assembly get_measuring_other" id="measure_temp_id1"
                                   data-id="temp_id1">
                                </i>
                            </div>
                            <div class="help_question"><i class="fa fa-question-circle"></i> Help</div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="ss_interview_assem_location" class="col-sm-4">Location</label>
                    <div class="col-sm-8">
                        <div class="d-flex my-auto">
                            <input type="text" class="form-control" id="ss_interview_assem_location"
                                   name="ss_interview_assem_location">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="cancelInterview()">Done
                </button>
                <button type="button" class="btn btn-success" id="save_assembly_interview">Save</button>
            </div>

        </div>
    </div>
</div>