<div class="row">
    @if ($invoice_text->count())
        <div class="col-sm-10">
            <div class="form-group row">

                <label for="question" class="col-sm-2 col-form-label">Title</label>
                <div class="col-sm-4 my-auto">
                    <input type="text" class="form-control" id="title" name="title"
                           value="{{ $invoice_text[0]->title }}" required>
                </div>
                <div class="col-sm-1">
                    <button class="btn btn-outline-secondary open_invoice_text_tree" data-toggle="modal"
                            data-target="#invoice_text_tree">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="btn-group col-sm-2" id="next_prev_section">
                    <a href="{{ $invoice_text->previousPageUrl() }}"
                       class="{{ $invoice_text->currentPage() == 1 ? 'btn btn-outline-secondary prev mr-1 disabled' :
                   'btn btn-outline-secondary prev mr-1' }}">
                        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                    </a>

                    <a href="{{ $invoice_text->nextPageUrl() }}"
                       class="{{ $invoice_text->total() == $invoice_text->currentPage() ?
                   'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}">
                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                    </a>
                </div>

            </div>

            <div class="form-group row" id="text_line">
                <label for="text" class="col-sm-2 col-form-label">Text</label>
                <div class="col-sm-8">
                    <div id="text" class="summernote">{!!$invoice_text[0]->text!!}</div>
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
            <button type="button" class="btn btn-outline-secondary add mt-2">Add Text</button>
            @if ($invoice_text->count())
                <button type="button" class="btn btn-outline-secondary delete mt-2"
                        id="delete" data-id="{{ $invoice_text[0]->id }}" data-page="{{$invoice_text->currentPage()}}">
                    Delete
                </button>
            @endif
        </div>

    </div>
</div>

<div class="card-footer bg-white border-top-0 text-center" id="card_footer">
    @if ($invoice_text->count())
        <button type="button" class="btn btn-outline-secondary"
                id="update_button" data-id="{{ $invoice_text[0]->id }}"
                data-page="{{ $invoice_text->currentPage() }}" disabled>Save
        </button>
    @endif
    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">Close</a>
</div>
