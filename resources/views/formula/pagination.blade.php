<div class="row">
    @if (!$calculations->count())
        <div class="col-md-10 text-center" id="no_exist_stored_formula">
            No exists stored calculations
        </div>
    @endif

    <div class="col-sm-10" id="main_area" style="display: {{ $calculations->count() ? 'initial' : 'none' }};">

        <div class="form-group row">
            <label for="formula_name" class="col-sm-2 col-form-label">Formula name</label>
            <div class="col-sm-4 my-auto">
                <input type="text" class="form-control" id="formula_name" name="formula_name"
                    value="{{ $calculations->count() ? $calculations[0]->calculation_name : '' }}" required>
            </div>
            <div class="col-sm-1">
                <button class="btn btn-outline-secondary open_tree" data-toggle="modal"
                    data-target="#stored_formula_tree">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>
            </div>

            <div class="btn-group col-sm-2" id="next_prev_section">
                <a href="{{ $calculations->count() ? $calculations->previousPageUrl() : '#' }}"
                    class="{{ $calculations->count() && $calculations->currentPage() == 1 ? 'btn btn-outline-secondary prev mr-1 disabled' : 'btn btn-outline-secondary prev mr-1' }}">
                    <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                </a>

                <a href="{{ $calculations->count() ? $calculations->nextPageUrl() : '#' }}"
                    class="{{ $calculations->count() && $calculations->total() == $calculations->currentPage() ? 'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}">
                    <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                </a>
            </div>

        </div>

        @include('partials.update-formula-wizard')
    </div>


    <div class="col-sm-2 d-flex flex-column">
        <div id="default_ctrl_btn_group" class="d-flex flex-column">
            <button type="button" class="btn btn-outline-secondary add mt-2" id="add_formula">
                Add
            </button>
            @if ($calculations->count())
                <button type="button" class="btn btn-outline-secondary delete mt-2" id="delete"
                    data-id="{{ $calculations[0]->id }}" data-page="{{ $calculations->currentPage() }}">
                    Delete
                </button>
            @endif
        </div>

    </div>
</div>

<div class="card-footer bg-white border-top-0 text-center" id="card_footer">
    @if ($calculations->count())
        <button type="button" class="btn btn-outline-secondary" id="update_button"
            data-id="{{ $calculations[0]->id }}" data-page="{{ $calculations->currentPage() }}" disabled>Save
        </button>
    @endif
    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary" id="back">Close</a>

    <button type="button" class="btn btn-outline-secondary" id="cancel_add" style="display: none"
        data-page="{{ $calculations->currentPage() }}">Cancel
    </button>
    <button type="button" class="btn btn-outline-secondary" id="save_new_formula" style="display: none"
        data-page="{{ $calculations->currentPage() }}">Save
    </button>
</div>
