<div class="row">
    @if ($add_ons->count())
        <div class="col-sm-10">
            <div class="form-group row">

                <label for="add_on_name" class="col-sm-2 col-form-label">Name</label>
                <div class="col-sm-4 my-auto">
                    <input type="text" class="form-control" id="add_on_name" name="add_on_name"
                           value="{{ $add_ons[0]->addon_name }}" required>
                </div>
                <div class="col-sm-1">
                    <button class="btn btn-outline-secondary open_add_on_tree" data-toggle="modal"
                            data-target="#add_on_tree">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button>
                </div>

            </div>

            <div class="form-group row">
                <label for="add_on_calc_method" class="col-sm-2 col-form-label">Calculation Method</label>
                <div class="col-sm-4">
                    <select name="add_on_calc_method" class="form-control" id="add_on_calc_method">
                        <option value="%" {{$add_ons[0]->addon_method == "%" ? 'selected' : ''}}>%</option>
                        <option value="$" {{$add_ons[0]->addon_method == "$" ? 'selected' : ''}} disabled>$</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="add_on_calc_value" class="col-sm-2 col-form-label">Calculation Value</label>
                <div class="col-sm-4 my-auto">
                    <input type="number" class="form-control" id="add_on_calc_value" name="add_on_calc_value"
                           value="{{ $add_ons[0]->addon_value }}" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="add_on_calc_category" class="col-sm-2 col-form-label">Calculation Category</label>
                <div class="col-sm-4">
                    <select name="add_on_calc_category" class="form-control"
                            id="add_on_calc_category">
                        <option value="Labor" {{$add_ons[0]->addon_category == "Labor" ? 'selected' : ''}}>
                            Labor
                        </option>
                        <option value="Material" {{$add_ons[0]->addon_category == "Material" ? 'selected' : ''}}>
                            Material
                        </option>
                        <option value="Subcontract" {{$add_ons[0]->addon_category == "Subcontract" ? 'selected' : ''}}>
                            Subcontract
                        </option>
                        <option value="Estimate Total" {{$add_ons[0]->addon_category == "Estimate Total" ? 'selected' : ''}}>
                            Estimate Total
                        </option>
                    </select>
                </div>
            </div>

        </div>

    @else
        <div class="col-sm-10 d-flex justify-content-center align-items-center">
            No data found
        </div>
    @endif

    <div class="col-sm-2 d-flex flex-column">
        <div class="btn-group" id="next_prev_section">
            <a href="{{ $add_ons->previousPageUrl() }}"
               class="{{ $add_ons->currentPage() == 1 ? 'btn btn-outline-secondary prev mr-1 disabled' :
                   'btn btn-outline-secondary prev mr-1' }}">
                <i class="fa fa-angle-double-left" aria-hidden="true"></i>
            </a>

            <a href="{{ $add_ons->nextPageUrl() }}"
               class="{{ $add_ons->total() == $add_ons->currentPage() ?
                   'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}">
                <i class="fa fa-angle-double-right" aria-hidden="true"></i>
            </a>
        </div>
        <div id="default_ctrl_btn_group">
            <button type="button" class="btn btn-outline-secondary add mt-2">Add add-ons</button>
            @if ($add_ons->count())
                <button type="button" class="btn btn-outline-secondary delete mt-2"
                        id="delete" data-id="{{ $add_ons[0]->id }}" data-page="{{$add_ons->currentPage()}}">
                    Delete
                </button>
            @endif
        </div>

    </div>
</div>

<div class="card-footer bg-white border-top-0 text-center" id="card_footer">
    @if ($add_ons->count())
        <button type="button" class="btn btn-outline-secondary"
                id="update_button" data-id="{{ $add_ons[0]->id }}"
                data-page="{{ $add_ons->currentPage() }}" disabled>Save
        </button>
    @endif
    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">Close</a>
</div>
