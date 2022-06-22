<div class="modal fade" id="edit_company">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0">
                <h4 class="modal-title">EDIT COMPANY</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form action="{{url('dashboard/update_company')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="form-group">
                        <label for="ucompany_name">Company name</label>
                        <input type="text" class="form-control" id="ucompany_name" name="ucompany_name" value="{{$company_info->company_name}}" placeholder="Enter company name">
                    </div>
                    <div class="form-group">
                        <label for="ustreet_address1">Address line 1</label>
                        <input type="text" class="form-control" id="ustreet_address1" name="ustreet_address1" value="{{$company_info->street_address1}}" placeholder="Enter address line 1">
                    </div>
                    <div class="form-group">
                        <label for="ustreet_address2">Address line 2</label>
                        <input type="text" class="form-control" id="ustreet_address2" name="ustreet_address2" value="{{$company_info->street_address2}}" placeholder="Enter address line 2">
                    </div>
                    <div class="form-group">
                        <label for="ucity">City</label>
                        <input type="text" class="form-control" id="ucity" name="ucity" value="{{$company_info->city}}" placeholder="Enter city name">
                    </div>
                    <div class="form-group">
                        <label for="ustate">State or Province</label>
                        <input type="text" class="form-control" id="ustate" name="ustate" value="{{$company_info->state}}" placeholder="Enter state or province">
                    </div>
                    <div class="form-group">
                        <label for="upostal_code">Postal Code</label>
                        <input type="text" class="form-control" id="upostal_code" name="upostal_code" value="{{$company_info->postal_code}}" placeholder="Enter state or province">
                    </div>
                    <div class="form-group">
                        <label for="ucompany_url">Company URL</label>
                        <input type="text" class="form-control" id="ucompany_url" name="ucompany_url" value="{{$company_info->company_url}}" placeholder="Enter the company URL">
                    </div>
                    <div class="form-group">
                        <label for="ucompany_logo">Company Logo</label>
                        <input type="file" class="form-control" id="ucompany_logo" name="ucompany_logo" placeholder="Select company logo">
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