<div class="modal fade" id="edit_project">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0">
                <h4 class="modal-title">EDIT PROJECT</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form class="was-validated" action="" id="edit_project_modal_form" method="POST">
            @method('PUT')
            @csrf
            <!-- Modal body -->
                <div class="modal-body" style="height: 60vh; overflow-y: auto;">
                    <div class="form-group">
                        <label for="update_project_name">PROJECT INFO</label>
                        <input type="text" class="form-control" id="update_project_name" name="update_project_name"
                               placeholder="Enter project name" required>
                        <small id="help" class="form-text text-muted"></small>
                        <div class="invalid-feedback">Please fill out this field.</div>
                    </div>
                    <div class="form-group">
                        <label for="street_address1">Project Address</label>
                        <input type="text" class="form-control" id="update_street_address1"
                               name="update_street_address1" placeholder="Enter address 1">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_street_address2"
                               name="update_street_address2" placeholder="Enter address 2">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_city" name="update_city"
                               placeholder="Enter city">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_state" name="update_state"
                               placeholder="Enter state">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_postal_code" name="update_postal_code"
                               placeholder="Enter postal code">
                    </div>

                    <div class="form-group">
                        <label for="customer_street_address1">CUSTOMER INFO</label>
                        <input type="text" class="form-control" id="update_customer_name" name="update_customer_name"
                               placeholder="Customer Name">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_customer_email" name="update_customer_email"
                               placeholder="Customer email">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_customer_phone" name="update_customer_phone"
                               placeholder="Customer phone">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_customer_street_address1"
                               name="update_customer_street_address1" placeholder="Enter address 1">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_customer_street_address2"
                               name="update_customer_street_address2" placeholder="Enter address 2">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_customer_city" name="update_customer_city"
                               placeholder="Enter city">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_customer_state" name="update_customer_state"
                               placeholder="Enter state">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="update_customer_postal_code"
                               name="update_customer_postal_code" placeholder="Enter postal code">
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>