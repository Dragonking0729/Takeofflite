<div class="modal fade" id="job_share_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0">
                <h4 class="modal-title">JOB SHARING</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="form-group">
                    <label for="share_receiver_user_id">Job Sharing Code</label>
                    <input type="text" class="form-control" id="share_receiver_user_id" name="share_receiver_user_id"
                           placeholder="Enter job sharing code" required>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="job_share" data-share_project_number="">Save</button>
            </div>

        </div>
    </div>
</div>