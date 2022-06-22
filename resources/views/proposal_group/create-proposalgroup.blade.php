<div class="card-body" id="add_section" style="display: none">
    <form action="{{ route('proposal_group.store') }}" method="POST" id="add_proposal_group">
        @csrf
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group row">
                    <label for="aproposalgroup" class="col-sm-4 col-form-label">Proposal Group Number</label>
                    <div class="col-sm-6 my-auto">
                        <input type="number" class="form-control" id="aproposalgroup" name="aproposalgroup"
                               placeholder="Enter proposal group" required>
                    </div>
                    <div class="form-check my-auto">
                        <input class="form-check-input" type="checkbox" id="afolder" name="afolder">
                        <label for="afolder" class="form-check-label">Folder</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="adesc" class="col-sm-4 col-form-label">Description</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="adesc" name="adesc" rows="3"
                                  placeholder="Enter description"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 text-center">
            <button type="button" class="btn btn-outline-secondary add_cancel"><i class="fa fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-outline-secondary add_proposalgroup"
                    id="add_proposalgroup_button" data-page="{{$proposal_group->currentPage()}}">
                <i class="fa fa-check"></i> Ok
            </button>
        </div>
    </form>
</div>