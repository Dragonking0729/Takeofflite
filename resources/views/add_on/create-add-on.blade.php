<div class="card-body" id="add_section" style="display: none">
    <form action="{{ route('add_on.store') }}" method="POST" id="add_addons">
        @csrf
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group row">
                    <label for="aadd_on_name" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-4 my-auto">
                        <input type="text" class="form-control" id="aadd_on_name" name="aadd_on_name"
                               placeholder="Enter name" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="aadd_on_calc_method" class="col-sm-2 col-form-label">Calculation Method</label>
                    <div class="col-sm-4">
                        <select name="aadd_on_calc_method" class="form-control" id="aadd_on_calc_method">
                            <option value="%">%</option>
                            <option value="$" disabled>$</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="aadd_on_calc_value" class="col-sm-2 col-form-label">Calculation Value</label>
                    <div class="col-sm-4 my-auto">
                        <input type="number" class="form-control" id="aadd_on_calc_value" name="aadd_on_calc_value"
                               required>
                    </div>
                </div>

                <div class="form-group row" id="add_on_category">
                    <label for="aadd_on_calc_category" class="col-sm-2 col-form-label">Calculation Category</label>
                    <div class="col-sm-4">
                        <select name="aadd_on_calc_category" class="form-control" id="aadd_on_calc_category">
                            <option value="Labor">
                                Labor
                            </option>
                            <option value="Material">
                                Material
                            </option>
                            <option value="Subcontract">
                                Subcontract
                            </option>
                            <option value="Estimate Total">
                                Estimate Total
                            </option>
                        </select>
                    </div>
                </div>
            </div>


        </div>

        <div class="card-footer bg-white border-top-0 text-center">
            <button type="button" class="btn btn-outline-secondary add_cancel"><i class="fa fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-outline-secondary"
                    id="add_addons_button" data-page="{{$add_ons->currentPage()}}">
                <i class="fa fa-check"></i> Ok
            </button>
        </div>
    </form>
</div>