<div id="job_deal_analyzer_page" class="tab-pane fade">
    <div class="row mt-3 job_analyzer">
        <div class="col-sm-12">
            <div class="card flip-analysis-card">
                <div class="card-header d-flex justify-content-end font-weight-bold">
                    Amount
                </div>
                <div>
                    <div class="d-flex justify-content-between flip-analysis-line">
                        <div>
                            After repair value (ARV)
                        </div>
                        <div>
                            $<span id="arv__analysis">{{$flip_info && $flip_info->arv ? $flip_info->arv : 0}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between flip-analysis-line">
                        <div>
                            Minus:
                        </div>
                        <div>
                            Purchase Price
                        </div>
                        <div>
                            -$<span id="purchase_price__analysis">{{$flip_info && $flip_info->purchase_price ? $flip_info->purchase_price : 0}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between flip-analysis-line">
                        <div></div>
                        <div>
                            Repair Cost
                        </div>
                        <div>
                            -$<span>{{$repair_cost}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between flip-analysis-line">
                        <div></div>
                        <div>
                            Acquisition Costs
                        </div>
                        <div>
                            -$<span id="acquisition_costs__analysis">{{$flip_info && $flip_info->acquisition_costs ? $flip_info->acquisition_costs : 0}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between flip-analysis-line">
                        <div></div>
                        <div>
                            Holding Costs
                        </div>
                        <div>
                            -$<span id="holding_costs__analysis">{{$flip_info && $flip_info->holding_costs ? $flip_info->holding_costs : 0}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between flip-analysis-line">
                        <div></div>
                        <div>
                            Selling Costs
                        </div>
                        <div>
                            -$<span id="selling_costs__analysis">{{$flip_info && $flip_info->selling_costs ? $flip_info->selling_costs : 0}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between flip-analysis-line">
                        <div></div>
                        <div>
                            Financing Costs
                        </div>
                        <div>
                            -$<span id="financing_costs__analysis">{{$flip_info && $flip_info->financing_costs ? $flip_info->financing_costs : 0}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between profit {{$profit < 0 ? 'negative' : ''}}">
                        <div class="col-8">
                            Profit using above assumptions
                        </div>
                        <div class="col-4 text-right">
                            <span class="profit_sign">{{$profit < 0 ? '-' : ''}}</span>
                            $<span class="profit_price">{{abs($profit)}}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between max_purchase_price {{$max_purchase < 0 ? 'negative' : ''}}">
                        <div class="col-8">
                            Max purchase price to break even using above expense assumptions
                        </div>
                        <div class="col-4 text-right">
                            <span class="max_purchase_sign">{{$max_purchase < 0 ? '-' : ''}}</span>
                            $<span class="max_purchase">{{abs($max_purchase)}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>