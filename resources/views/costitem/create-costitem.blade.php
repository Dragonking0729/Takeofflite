<div id="add_cost_item_section" style="display: none;">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <form action="{{ route('costitem.store') }}" method="POST" id="add_cost_item">
                    @csrf
                    <input type="hidden" name="acostgroup" id="acostgroup">
                    <div class="cost_item_block">
                        <div class="form-group required row">
                            <label for="acost_group" class="col-sm-3 col-form-label">This cost item belongs in cost
                                group: </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" name="acost_group" id="acost_group" disabled>
                            </div>
                            <div class="col-sm-1 my-auto">
                                <button type="button" class="btn btn-outline-secondary" data-toggle="modal"
                                    data-target="#create_costitem_treeview">
                                    <i class="fa fa-bars" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group required row">
                            <label for="aitem_number" class="col-sm-3 col-form-label">This item's number is </label>
                            <div class="col-sm-2 my-auto">
                                <input type="number" class="form-control" name="aitem_number" id="aitem_number">
                            </div>
                        </div>
                        <div class="form-group required row">
                            <label for="aitem_desc" class="col-sm-3 col-form-label">Item Description: </label>
                            <div class="col-sm-6 my-auto">
                                <textarea class="form-control" name="aitem_desc" id="aitem_desc" rows="1"></textarea>
                            </div>
                        </div>
                        <div class="form-group required row">
                            <label for="atakeoff_uom" class="col-sm-3 col-form-label">This item's takeoff unit of
                                measure
                                is </label>
                            <div class="col-sm-3 my-auto">
                                <select class="form-control" id="atakeoff_uom" name="atakeoff_uom">
                                    @foreach ($uom as $option)
                                        <option value="{{ $option->uom_name }}">
                                            {{ $option->uom_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap cost_item_block">
                        {{-- LABOR --}}
                        <div class="col-12 col-md-4">
                            <div class="text-center">
                                <h5 class="text-muted">
                                    <span class="mr-3">LABOR</span>
                                    <img src="{{ asset('/icons/uncheck.png') }}" data-checked="0" width="30px"
                                        class="mb-1 a_check_use_labor" alt="use_labor">
                                </h5>
                            </div>
                            <div class="form-group required row">
                                <label for="alabor_uom" class="col-sm-4 col-form-label">This item&#39; order unit of
                                    measure is </label>
                                <div class="col-sm-7 my-auto">
                                    <select class="form-control" id="alabor_uom" name="alabor_uom">
                                        @foreach ($uom as $option)
                                            <option value="{{ $option->uom_name }}"
                                                {{ $option->uom_name == 'not used' ? 'selected' : '' }}>
                                                {{ $option->uom_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row" id="alabor_conversion_factor_area">
                                <label for="alabor_conversion_factor" class="col-sm-4 col-form-label">Conversion factor
                                    from
                                    takeoff unit to order unit is </label>
                                <div class="col-sm-3 my-auto">
                                    <input type="number" min="0" class="form-control" id="alabor_conversion_factor"
                                        name="alabor_conversion_factor" value="1.0000">
                                </div>
                                {{-- conversion toggle status --}}
                                <div class="col-sm-4 my-auto text-center">
                                    <input type="hidden" id="alabor_conversion_toggle_status"
                                        name="alabor_conversion_toggle_status" value="1">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success" type="button" id="alabor_conversion_toggle_on">
                                            <span class="atakeoff_uom_toggle_text"></span>
                                            &nbsp;per&nbsp;
                                            <span class="alabor_uom_toggle_text"></span>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="alabor_conversion_toggle_off">
                                            <span class="alabor_uom_toggle_text"></span>
                                            &nbsp;per&nbsp;
                                            <span class="atakeoff_uom_toggle_text"></span>
                                        </button>
                                    </div>
                                </div>
                                {{-- end conversion toggle status --}}
                            </div>

                            <div class="form-group row">
                                <label for="alabor_price" class="col-sm-4 col-form-label">This item's price is </label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="alabor_price" name="alabor_price">
                                    <span class="my-auto ml-1" id="alabor_price_unit"></span>
                                </div>
                            </div>

                        </div>
                        {{-- END LABOR --}}

                        {{-- MATERIAL --}}
                        <div class="col-12 col-md-4">
                            <div class="text-center">
                                <h5 class="text-muted">
                                    <span class="mr-3">MATERIAL</span>
                                    <img src="{{ asset('/icons/uncheck.png') }}" data-checked="0" width="30px"
                                        class="mb-1 a_check_use_material" alt="use_material">
                                </h5>
                            </div>
                            <div class="form-group required row">
                                <label for="amaterial_uom" class="col-sm-4 col-form-label">This item&#39; order unit of
                                    measure
                                    is </label>
                                <div class="col-sm-7 my-auto">
                                    <select class="form-control" id="amaterial_uom" name="amaterial_uom">
                                        @foreach ($uom as $option)
                                            <option value="{{ $option->uom_name }}"
                                                {{ $option->uom_name == 'not used' ? 'selected' : '' }}>
                                                {{ $option->uom_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row" id="amaterial_conversion_factor_area">
                                <label for="amaterial_conversion_factor" class="col-sm-4 col-form-label">Conversion
                                    factor from
                                    takeoff unit to order unit is </label>
                                <div class="col-sm-3 my-auto">
                                    <input type="number" min="0" class="form-control" id="amaterial_conversion_factor"
                                        name="amaterial_conversion_factor" value="1.0000">
                                </div>
                                {{-- conversion toggle status --}}
                                <div class="col-sm-4 my-auto text-center">
                                    <input type="hidden" id="amaterial_conversion_toggle_status"
                                        name="amaterial_conversion_toggle_status" value="1">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success" type="button"
                                            id="amaterial_conversion_toggle_on">
                                            <span class="atakeoff_uom_toggle_text"></span>
                                            &nbsp;per&nbsp;
                                            <span class="amaterial_uom_toggle_text"></span>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="amaterial_conversion_toggle_off">
                                            <span class="amaterial_uom_toggle_text"></span>
                                            &nbsp;per&nbsp;
                                            <span class="atakeoff_uom_toggle_text"></span>
                                        </button>
                                    </div>
                                </div>
                                {{-- end conversion toggle status --}}
                            </div>

                            <div class="form-group row">
                                <label for="amaterial_price" class="col-sm-4 col-form-label">This item's price
                                    is </label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="amaterial_price"
                                        name="amaterial_price">
                                    <span class="my-auto ml-1" id="amaterial_price_unit"></span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="amaterial_waste_factor" class="col-sm-4 col-form-label">This item's waste
                                    factor
                                    is </label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="amaterial_waste_factor"
                                        name="amaterial_waste_factor">
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
                                    <img src="{{ asset('/icons/uncheck.png') }}" data-checked="0" width="30px"
                                        class="mb-1 a_check_use_sub" alt="use_sub">
                                </h5>
                            </div>
                            <div class="form-group required row">
                                <label for="asubcontract_uom" class="col-sm-4 col-form-label">This item&#39; order unit
                                    of
                                    measure
                                    is </label>
                                <div class="col-sm-7 my-auto">
                                    <select class="form-control" id="asubcontract_uom" name="asubcontract_uom">
                                        @foreach ($uom as $option)
                                            <option value="{{ $option->uom_name }}"
                                                {{ $option->uom_name == 'not used' ? 'selected' : '' }}>
                                                {{ $option->uom_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row" id="asubcontract_conversion_factor_area">
                                <label for="asubcontract_conversion_factor" class="col-sm-4 col-form-label">Conversion
                                    factor from
                                    takeoff unit to order unit is </label>
                                <div class="col-sm-3 my-auto">
                                    <input type="number" min="0" class="form-control"
                                        id="asubcontract_conversion_factor" name="asubcontract_conversion_factor"
                                        value="1.0000">
                                </div>
                                {{-- conversion toggle status --}}
                                <div class="col-sm-4 my-auto text-center">
                                    <input type="hidden" id="asubcontract_conversion_toggle_status"
                                        name="asubcontract_conversion_toggle_status" value="1">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success" type="button"
                                            id="asubcontract_conversion_toggle_on">
                                            <span class="atakeoff_uom_toggle_text"></span>
                                            &nbsp;per&nbsp;
                                            <span class="asubcontract_uom_toggle_text"></span>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="asubcontract_conversion_toggle_off">
                                            <span class="asubcontract_uom_toggle_text"></span>
                                            &nbsp;per&nbsp;
                                            <span class="atakeoff_uom_toggle_text"></span>
                                        </button>
                                    </div>
                                </div>
                                {{-- end conversion toggle status --}}
                            </div>

                            <div class="form-group row">
                                <label for="asubcontract_price" class="col-sm-4 col-form-label">This item's price
                                    is </label>
                                <div class="col-sm-7 d-flex my-auto">
                                    <input type="number" class="form-control" id="asubcontract_price"
                                        name="asubcontract_price">
                                    <span class="my-auto ml-1" id="asubcontract_price_unit"></span>
                                </div>
                            </div>
                        </div>
                        {{-- END SUBCONTRACT --}}
                    </div>

                    {{-- formula block --}}
                    {{-- <div class="cost_item_block"> --}}
                    {{-- @include('partials.create-formula-wizard') --}}
                    {{-- </div> --}}
                    {{-- end formula block --}}

                    {{-- HD/Lowes Block --}}
                    <div class="cost_item_block">
                        <div class="form-group row">
                            <label for="lowes_sku" class="col-sm-3 col-form-label">This item's SKU at Lowe's is
                            </label>
                            <div class="col-sm-2 my-auto">
                                <input type="text" class="form-control" name="lowes_sku">
                            </div>
                            <label for="lowes_price" class="col-sm-3 col-form-label">Lowes price is </label>
                            <div class="col-sm-2 my-auto">
                                <input type="number" class="form-control" name="lowes_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="whitecap_sku" class="col-sm-3 col-form-label">This item's SKU at Whitecap's is
                            </label>
                            <div class="col-sm-2 my-auto">
                                <input type="text" class="form-control" name="whitecap_sku">
                            </div>
                            <label for="whitecap_price" class="col-sm-3 col-form-label">Whitecap price is </label>
                            <div class="col-sm-2 my-auto">
                                <input type="number" class="form-control" name="whitecap_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="home_depot_sku" class="col-sm-3 col-form-label">This item's Home Depot Internet
                                number is </label>
                            <div class="col-sm-2 my-auto">
                                <input type="text" class="form-control" name="home_depot_sku">
                            </div>
                            <label for="home_depot_price" class="col-sm-3 col-form-label">Home Depot's price is
                            </label>
                            <div class="col-sm-2 my-auto">
                                <input type="number" class="form-control" name="home_depot_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="bls_number" class="col-sm-3 col-form-label">This item's BLS number is </label>
                            <div class="col-sm-2 my-auto">
                                <input type="text" class="form-control" name="bls_number">
                            </div>
                            <label for="bls_price" class="col-sm-3 col-form-label">BLS price is </label>
                            <div class="col-sm-2 my-auto">
                                <input type="number" class="form-control" name="bls_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="grainger_number" class="col-sm-3 col-form-label">This item's Grainger number is
                            </label>
                            <div class="col-sm-2 my-auto">
                                <input type="text" class="form-control" name="grainger_number">
                            </div>
                            <label for="grainger_price" class="col-sm-3 col-form-label">Grainger price is </label>
                            <div class="col-sm-2 my-auto">
                                <input type="number" class="form-control" name="grainger_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="wcyw_number" class="col-sm-3 col-form-label">Wire & Cable YW SKU
                            </label>
                            <div class="col-sm-2 my-auto">
                                <input type="text" class="form-control" name="wcyw_number">
                            </div>
                            <label for="wcyw_price" class="col-sm-3 col-form-label">Wire & Cable YW Price </label>
                            <div class="col-sm-2 my-auto">
                                <input type="number" class="form-control" name="wcyw_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="aitem_invoice" class="col-sm-3 col-form-label">Default Invoice Item: </label>
                            <div class="col-sm-2 my-auto">
                                <select class="form-control select2-invoice" id="aitem_invoice" name="aitem_invoice">
                                    @foreach ($invoice_items_list as $option)
                                        <option value="{{ $option }}">
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="aitem_notes" class="col-sm-3 col-form-label">Item Note: </label>
                            <div class="col-sm-2 my-auto">
                                <textarea class="form-control" name="aitem_notes" id="aitem_notes" rows="1"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="aitem_proposal" class="col-sm-3 col-form-label">Default Quote/Proposal Item:
                            </label>
                            <div class="col-sm-2 my-auto">
                                <select class="form-control select2-proposal" id="aitem_proposal"
                                    name="aitem_proposal">
                                    @foreach ($proposal_items_list as $option)
                                        <option value="{{ $option }}">
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    {{-- End HD/Lowes Block --}}

                </form>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-top-0 text-center">
        <button type="button" class="btn btn-outline-secondary cancel">Cancel</button>
        <button type="button" class="btn btn-outline-secondary save" id="add_costitem_button"
            data-page="{{ $cost_item->currentPage() }}">
            Save
        </button>
    </div>
</div>
