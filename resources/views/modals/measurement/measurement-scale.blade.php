<div class="modal fade" id="set_scale_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Enter measurement scale</h4>
                <button type="button" class="close" data-dismiss="modal" onclick="cancelMeasuring()">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="d-flex mb-3 align-items-center">
                    <label class="mr-2" for="feet">Feet: </label>
                    <input type="text" class="form-control" name="scale_feet" id="scale_feet"
                           placeholder="feet" value="1">
                </div>
                <div class="d-flex mb-3 align-items-center">
                    <label class="mr-2" for="inch">Inch: </label>
                    <input type="text" class="form-control" name="scale_inch" id="scale_inch"
                            placeholder="inch" value="1">
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" onclick="cancelSetScale()">
                    Cancel
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="setScale();">Confirm</button>
            </div>

        </div>
    </div>
</div>