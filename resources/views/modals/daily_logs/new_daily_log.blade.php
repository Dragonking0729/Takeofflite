<div class="modal fade" id="new_daily_log_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">New Daily Log</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form method="POST" action="{{route('daily_logs.store')}}" enctype="multipart/form-data">
                @csrf
                <!-- Modal body -->
                <div class="modal-body">
                    <input type="hidden" name="project_id" value="{{$page_info['project_id']}}">
                    <input type="hidden" name="new_log_entry_date" id="new_log_entry_date">
                    <div class="form-group">
                        <label for="new_customer_view">Customer can view?</label>
                        <input type="checkbox" name="new_customer_view" id="new_customer_view">
                    </div>
                    <div class="form-group">
                        <label for="new_note">Note</label>
                        <textarea class="form-control" rows="2" name="new_note" id="new_note"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="new_attached_files">Attach Files</label>
                        <input type="file" name="filenames[]" class="form-control" id="new_attached_files" multiple>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </form>

        </div>
    </div>
</div>