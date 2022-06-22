<div id="job_detail_page" class="tab-pane fade in active show">
    <div class="row mt-3">
        <div class="col-sm-12">
            <div class="project_name">{{$project->project_name}}</div>
            <div class="job_address">{{$project->street_address_1 ? $project->street_address_1 : 'No exist address'}}</div>
            <div class="job_address">{{trim($project->city.' '.$project->state.' '.$project->postal_code)}}</div>
        </div>
    </div>

    <div class="row mt-3" id="job_detail_flip">
        <div class="col-sm-12">

            <div class="d-flex justify-content-between mb-3">
                <label for="square_footage" class="col-sm-4 px-0 col-form-label" style="font-size: 14px;">Square
                    Footage</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="square_footage" name="square_footage"
                           value="{{$project->square_footage}}" disabled>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <label for="purchase_price" class="col-sm-4 px-0 col-form-label" style="font-size: 14px;">Purchase
                    Price</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="purchase_price" name="purchase_price" disabled
                           value="{{$flip_info && $flip_info->purchase_price ? $flip_info->purchase_price : 0}}">
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <label for="arv" class="col-sm-4 px-0 col-form-label" style="font-size: 14px;">After Repair
                    Value</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="arv" name="arv"
                           value="{{$flip_info && $flip_info->arv ? $flip_info->arv : 0}}" disabled>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <label for="repair_cost" class="col-sm-4 px-0 col-form-label" style="font-size: 14px;">Repair
                    Cost</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="repair_cost" name="repair_cost"
                           value="{{$repair_cost}}" disabled>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <label for="acquisition_costs" class="col-sm-4 px-0 col-form-label" style="font-size: 14px;">Acquisition
                    Costs</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="acquisition_costs" name="acquisition_costs" disabled
                           value="{{$flip_info && $flip_info->acquisition_costs ? $flip_info->acquisition_costs : 0}}">
                </div>
                <i class="fa fa-plus my-auto show-acquisition-detail" aria-hidden="true"></i>
            </div>
            <div class="acquisition-detail">
                <table style="width: 100%;">
                    <thead>
                    <tr>
                        <th style="width: 40%;">Item</th>
                        <th style="width: 40%;">Amount</th>
                    </tr>
                    </thead>
                    <tbody class="acquisition-detail-body">
                    @foreach($detailed_acquisition_costs as $item)
                        <tr>
                            <td>
                                <input type="text" class="form-control" value="{{$item['item']}}" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{$item['amount']}}" disabled>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="detail-total-cost">
                    <div>Total Acquisition Costs</div>
                    <div>$<span class="acquisition-detail-total ">{{$total_acquisition_costs}}</span></div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <label for="holding_costs" class="col-sm-4 px-0 col-form-label">Holding Costs</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="holding_costs" name="holding_costs" disabled
                           value="{{$flip_info && $flip_info->holding_costs ? $flip_info->holding_costs : 0}}">
                </div>
                <i class="fa fa-plus my-auto show-holding-detail" aria-hidden="true"></i>
            </div>
            <div class="holding-detail">
                <table>
                    <thead>
                    <tr>
                        <th style="width: 40%;">Item</th>
                        <th style="width: 40%;">Amount</th>
                    </tr>
                    </thead>
                    <tbody class="holding-detail-body">
                    @foreach($detailed_holding_costs as $item)
                        <tr>
                            <td>
                                <input type="text" class="form-control" value="{{$item['item']}}" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{$item['amount']}}" disabled>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="detail-total-cost">
                    <div>Total Holding Costs</div>
                    <div>$<span class="holding-detail-total">{{$total_holding_costs}}</span></div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <label for="selling_costs" class="col-sm-4 px-0 col-form-label">Selling Costs</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="selling_costs" name="selling_costs" disabled
                           value="{{$flip_info && $flip_info->selling_costs ? $flip_info->selling_costs : 0}}">
                </div>
                <i class="fa fa-plus my-auto show-selling-detail" aria-hidden="true"></i>
            </div>
            <div class="selling-detail">
                <table>
                    <thead>
                    <tr>
                        <th style="width: 40%;">Item</th>
                        <th style="width: 40%;">Amount</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="selling-detail-body">
                    @foreach($detailed_selling_costs as $item)
                        <tr>
                            <td>
                                <input type="text" class="form-control" value="{{$item['item']}}" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{$item['amount']}}" disabled>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="detail-total-cost">
                    <div>Total Selling Costs</div>
                    <div>$<span class="selling-detail-total">{{$total_selling_costs}}</span></div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <label for="financing_costs" class="col-sm-4 px-0 col-form-label">Financing Costs</label>
                <div class="col-sm-6 my-auto">
                    <input type="text" class="form-control" id="financing_costs" name="financing_costs" disabled
                           value="{{$flip_info && $flip_info->financing_costs ? $flip_info->financing_costs : 0}}">
                </div>
                <i class="fa fa-plus my-auto show-financing-detail" aria-hidden="true"></i>
            </div>
            <div class="financing-detail">
                <table>
                    <thead>
                    <tr>
                        <th style="width: 40%;">Item</th>
                        <th style="width: 40%;">Amount</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="financing-detail-body">
                    @foreach($detailed_financing_costs as $item)
                        <tr>
                            <td>
                                <input type="text" class="form-control" value="{{$item['item']}}" disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{$item['amount']}}" disabled>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="detail-total-cost">
                    <div>Total Financing Costs</div>
                    <div>$<span class="financing-detail-total">{{$total_financing_costs}}</span></div>
                </div>
            </div>

        </div>
    </div>
</div>