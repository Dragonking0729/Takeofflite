<!-- assembly item formula test modal -->
<div class="modal fade" id="assembly_item_formula_test_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Test Formula</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div class="text-center font-weight-bold text-danger">
                    <div class="form-group row">
                        <label for="parsed_formula_div" class="col-sm-2 text-right my-auto">Formula: </label>
                        <div class="col-sm-10" id="parsed_formula_div">

                        </div>
                    </div>
                </div>
                <div id="test_formula_body">
                    <div class="form-group row">
                        <label for="temp_id" class="col-sm-4 text-right my-auto">Label </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="temp_id" name="temp_id">
                        </div>
                    </div>
                </div>
                <div class="text-center font-weight-bold text-success" id="test_formula_result_div_area">
                    <div class="form-group row">
                        <label for="test_formula_result_div" class="col-sm-2 text-right my-auto">Result: </label>
                        <div class="col-sm-10" id="test_formula_result_div">

                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="test_formula_result">Result</button>
            </div>

        </div>
    </div>
</div>