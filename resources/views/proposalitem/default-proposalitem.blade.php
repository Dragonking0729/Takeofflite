<div id="default_proposal_item_section">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-flex">
                    @if (!$proposal_item->count())
                        <div class="col-md-10 text-center">
                            No data found
                        </div>
                    @else
                        <div class="col-sm-10 pl-0">
                            <div class="proposal_item_block">
                                <div class="form-group required row" id="proposalgroup_div">
                                    <label for="proposalgroup" class="col-sm-3 col-form-label">This proposal item
                                        belongs in
                                        proposal
                                        group: </label>
                                    <div class="col-sm-3 my-auto">
                                        <input type="text" class="form-control" id="proposalgroup" name="proposalgroup"
                                               disabled
                                               required
                                               value="{{ $proposal_item->count() ? $proposal_item[0]->proposal_standard_item_group_number.'-'.$group_desc : '' }}"
                                               data-group_number="{{ $proposal_item->count() ? $proposal_item[0]->proposal_standard_item_group_number : '' }}">
                                    </div>
                                    <div class="col-sm-1 my-auto">
                                        <button class="btn btn-outline-secondary" data-toggle="modal"
                                                id="proposalitem_treeview_btn"
                                                data-target="#proposalitem_treeview">
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
                                               value="{{ $proposal_item->count() ? $proposal_item[0]->proposal_standard_item_number : '' }}">
                                    </div>
                                </div>
                                <div class="form-group required row">
                                    <label for="item_desc" class="col-sm-3 col-form-label">Item Description: </label>
                                    <div class="col-sm-9 my-auto">
                <textarea class="form-control" id="item_desc"
                          rows="1">{{ $proposal_item->count() ? $proposal_item[0]->proposal_standard_item_description : '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group required row">
                                    <label for="takeoff_uom" class="col-sm-3 col-form-label">This item's takeoff unit of
                                        measure
                                        is </label>
                                    <div class="col-sm-3 my-auto">
                                        <select class="form-control" id="takeoff_uom" name="takeoff_uom">
                                            @foreach ($uom as $option)
                                                <option value="{{$option->uom_name}}"
                                                        {{($proposal_item->count() && $option->uom_name == $proposal_item[0]->proposal_standard_item_uom) ? 'selected' : ''}}
                                                >
                                                    {{$option->uom_name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endif

                    <div class="col-sm-2 proposal_item_block" id="control_group">
                        <div class="d-flex flex-column">
                            @if ($proposal_item->count())
                                <div class="btn-group" id="pagination_btn">
                                    <a href="{{ $proposal_item->previousPageUrl() }}" class="{{ $proposal_item->currentPage() == 1 ?
                    'btn btn-outline-secondary prev mr-1 disabled' : 'btn btn-outline-secondary prev mr-1' }}">
                                        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ $proposal_item->nextPageUrl() }}" class="{{ $proposal_item->total() == $proposal_item->currentPage() ?
                    'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}">
                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @endif
                            <button type="button" class="btn btn-outline-secondary mt-2" id="add">Add Proposal Item
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="delete"
                                    data-page="{{$proposal_item->currentPage()}}"
                                    data-id="{{ count($proposal_item) ? $proposal_item[0]->id : '' }}"
                                    {{ $proposal_item->count() ? '' : 'disabled' }}>
                                Delete
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="renumber"
                                    data-id="{{ count($proposal_item) ? $proposal_item[0]->id : '' }}"
                                    {{ $proposal_item->count() ? '' : 'disabled' }}>
                                Renumber
                            </button>

                            <button type="button" class="btn btn-outline-secondary" id="renumber_confirm_button"
                                    style="display: none"
                                    data-id="{{ count($proposal_item) ? $proposal_item[0]->id : '' }}"
                                    data-page="{{$proposal_item->currentPage()}}">Ok
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2"
                                    id="renumber_confirm_cancel_button"
                                    style="display: none">Cancel
                            </button>
                        </div>
                    </div>
                </div>

                @if ($proposal_item->count())
                    <div class="proposal_item_block advanced_section">
                        <div class="form-group row">
                            <label for="markup_percent" class="col-sm-2 col-form-label">Markup Percent </label>
                            <div class="col-sm-4 my-auto">
                                <input type="number" class="form-control" id="markup_percent" name="markup_percent"
                                       value="{{ $proposal_item->count() ? $proposal_item[0]->proposal_standard_item_default_markup_percent : '' }}">
                            </div>
                            <label for="explanatory_text" class="col-sm-2 col-form-label">Explanatory</label>
                            <div class="col-sm-4 my-auto">
                            <textarea class="form-control" rows="2" id="explanatory_text"
                                      name="explanatory_text">{{ $proposal_item->count() ? $proposal_item[0]->proposal_standard_item_explanatory_text : '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="internal_notes" class="col-sm-2 col-form-label">Notes</label>
                            <div class="col-sm-4 my-auto">
                            <textarea class="form-control" rows="2" id="internal_notes"
                                      name="internal_notes">{{ $proposal_item->count() ? $proposal_item[0]->proposal_standard_item_internal_notes : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <div class="card-footer bg-white border-top-0 text-center">
        <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary" id="back">Close</a>
        <button type="button" class="btn btn-outline-secondary" id="cancel" style="display: none;">Cancel</button>
        <button type="button" class="btn btn-outline-secondary" id="update" style="display: none;"
                data-id="{{ count($proposal_item) ? $proposal_item[0]->id : '' }}"
                data-page="{{$proposal_item->currentPage()}}">
            Update
        </button>
    </div>
</div>