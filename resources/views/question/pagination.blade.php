<div class="row">
    @if ($question->count())
        <div class="col-sm-10">
            <div class="form-group row">

                <label for="question" class="col-sm-2 col-form-label">Question</label>
                <div class="col-sm-4 my-auto">
                    <input type="text" class="form-control" id="question" name="question"
                           value="{{ $question[0]->question }}" required>
                </div>
                <div class="col-sm-1">
                    <button class="btn btn-outline-secondary open_question_tree" data-toggle="modal"
                            data-target="#question_tree">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="btn-group col-sm-2" id="next_prev_section">
                    <a href="{{ $question->previousPageUrl() }}"
                       class="{{ $question->currentPage() == 1 ? 'btn btn-outline-secondary prev mr-1 disabled' :
                   'btn btn-outline-secondary prev mr-1' }}">
                        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                    </a>

                    <a href="{{ $question->nextPageUrl() }}"
                       class="{{ $question->total() == $question->currentPage() ?
                   'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}">
                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                    </a>
                </div>

            </div>

            <div class="form-group row" id="desc_line">
                <label for="desc" class="col-sm-2 col-form-label">Help Prompt</label>
                <div class="col-sm-8">
                    <textarea class="form-control" id="desc" rows="2">{{ $question[0]->notes }}</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-2 col-form-label">Question Type</label>
                <div class="col-8">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="question_type"
                               id="q_type_standard" value="standard"
                                {{ $question[0]->type === 'standard' ? 'checked' : '' }}>
                        <label class="form-check-label" for="q_type_standard">
                            <img src="{{asset('icons/dsq_1.png')}}" width="25">
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="question_type" id="q_type_total"
                               value="total" {{ $question[0]->type === 'total' ? 'checked' : '' }}>
                        <label class="form-check-label" for="q_type_total">
                            <img src="{{asset('icons/dsq_2.png')}}" width="25">
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="question_type"
                               id="q_type_category" value="category"
                                {{ $question[0]->type === 'category' ? 'checked' : '' }}>
                        <label class="form-check-label" for="q_type_category">
                            <img src="{{asset('icons/dsq_3.png')}}" width="25">
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="question_type" id="q_type_tricky"
                               value="tricky" {{ $question[0]->type === 'tricky' ? 'checked' : '' }}>
                        <label class="form-check-label" for="q_type_tricky">
                            <img src="{{asset('icons/dsq_4.png')}}" width="25">
                        </label>
                    </div>
                </div>

            </div>

        </div>

    @else
        <div class="col-sm-10 d-flex justify-content-center align-items-center">
            No data found
        </div>
    @endif

    <div class="col-sm-2 d-flex flex-column">
        <div id="default_ctrl_btn_group">
            <button type="button" class="btn btn-outline-secondary add mt-2">Add Question</button>
            @if ($question->count())
                <button type="button" class="btn btn-outline-secondary delete mt-2"
                        id="delete" data-id="{{ $question[0]->id }}" data-page="{{$question->currentPage()}}">
                    Delete
                </button>
            @endif
        </div>

    </div>
</div>

<div class="card-footer bg-white border-top-0 text-center" id="card_footer">
    @if ($question->count())
        <button type="button" class="btn btn-outline-secondary"
                id="update_button" data-id="{{ $question[0]->id }}"
                data-page="{{ $question->currentPage() }}" disabled>Save
        </button>
    @endif
    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">Close</a>
</div>
