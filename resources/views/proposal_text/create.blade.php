<div class="card-body" id="add_section" style="display: none">
    <form action="{{ route('proposal_text.store') }}" method="POST" id="add_proposal_text">
        @csrf
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group row">
                    <label for="atitle" class="col-sm-4 col-form-label">Title</label>
                    <div class="col-sm-6 my-auto">
                        <input type="text" class="form-control" id="atitle" name="atitle"
                               placeholder="Enter title" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="atext" class="col-sm-4 col-form-label">Text</label>
                    <div class="col-sm-6">
                        <div id="atext" class="summernote"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 text-center">
            <button type="button" class="btn btn-outline-secondary add_cancel"><i class="fa fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-outline-secondary"
                    id="add_proposal_text_button" data-page="{{$proposal_text->currentPage()}}">
                <i class="fa fa-check"></i> Ok
            </button>
        </div>
    </form>
</div>