<!-- create new question modal -->
<div class="modal fade" id="create_new_question_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">New Question</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="form-group row">
                    <label for="new_question" class="col-sm-3 col-form-label">Question </label>
                    <div class="col-sm-9 my-auto">
                        <input type="text" class="form-control" id="new_question" name="new_question">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="help_notes" class="col-sm-3 col-form-label">Help Prompt </label>
                    <div class="col-sm-9 my-auto">
                        <textarea class="form-control" id="help_notes" name="help_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-3 col-form-label">Question Type</label>
                    <div class="col-9 my-auto">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="question_type" id="q_type_standard"
                                   value="standard" checked>
                            <label class="form-check-label" for="q_type_standard">
                                <img src="{{asset('icons/dsq_1.png')}}" alt="standard" width="25">
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="question_type" id="q_type_total"
                                   value="total">
                            <label class="form-check-label" for="q_type_total">
                                <img src="{{asset('icons/dsq_2.png')}}" alt="total" width="25">
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="question_type" id="q_type_category"
                                   value="category">
                            <label class="form-check-label" for="q_type_category">
                                <img src="{{asset('icons/dsq_3.png')}}" alt="category" width="25">
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="question_type" id="q_type_tricky"
                                   value="tricky">
                            <label class="form-check-label" for="q_type_tricky">
                                <img src="{{asset('icons/dsq_4.png')}}" alt="tricky" width="25">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="create_new_question">Create</button>
            </div>

        </div>
    </div>
</div>