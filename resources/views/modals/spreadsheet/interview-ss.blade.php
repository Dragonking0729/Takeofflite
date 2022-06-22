<!-- interview costitem formula modal -->
<div class="modal fade" id="formula_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="item_interview_modal_title">Formula</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <input type="hidden" id="ss_item_id">
                <div class="text-center font-weight-bold" style="color: #046a5c;">
                    <div class="form-group row">
                        <label for="parsed_formula_div" class="col-sm-2 text-right my-auto">Formula: </label>
                        <div class="col-sm-10" id="parsed_formula_div">

                        </div>
                    </div>
                </div>
                <div id="formula_body">
                    <div class="form-group row">
                        <label for="temp_id" class="col-sm-4 text-right">Label </label>
                        <div class="col-sm-8">
                            <div class="d-flex my-auto">
                                <input type="text" class="form-control" id="temp_id" name="temp_id">
                                <i class="get_measuring_by_interview material-icons" id="measure_temp_id" data-id="temp_id">
                                    architecture
                                </i>
                            </div>
                            <div class="help_question"><i class="fa fa-question-circle"></i> Help</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="cancelInterview()">Done</button>
                <button type="button" class="btn btn-success" id="save_interview">Save</button>
            </div>

        </div>
    </div>
</div>