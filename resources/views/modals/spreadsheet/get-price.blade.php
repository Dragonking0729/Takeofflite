<!-- interview costitem formula modal -->
<div class="modal fade" id="price_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">GET REAL TIME PRICES FROM VENDORS</h5>
                <button type="button" class="close" data-dismiss="modal"
                    onclick="cancelPrice()">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" action="{{ url('estimate/get_price') }}" id="frmGetPriceLookup">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="price_project_id" value="{{ $page_info['project_id'] }}">
                    <input type="hidden" name="price_selected_rows" id="price_selected_rows" value="[]">
                    <div class="form-group row">
                        <label for="price_hd_lowes" class="col-sm-4 my-auto">Select HD/Lowes: </label>
                        <div class="col-sm-8">
                            <select class="form-control" id="price_hd_lowes" name="price_hd_lowes">
                                <option value="hd">HD</option>
                                <option value="lowes">Lowes</option>
                                <option value="whitecap">Whitecap</option>
                                <option value="BLS">BLS</option>
                                <option value="GRAINGER">Grainger</option>
                                <option value="WCYW">Wire & Cable YW</option>
                                <option value="bmc">BMC</option>
                                <option value="abc_supply">ABC Supply</option>
                                <option value="lumber">84 Lumber</option>
                                <option value="shop_best_price">Shop for best prices</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-8">
                            <input type="checkbox" class="form-check-input ml-1" id="price_store_to_lib"
                                name="price_store_to_lib">
                            <div class="col-sm-12">
                                <label class="form-check-label ml-2" for="price_store_to_lib">Update prices in Cost
                                    Items
                                    Library?</label>
                            </div>

                        </div>


                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="cancelPrice()">Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="submit_get_price">Get Price</button>
                </div>
            </form>

        </div>
    </div>
</div>
