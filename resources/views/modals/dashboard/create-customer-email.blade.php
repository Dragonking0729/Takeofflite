<div class="modal fade" id="create_customer_email_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0">
                <h4 class="modal-title">CREATE CUSTOMER EMAIL</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="height: auto; overflow: auto;">
                @csrf
                <p class="text-center">No customer email exists, add it here and we will put it in the job record for
                    you</p>
                <input class="form-control" name="new_customer_email" id="new_customer_email">
                <input type="hidden" id="project_id_for_new_customer_email">
            </div>

            <!-- Modal footer -->
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="save_customer_email">Save</button>
            </div>

        </div>
    </div>
</div>