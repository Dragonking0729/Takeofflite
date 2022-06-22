<div id="default_invoice_item_section">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-flex">
                    @if (!$invoice_item->count())
                        <div class="col-md-10 text-center">
                            No data found
                        </div>
                    @else
                        <div class="col-sm-10 pl-0">
                            <div class="invoice_item_block">
                                <div class="form-group required row" id="invoicegroup_div">
                                    <label for="invoicegroup" class="col-sm-3 col-form-label">This invoice item belongs
                                        in
                                        invoice
                                        group: </label>
                                    <div class="col-sm-3 my-auto">
                                        <input type="text" class="form-control" id="invoicegroup" name="invoicegroup"
                                               disabled
                                               required
                                               value="{{ $invoice_item->count() ? $invoice_item[0]->invoice_standard_item_group_number.'-'.$group_desc : '' }}"
                                               data-group_number="{{ $invoice_item->count() ? $invoice_item[0]->invoice_standard_item_group_number : '' }}">
                                    </div>
                                    <div class="col-sm-1 my-auto">
                                        <button class="btn btn-outline-secondary" data-toggle="modal"
                                                id="invoiceitem_treeview_btn"
                                                data-target="#invoiceitem_treeview">
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
                                               value="{{ $invoice_item->count() ? $invoice_item[0]->invoice_standard_item_number : '' }}">
                                    </div>
                                </div>
                                <div class="form-group required row">
                                    <label for="item_desc" class="col-sm-3 col-form-label">Item Description: </label>
                                    <div class="col-sm-9 my-auto">
                <textarea class="form-control" id="item_desc"
                          rows="1">{{ $invoice_item->count() ? $invoice_item[0]->invoice_standard_item_description : '' }}</textarea>
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
                                                        {{($invoice_item->count() && $option->uom_name == $invoice_item[0]->invoice_standard_item_uom) ? 'selected' : ''}}
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
                    <div class="col-sm-2 invoice_item_block" id="control_group">
                        <div class="d-flex flex-column">
                            @if ($invoice_item->count())
                                <div class="btn-group" id="pagination_btn">
                                    <a href="{{ $invoice_item->previousPageUrl() }}" class="{{ $invoice_item->currentPage() == 1 ?
                    'btn btn-outline-secondary prev mr-1 disabled' : 'btn btn-outline-secondary prev mr-1' }}">
                                        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ $invoice_item->nextPageUrl() }}" class="{{ $invoice_item->total() == $invoice_item->currentPage() ?
                    'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}">
                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @endif
                            <button type="button" class="btn btn-outline-secondary mt-2" id="add">Add Invoice Item
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="delete"
                                    data-page="{{$invoice_item->currentPage()}}"
                                    data-id="{{ count($invoice_item) ? $invoice_item[0]->id : '' }}"
                                    {{ $invoice_item->count() ? '' : 'disabled' }}>
                                Delete
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2" id="renumber"
                                    data-id="{{ count($invoice_item) ? $invoice_item[0]->id : '' }}"
                                    {{ $invoice_item->count() ? '' : 'disabled' }}>
                                Renumber
                            </button>

                            <button type="button" class="btn btn-outline-secondary" id="renumber_confirm_button"
                                    style="display: none"
                                    data-id="{{ count($invoice_item) ? $invoice_item[0]->id : '' }}"
                                    data-page="{{$invoice_item->currentPage()}}">Ok
                            </button>
                            <button type="button" class="btn btn-outline-secondary mt-2"
                                    id="renumber_confirm_cancel_button"
                                    style="display: none">Cancel
                            </button>
                        </div>
                    </div>
                </div>

                @if ($invoice_item->count())
                    <div class="invoice_item_block advanced_section">
                        <div class="form-group row">
                            <label for="markup_percent" class="col-sm-2 col-form-label">Markup Percent </label>
                            <div class="col-sm-4 my-auto">
                                <input type="number" class="form-control" id="markup_percent" name="markup_percent"
                                       value="{{ $invoice_item->count() ? $invoice_item[0]->invoice_standard_item_default_markup_percent : '' }}">
                            </div>
                            <label for="explanatory_text" class="col-sm-2 col-form-label">Explanatory</label>
                            <div class="col-sm-4 my-auto">
                            <textarea class="form-control" rows="2" id="explanatory_text"
                                      name="explanatory_text">{{ $invoice_item->count() ? $invoice_item[0]->invoice_standard_item_explanatory_text : '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="internal_notes" class="col-sm-2 col-form-label">Notes</label>
                            <div class="col-sm-4 my-auto">
                            <textarea class="form-control" rows="2" id="internal_notes"
                                      name="internal_notes">{{ $invoice_item->count() ? $invoice_item[0]->invoice_standard_item_internal_notes : '' }}</textarea>
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
                data-id="{{ count($invoice_item) ? $invoice_item[0]->id : '' }}"
                data-page="{{$invoice_item->currentPage()}}">
            Update
        </button>
    </div>
</div>