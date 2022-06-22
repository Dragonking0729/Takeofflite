<div id="default_cost_item_section">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-flex">
                    @if (!$cost_item->count())
                        <div class="col-md-10 text-center">
                            No data found
                        </div>
                    @else
                        <div class="col-sm-10 pl-0">
                            <div class="cost_item_block">
                                <div class="form-group required row" id="costgroup_div">
                                    <label for="costgroup" class="col-sm-3 col-form-label">This cost item belongs in
                                        cost
                                        group: </label>
                                    <div class="col-sm-3 my-auto">
                                        <input type="text" class="form-control" id="costgroup" name="costgroup"
                                            disabled required
                                            value="{{ $cost_item->count() ? $cost_item[0]->cost_group_number . '-' . $group_desc : '' }}"
                                            data-group_number="{{ $cost_item->count() ? $cost_item[0]->cost_group_number : '' }}">
                                    </div>
                                    <div class="col-sm-1 my-auto">
                                        <button class="btn btn-outline-secondary" data-toggle="modal"
                                            id="costitem_treeview_btn" data-target="#costitem_treeview">
                                            <i class="fa fa-bars" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group required row" id="item_number_group">
                                    <label for="item_number" class="col-sm-3 col-form-label">This item's number
                                        is </label>
                                    <div class="col-sm-2 my-auto">
                                        <input type="number" class="form-control" id="item_number" name="item_number"
                                            disabled
                                            value="{{ $cost_item->count() ? $cost_item[0]->item_number : '' }}">
                                    </div>
                                </div>
                                <div class="form-group required row">
                                    <label for="item_desc" class="col-sm-3 col-form-label">Item Description: </label>
                                    <div class="col-sm-9 my-auto">
                                        <textarea class="form-control" id="item_desc"
                                            rows="1">{{ $cost_item->count() ? $cost_item[0]->item_desc : '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group required row">
                                    <label for="takeoff_uom" class="col-sm-3 col-form-label">This item's takeoff unit of
                                        measure
                                        is </label>
                                    <div class="col-sm-3 my-auto">
                                        <select class="form-control" id="takeoff_uom" name="takeoff_uom">
                                            @foreach ($uom as $option)
                                                <option value="{{ $option->uom_name }}"
                                                    {{ $cost_item->count() && $option->uom_name == $cost_item[0]->takeoff_uom ? 'selected' : '' }}>
                                                    {{ $option->uom_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endif
                    <div class="col-sm-2 cost_item_block" id="control_group">
                        <div class="d-flex flex-column">
                            @if ($cost_item->count())
                                <div class="btn-group" id="pagination_btn">
                                    <a href="{{ $cost_item->previousPageUrl() }}"
                                        class="{{ $cost_item->currentPage() == 1
                                            ? 'btn btn-outline-secondary prev mr-1 disabled'
                                            : 'btn btn-outline-secondary prev mr-1' }}">
                                        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ $cost_item->nextPageUrl() }}"
                                        class="{{ $cost_item->total() == $cost_item->currentPage()
                                            ? 'btn btn-outline-secondary next disabled'
                                            : 'btn btn-outline-secondary next' }}">
                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @endif
                            <button type="button" class="btn btn-outline-secondary mt-2" id="add">Add Cost Item</button>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="delete"
                                data-page="{{ $cost_item->currentPage() }}"
                                data-id="{{ count($cost_item) ? $cost_item[0]->id : '' }}"
                                {{ $cost_item->count() ? '' : 'disabled' }}>
                                Delete
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="renumber"
                                data-id="{{ count($cost_item) ? $cost_item[0]->id : '' }}"
                                {{ $cost_item->count() ? '' : 'disabled' }}>
                                Renumber
                            </button>

                            <button type="button" class="btn btn-outline-secondary" id="renumber_confirm_button"
                                style="display: none" data-id="{{ count($cost_item) ? $cost_item[0]->id : '' }}"
                                data-page="{{ $cost_item->currentPage() }}">Ok
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2"
                                id="renumber_confirm_cancel_button" style="display: none">Cancel
                            </button>
                        </div>
                    </div>
                </div>

                @if ($cost_item->count())
                    <div class="d-flex flex-wrap cost_item_block">
                        {{-- LABOR --}}
                        <div class="col-12 col-md-4">
                            <div class="text-center">
                                <h5 class="text-muted">
                                    <span class="mr-3">LABOR</span>
                                    <img src="{{ $cost_item[0]->use_labor ? asset('/icons/check.png') : asset('/icons/uncheck.png') }}"
                                        data-checked="{{ $cost_item[0]->use_labor ? 1 : 0 }}" width="30px"
                                        class="mb-1 check_use_labor" alt="use_labor">
                                </h5>
                            </div>
                            <div class="form-group required row">
                                <label for="labor_uom" class="col-sm-4 col-form-label">Labor order UOM </label>
                                <div class="col-sm-7 my-auto">
                                    <select class="form-control" id="labor_uom" name="labor_uom">
                                        @foreach ($uom as $option)
                                            <option value="{{ $option->uom_name }}"
                                                {{ $cost_item->count() && $option->uom_name == ($cost_item[0]->labor_uom ? $cost_item[0]->labor_uom : 'not used') ? 'selected' : '' }}>
                                                {{ $option->uom_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row" id="labor_conversion_factor_area">
                                <label for="labor_conversion_factor" class="col-sm-4 col-form-label">Conversion factor
                                    from
                                    takeoff unit to order unit is </label>
                                <div class="col-sm-3 my-auto">
                                    <input type="number" min="0" class="form-control" id="labor_conversion_factor"
                                        name="labor_conversion_factor"
                                        value="{{ $cost_item->count() ? $cost_item[0]->labor_conversion_factor : '' }}">
                                </div>
                                {{-- conversion toggle status --}}
                                @if ($cost_item->count())
                                    <div class="col-sm-4 my-auto text-center">
                                        <input type="hidden" id="labor_conversion_toggle_status"
                                            value="{{ $cost_item[0]->labor_conversion_toggle_status }}">
                                        <div class="btn-group btn-group-sm">
                                            <button
                                                class="btn {{ $cost_item[0]->labor_conversion_toggle_status ? 'btn-success' : 'btn-outline-secondary' }}"
                                                type="button" id="labor_conversion_toggle_on">
                                                <span
                                                    class="takeoff_uom_toggle_text">{{ $cost_item[0]->takeoff_uom }}</span>
                                                &nbsp;per&nbsp;
                                                <span
                                                    class="labor_uom_toggle_text">{{ $cost_item[0]->labor_uom }}</span>
                                            </button>
                                            <button
                                                class="btn {{ $cost_item[0]->labor_conversion_toggle_status ? 'btn-outline-secondary' : 'btn-success' }}"
                                                type="button" id="labor_conversion_toggle_off">
                                                <span
                                                    class="labor_uom_toggle_text">{{ $cost_item[0]->labor_uom }}</span>
                                                &nbsp;per&nbsp;
                                                <span
                                                    class="takeoff_uom_toggle_text">{{ $cost_item[0]->takeoff_uom }}</span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                {{-- end conversion toggle status --}}
                            </div>

                            <div class="form-group row">
                                <label for="labor_price" class="col-sm-4 col-form-label">Labor price</label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="labor_price" name="labor_price"
                                        value="{{ $cost_item->count() ? $cost_item[0]->labor_price : '' }}">
                                    <span class="my-auto ml-1" id="labor_price_unit">
                                        {{ $cost_item->count() ? 'per ' . $cost_item[0]->labor_uom : '' }}
                                    </span>
                                </div>
                            </div>

                        </div>
                        {{-- END LABOR --}}

                        {{-- MATERIAL --}}
                        <div class="col-12 col-md-4">
                            <div class="text-center">
                                <h5 class="text-muted">
                                    <span class="mr-3">MATERIAL</span>
                                    <img src="{{ $cost_item[0]->use_material ? asset('/icons/check.png') : asset('/icons/uncheck.png') }}"
                                        data-checked="{{ $cost_item[0]->use_material ? 1 : 0 }}" width="30px"
                                        class="mb-1 check_use_material" alt="use_material">
                                </h5>
                            </div>
                            <div class="form-group required row">
                                <label for="material_uom" class="col-sm-4 col-form-label">Material order UOM </label>
                                <div class="col-sm-7 my-auto">
                                    <select class="form-control" id="material_uom" name="material_uom">
                                        @foreach ($uom as $option)
                                            <option value="{{ $option->uom_name }}"
                                                {{ $cost_item->count() && $option->uom_name == ($cost_item[0]->material_uom ? $cost_item[0]->material_uom : 'not used') ? 'selected' : '' }}>
                                                {{ $option->uom_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row" id="material_conversion_factor_area">
                                <label for="material_conversion_factor" class="col-sm-4 col-form-label">Conversion
                                    factor
                                    from
                                    takeoff unit to order unit is </label>
                                <div class="col-sm-3 my-auto">
                                    <input type="number" min="0" class="form-control" id="material_conversion_factor"
                                        name="material_conversion_factor"
                                        value="{{ $cost_item->count() ? $cost_item[0]->material_conversion_factor : '' }}">
                                </div>
                                {{-- conversion toggle status --}}
                                @if ($cost_item->count())
                                    <div class="col-sm-4 my-auto text-center">
                                        <input type="hidden" id="material_conversion_toggle_status"
                                            value="{{ $cost_item[0]->material_conversion_toggle_status }}">
                                        <div class="btn-group btn-group-sm">
                                            <button
                                                class="btn {{ $cost_item[0]->material_conversion_toggle_status ? 'btn-success' : 'btn-outline-secondary' }}"
                                                type="button" id="material_conversion_toggle_on">
                                                <span
                                                    class="takeoff_uom_toggle_text">{{ $cost_item[0]->takeoff_uom }}</span>
                                                &nbsp;per&nbsp;
                                                <span
                                                    class="material_uom_toggle_text">{{ $cost_item[0]->material_uom }}</span>
                                            </button>
                                            <button
                                                class="btn {{ $cost_item[0]->material_conversion_toggle_status ? 'btn-outline-secondary' : 'btn-success' }}"
                                                type="button" id="material_conversion_toggle_off">
                                                <span
                                                    class="material_uom_toggle_text">{{ $cost_item[0]->material_uom }}</span>
                                                &nbsp;per&nbsp;
                                                <span
                                                    class="takeoff_uom_toggle_text">{{ $cost_item[0]->takeoff_uom }}</span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                {{-- end conversion toggle status --}}
                            </div>

                            <div class="form-group row">
                                <label for="material_price" class="col-sm-4 col-form-label">Material price</label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="material_price"
                                        name="material_price"
                                        value="{{ $cost_item->count() ? $cost_item[0]->material_price : '' }}">
                                    <span class="my-auto ml-1" id="material_price_unit">
                                        {{ $cost_item->count() ? 'per ' . $cost_item[0]->material_uom : '' }}
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="material_waste_factor" class="col-sm-4 col-form-label">Waste </label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="material_waste_factor"
                                        name="material_waste_factor"
                                        value="{{ $cost_item->count() ? $cost_item[0]->material_waste_factor : '' }}">
                                    <span class="my-auto">&nbsp;%</span>
                                </div>
                            </div>
                        </div>
                        {{-- END MATERIAL --}}

                        {{-- SUBCONTRACT --}}
                        <div class="col-12 col-md-4">
                            <div class="text-center">
                                <h5 class="text-muted">
                                    <span class="mr-3">SUBCONTRACT</span>
                                    <img src="{{ $cost_item[0]->use_sub ? asset('/icons/check.png') : asset('/icons/uncheck.png') }}"
                                        data-checked="{{ $cost_item[0]->use_sub ? 1 : 0 }}" width="30px"
                                        class="mb-1 check_use_sub" alt="use_sub">
                                </h5>
                            </div>

                            <div class="form-group required row">
                                <label for="subcontract_uom" class="col-sm-4 col-form-label">Sub order UOM </label>
                                <div class="col-sm-7 my-auto">
                                    <select class="form-control" id="subcontract_uom" name="subcontract_uom">
                                        @foreach ($uom as $option)
                                            <option value="{{ $option->uom_name }}"
                                                {{ $cost_item->count() && $option->uom_name == ($cost_item[0]->subcontract_uom ? $cost_item[0]->subcontract_uom : 'not used') ? 'selected' : '' }}>
                                                {{ $option->uom_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row" id="subcontract_conversion_factor_area">
                                <label for="subcontract_conversion_factor" class="col-sm-4 col-form-label">Conversion
                                    factor
                                    from
                                    takeoff unit to order unit is </label>
                                <div class="col-sm-3 my-auto">
                                    <input type="number" min="0" class="form-control"
                                        id="subcontract_conversion_factor" name="subcontract_conversion_factor"
                                        value="{{ $cost_item->count() ? $cost_item[0]->subcontract_conversion_factor : '' }}">
                                </div>
                                {{-- conversion toggle status --}}
                                @if ($cost_item->count())
                                    <div class="col-sm-4 my-auto text-center">
                                        <input type="hidden" id="subcontract_conversion_toggle_status"
                                            value="{{ $cost_item[0]->subcontract_conversion_toggle_status }}">
                                        <div class="btn-group btn-group-sm">
                                            <button
                                                class="btn {{ $cost_item[0]->subcontract_conversion_toggle_status ? 'btn-success' : 'btn-outline-secondary' }}"
                                                type="button" id="subcontract_conversion_toggle_on">
                                                <span
                                                    class="takeoff_uom_toggle_text">{{ $cost_item[0]->takeoff_uom }}</span>
                                                &nbsp;per&nbsp;
                                                <span
                                                    class="subcontract_uom_toggle_text">{{ $cost_item[0]->subcontract_uom }}</span>
                                            </button>
                                            <button
                                                class="btn {{ $cost_item[0]->subcontract_conversion_toggle_status ? 'btn-outline-secondary' : 'btn-success' }}"
                                                type="button" id="subcontract_conversion_toggle_off">
                                                <span
                                                    class="subcontract_uom_toggle_text">{{ $cost_item[0]->subcontract_uom }}</span>
                                                &nbsp;per&nbsp;
                                                <span
                                                    class="takeoff_uom_toggle_text">{{ $cost_item[0]->takeoff_uom }}</span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                {{-- end conversion toggle status --}}
                            </div>

                            <div class="form-group row">
                                <label for="subcontract_price" class="col-sm-4 col-form-label">Sub price </label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="subcontract_price"
                                        name="subcontract_price"
                                        value="{{ $cost_item->count() ? $cost_item[0]->subcontract_price : '' }}">
                                    <span class="my-auto ml-1" id="subcontract_price_unit">
                                        {{ $cost_item->count() ? 'per ' . $cost_item[0]->subcontract_uom : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- END SUBCONTRACT --}}
                    </div>

                    <div class="show_hide_advanced_section">
                        <span class="advanced_icon"></span>
                        <span class="arrow_down_icon"></span>
                        <div>Show more</div>
                    </div>

                    {{-- formula block --}}
                    {{-- <div class="cost_item_block advanced_section"> --}}
                    {{-- @include('partials.update-formula-wizard') --}}
                    {{-- </div> --}}
                    {{-- end formula block --}}

                    {{-- HD/Lowes Block --}}
                    <div class="cost_item_block advanced_section">
                        <div class="form-group row">
                            <label for="lowes_sku" class="col-sm-3 col-form-label">This item's SKU at Lowe's
                                is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" id="lowes_sku" name="lowes_sku"
                                    value="{{ $cost_item->count() ? $cost_item[0]->lowes_sku : '' }}">
                            </div>
                            <label for="lowes_price" class="col-sm-3 col-form-label">Lowes price is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="number" class="form-control" id="lowes_price" name="lowes_price"
                                    value="{{ $cost_item->count() ? $cost_item[0]->lowes_price : '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="home_depot_sku" class="col-sm-3 col-form-label">This item's Home Depot Internet
                                number is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" id="home_depot_sku" name="home_depot_sku"
                                    value="{{ $cost_item->count() ? $cost_item[0]->home_depot_sku : '' }}">
                            </div>
                            <label for="home_depot_price" class="col-sm-3 col-form-label">Home Depot's price
                                is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="number" class="form-control" id="home_depot_price"
                                    name="home_depot_price"
                                    value="{{ $cost_item->count() ? $cost_item[0]->home_depot_price : '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="whitecap_sku" class="col-sm-3 col-form-label">This item's SKU at Whitecap is
                            </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" id="whitecap_sku" name="whitecap_sku"
                                    value="{{ $cost_item->count() ? $cost_item[0]->whitecap_sku : '' }}">
                            </div>
                            <label for="whitecap_price" class="col-sm-3 col-form-label">Whitecap's price is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="number" class="form-control" id="whitecap_price" name="whitecap_price"
                                    value="{{ $cost_item->count() ? $cost_item[0]->whitecap_price : '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="bls_number" class="col-sm-3 col-form-label">This item's Number at BLS is
                            </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" id="bls_number" name="bls_number"
                                    value="{{ $cost_item->count() ? $cost_item[0]->bls_number : '' }}">
                            </div>
                            <label for="bls_price" class="col-sm-3 col-form-label">BLS price is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="number" class="form-control" id="bls_price" name="bls_price"
                                    value="{{ $cost_item->count() ? $cost_item[0]->bls_price : '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="grainger_number" class="col-sm-3 col-form-label">This item's Number at Grainger
                                is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" id="grainger_number" name="grainger_number"
                                    value="{{ $cost_item->count() ? $cost_item[0]->grainger_number : '' }}">
                            </div>
                            <label for="grainger_price" class="col-sm-3 col-form-label">Grainger price is </label>
                            <div class="col-sm-3 my-auto">
                                <input type="number" class="form-control" id="grainger_price" name="grainger_price"
                                    value="{{ $cost_item->count() ? $cost_item[0]->grainger_price : '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="wcyw_number" class="col-sm-3 col-form-label">Wire & Cable YW SKU
                            </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" id="wcyw_number" name="wcyw_number"
                                    value="{{ $cost_item->count() ? $cost_item[0]->wcyw_number : '' }}">
                            </div>
                            <label for="wcyw_price" class="col-sm-3 col-form-label">Wire & Cable YW Price </label>
                            <div class="col-sm-3 my-auto">
                                <input type="number" class="form-control" id="wcyw_price" name="wcyw_price"
                                    value="{{ $cost_item->count() ? $cost_item[0]->wcyw_price : '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="item_invoice" class="col-sm-3 col-form-label">Default Invoice Item: </label>
                            <div class="col-sm-3 my-auto">
                                <select class="form-control select2-invoice" id="item_invoice" name="item_invoice">
                                    @foreach ($invoice_items_list as $option)
                                        <option value="{{ $option }}"
                                            {{ $option == $cost_item[0]->invoice_item_default ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="item_notes" class="col-sm-3 col-form-label">Item Note: </label>
                            <div class="col-sm-3 my-auto">
                                <textarea class="form-control" id="item_notes" rows="1">
                                {{ $cost_item->count() ? $cost_item[0]->notes : '' }}
                            </textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="item_proposal" class="col-sm-3 col-form-label">Default Quote/Proposal Item:
                            </label>
                            <div class="col-sm-3 my-auto">
                                <select class="form-control select2-proposal" id="item_proposal" name="item_proposal">
                                    @foreach ($proposal_items_list as $option)
                                        <option value="{{ $option }}"
                                            {{ $option == $cost_item[0]->quote_or_invoice_item ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- End HD/Lowes Block --}}
                @endif
            </div>

        </div>
    </div>

    <div class="card-footer bg-white border-top-0 text-center">
        <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary" id="back">Close</a>
        <button type="button" class="btn btn-outline-secondary" id="cancel" style="display: none;">Cancel</button>
        <button type="button" class="btn btn-outline-secondary" id="update" style="display: none;"
            data-id="{{ count($cost_item) ? $cost_item[0]->id : '' }}"
            data-page="{{ $cost_item->currentPage() }}">
            Update
        </button>
    </div>
</div>
