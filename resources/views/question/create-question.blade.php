<div class="card-body" id="add_section" style="display: none">
    <form action="{{ route('question.store') }}" method="POST" id="add_question">
        @csrf
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group row">
                    <label for="aquestion" class="col-sm-4 col-form-label">Question</label>
                    <div class="col-sm-6 my-auto">
                        <input type="text" class="form-control" id="aquestion" name="aquestion"
                               placeholder="Enter question" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="adesc" class="col-sm-4 col-form-label">Help Prompt</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="adesc" name="adesc" rows="2"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-4 col-form-label">Question Type</label>
                    <div class="col-6">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="add_question_type"
                                   id="add_q_type_standard"
                                   value="standard" checked>
                            <label class="form-check-label" for="add_q_type_standard">
                                <img src="{{asset('icons/dsq_1.png')}}" width="25">
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="add_question_type" id="add_q_type_total"
                                   value="total">
                            <label class="form-check-label" for="add_q_type_total">
                                <img src="{{asset('icons/dsq_2.png')}}" width="25">
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="add_question_type"
                                   id="add_q_type_category"
                                   value="category">
                            <label class="form-check-label" for="add_q_type_category">
                                <img src="{{asset('icons/dsq_3.png')}}" width="25">
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="add_question_type" id="add_q_type_tricky"
                                   value="tricky">
                            <label class="form-check-label" for="add_q_type_tricky">
                                <img src="{{asset('icons/dsq_4.png')}}" width="25">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 text-center">
            <button type="button" class="btn btn-outline-secondary add_cancel"><i class="fa fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-outline-secondary"
                    id="add_question_button" data-page="{{$question->currentPage()}}">
                <i class="fa fa-check"></i> Ok
            </button>
        </div>
    </form>
</div>