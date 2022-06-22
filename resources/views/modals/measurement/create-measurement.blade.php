<div class="modal fade" id="create_measurement_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Create <span id="measurement_title_span"></span> Measurement</h4>
                <button type="button" class="close" data-dismiss="modal" onclick="cancelMeasuring()">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="d-flex mb-3 align-items-center">
                    <label for="segment_name" class="mr-2">Label:&nbsp;</label>
                    <input type="text" class="form-control" name="segment_name" id="segment_name"
                           placeholder="Enter the measurement name">
                </div>
                <div class="d-flex mb-3 align-items-center">
                    <label for="segment_color" class="mr-2">Color:&nbsp;</label>
                    <input type="color" class="form-control" name="segment_color" id="segment_color" value="#0000ff">
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" onclick="cancelMeasuring()">
                    Cancel
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="createMeasurement();">Create</button>
            </div>

        </div>
    </div>
</div>