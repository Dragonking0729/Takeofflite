<div class="row">
    @if ($proposal_group->count())
        <div class="col-sm-10">
            <div class="form-group row">
                <label for="proposalgroup" class="col-sm-2 col-form-label">Proposal Group Number</label>
                <div class="col-sm-4 my-auto">
                    <input type="number" class="form-control" id="proposalgroup" name="proposalgroup"
                           value="{{ $proposal_group[0]->proposal_standard_item_group_number }}" placeholder="Enter proposal group"
                           disabled
                           required>
                </div>
                <div class="col-sm-1">
                    <button class="btn btn-outline-secondary open_folder" data-toggle="modal" data-target="#treeview">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="form-check-inline my-auto" id="folder_line">
                    <input class="form-check-input my-auto" type="checkbox" id="folder"
                           name="folder" {{ $proposal_group[0]->is_folder ? 'checked' : '' }}>
                    <label for="folder" class="form-check-label">Folder</label>
                </div>

                <div class="btn-group col-sm-2" id="next_prev_section">
                    <a href="{{ $proposal_group->previousPageUrl() }}"
                       class="{{ $proposal_group->currentPage() == 1 ? 'btn btn-outline-secondary prev mr-1 disabled' :
                   'btn btn-outline-secondary prev mr-1' }}">
                        <i class="fa fa-angle-double-left" aria-hidden="true"></i>
                    </a>

                    <a href="{{ $proposal_group->nextPageUrl() }}"
                       class="{{ $proposal_group->total() == $proposal_group->currentPage() ?
                   'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}">
                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                    </a>
                </div>

            </div>
            <div class="form-group row" id="desc_line">
                <label for="desc" class="col-sm-2 col-form-label">Description</label>
                <div class="col-sm-8">
                    <textarea class="form-control" id="desc"
                              rows="3">{{ $proposal_group[0]->proposal_standard_item_group_description }}</textarea>
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
            <button type="button" class="btn btn-outline-secondary add mt-2">Add Proposal Group</button>
            @if ($proposal_group->count())
                <button type="button" class="btn btn-outline-secondary delete mt-2" id="delete"
                        {{ $proposal_group->count() ? '' : 'disabled' }} data-id="{{ $proposal_group[0]->id }}"
                        data-page="{{$proposal_group->currentPage()}}">Delete
                </button>
            @endif
            @if ($proposal_group->count())
                <button type="button" class="btn btn-outline-secondary mt-2" id="renumber">Renumber</button>
            @endif
        </div>

        <div id="confirm_renumber_section" style="display: none">
            <button type="button" class="btn btn-outline-secondary mt-2" id="renumber_confirm_button"
                    data-id="{{ $proposal_group->count() ? $proposal_group[0]->id : '' }}"
                    data-page="{{$proposal_group->currentPage()}}">Ok
            </button>
            <button type="button" class="btn btn-outline-secondary mt-2" id="renumber_confirm_cancel_button">Cancel
            </button>
        </div>

    </div>
</div>
<div class="card-footer bg-white border-top-0 text-center" id="card_footer">
    @if ($proposal_group->count())
        <button type="button" class="btn btn-outline-secondary" id="update_proposalgroup_button"
                data-id="{{ $proposal_group[0]->id }}" data-page="{{$proposal_group->currentPage()}}" disabled>Save
        </button>
    @endif
    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">Close</a>
</div>