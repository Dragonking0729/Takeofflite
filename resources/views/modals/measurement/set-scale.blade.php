<div class="modal fade" id="confirm_scale_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Set Scale</h4>
                <button type="button" class="close" data-dismiss="modal" onclick="cancelMeasuring()">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <p>
                    Click or tap the two endpoints of a line of known length
                </p>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" onclick="cancelMeasuring()">
                    Cancel
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="enteringSetScale();">Ok</button>
            </div>

        </div>
    </div>
</div>