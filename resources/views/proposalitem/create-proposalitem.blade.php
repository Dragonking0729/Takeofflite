<div id="add_proposal_item_section" style="display: none;">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <form action="{{ route('proposal_item.store') }}" method="POST" id="add_proposal_item">
                    @csrf
                    <input type="hidden" name="aproposalgroup" id="aproposalgroup">
                    <div class="proposal_item_block">
                        <div class="form-group required row">
                            <label for="aproposal_group" class="col-sm-3 col-form-label">This proposal item belongs in proposal
                                group: </label>
                            <div class="col-sm-3 my-auto">
                                <input type="text" class="form-control" name="aproposal_group" id="aproposal_group" disabled>
                            </div>
                            <div class="col-sm-1 my-auto">
                                <button type="button" class="btn btn-outline-secondary" data-toggle="modal"
                                        data-target="#create_proposalitem_treeview">
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
                                        <option value="{{$option->uom_name}}">
                                            {{$option->uom_name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="proposal_item_block">
                        <div class="form-group row">
                            <label for="amarkup_percent" class="col-sm-3 col-form-label">Markup Percent </label>
                            <div class="col-sm-3 my-auto">
                                <input type="number" class="form-control" id="amarkup_percent" name="amarkup_percent">
                            </div>
                            <label for="aexplanatory_text" class="col-sm-3 col-form-label">Explanatory</label>
                            <div class="col-sm-3 my-auto">
                                <textarea class="form-control" rows="2" id="aexplanatory_text" name="aexplanatory_text"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="ainternal_notes" class="col-sm-3 col-form-label">Notes</label>
                            <div class="col-sm-3 my-auto">
                                <textarea class="form-control" rows="2" id="ainternal_notes" name="ainternal_notes"></textarea>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-top-0 text-center">
        <button type="button" class="btn btn-outline-secondary cancel">Cancel</button>
        <button type="button" class="btn btn-outline-secondary save" id="add_proposalitem_button"
                data-page="{{$proposal_item->currentPage()}}">
            Save
        </button>
    </div>
</div>