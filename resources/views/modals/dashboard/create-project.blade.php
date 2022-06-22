<div class="modal fade" id="create_project">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0">
                <h4 class="modal-title">NEW PROJECT</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form class="was-validated" method="POST" action="{{route('dashboard.store')}}">
            @csrf
            <!-- Modal body -->
                <div class="modal-body" style="height: 60vh; overflow-y: auto;">
                    <div class="form-group">
                        <label for="project_name">PROJECT INFO</label>
                        <input type="text" class="form-control" id="project_name" name="project_name"
                               placeholder="Enter project name" required>
                        <small id="help" class="form-text text-muted"></small>
                        <div class="invalid-feedback">Please fill out this field.</div>
                    </div>
                    <div class="form-group">
                        <label for="street_address1">Project Address</label>
                        <input type="text" class="form-control" name="street_address1" placeholder="Enter address 1">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="street_address2" placeholder="Enter address 2">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="city" placeholder="Enter city">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="state" placeholder="Enter state">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="postal_code" placeholder="Enter postal code">
                    </div>

                    <div class="form-group">
                        <label for="customer_street_address1">CUSTOMER INFO</label>
                        <input type="text" class="form-control" name="customer_name" placeholder="Customer Name">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="customer_email" placeholder="Customer email">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="customer_phone" placeholder="Customer phone">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="customer_street_address1" placeholder="Enter address 1">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="customer_street_address2" placeholder="Enter address 2">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="customer_city" placeholder="Enter city">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="customer_state" placeholder="Enter state">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="customer_postal_code" placeholder="Enter postal code">
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </form>

        </div>
    </div>
</div>