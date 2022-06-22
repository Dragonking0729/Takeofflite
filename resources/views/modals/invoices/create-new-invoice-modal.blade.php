<!-- create_new_invoice_modal -->
<div class="modal fade" id="create_new_invoice_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">CREATE NEW INVOICE</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" action="{{route('invoice.store')}}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="project_id" value="{{$page_info['project_id']}}">
                    <div class="form-group row">
                        <label for="create_new" class="col-sm-3 my-auto">Create new </label>
                        <input type="radio" class="col-sm-1 m-auto" name="create_from" id="create_new"
                               value="create_new" required checked>
                        <label for="import_existing" class="col-sm-3 my-auto">Import existing </label>
                        <input type="radio" class="col-sm-1 m-auto" name="create_from" id="import_existing"
                               value="import_existing" required>
                    </div>
                    <div class="form-group row">
                        <label for="invoice_name" class="col-sm-4 my-auto">Invoice Name </label>
                        <input type="text" class="form-control col-sm-7" name="invoice_name" id="invoice_name"
                               required>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="submit_get_price">Create</button>
                </div>
            </form>

        </div>
    </div>
</div>