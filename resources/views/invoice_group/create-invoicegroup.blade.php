<div class="card-body" id="add_section" style="display: none">
    <form action="{{ route('invoice_group.store') }}" method="POST" id="add_invoice_group">
        @csrf
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group row">
                    <label for="ainvoicegroup" class="col-sm-4 col-form-label">Invoice Group Number</label>
                    <div class="col-sm-6 my-auto">
                        <input type="number" class="form-control" id="ainvoicegroup" name="ainvoicegroup"
                               placeholder="Enter invoice group" required>
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
            <button type="button" class="btn btn-outline-secondary add_invoicegroup"
                    id="add_invoicegroup_button" data-page="{{$invoice_group->currentPage()}}">
                <i class="fa fa-check"></i> Ok
            </button>
        </div>
    </form>
</div>