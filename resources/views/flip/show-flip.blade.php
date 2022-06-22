@extends('layouts.app')

@section('title')
    {{ trans('titles.flip') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/flip.css') }}"/>
@endsection

@section('content')
    @include('partials.tabs')

    <div class="container" style="margin-left: 50px;margin-top: 25px;">
        <div class="text-center font-weight-bold" style="font-size: 32px;">
            Flip Analysis
        </div>
        <div class="flip_body">
            <div class="row">
                {{-- flip entry --}}
                <div class="col-sm-6">
                    <div class="form-group row">
                        @csrf
                        <label for="purchase_price" class="col-sm-4 col-form-label">Purchase Price</label>
                        <div class="col-sm-6 my-auto">
                            <input type="text" class="form-control" id="purchase_price" name="purchase_price"
                                   value="{{$flip_info && $flip_info->purchase_price ? $flip_info->purchase_price : 0}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="arv" class="col-sm-4 col-form-label">After Repair Value</label>
                        <div class="col-sm-6 my-auto">
                            <input type="text" class="form-control" id="arv" name="arv"
                                   value="{{$flip_info && $flip_info->arv ? $flip_info->arv : 0}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="repair_cost" class="col-sm-4 col-form-label">Repair Cost</label>
                        <div class="col-sm-6 my-auto">
                            <input type="text" class="form-control" id="repair_cost" name="repair_cost"
                                   value="{{$repair_cost}}" disabled>
                        </div>
                    </div>

                    {{-- acquisition cost --}}
                    <div class="form-group row">
                        <label for="acquisition_costs" class="col-sm-4 col-form-label">Acquisition Costs</label>
                        <div class="col-sm-6 my-auto">
                            <input type="text" class="form-control" id="acquisition_costs" name="acquisition_costs"
                                   value="{{$flip_info && $flip_info->acquisition_costs ? $flip_info->acquisition_costs : 0}}">
                        </div>
                        <i class="fa fa-plus my-auto show-acquisition-detail" aria-hidden="true"></i>
                    </div>
                    <div class="acquisition-detail">
                        <table>
                            <thead>
                            <tr>
                                <th style="width: 40%;">Item</th>
                                <th style="width: 40%;">Amount</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="acquisition-detail-body">
                            </tbody>
                        </table>
                        <div class="my-1">
                            <button class="btn btn-success btn-sm add-acquisition-detail-line">Add an Acquisition Cost
                            </button>
                        </div>
                        <div class="detail-total-cost">
                            <div>Total Acquisition Costs</div>
                            <div>$<span class="acquisition-detail-total ">0</span></div>
                        </div>
                    </div>
                    {{-- end acquisition cost --}}

                    {{-- Holding cost --}}
                    <div class="form-group row">
                        <label for="holding_costs" class="col-sm-4 col-form-label">Holding Costs</label>
                        <div class="col-sm-6 my-auto">
                            <input type="text" class="form-control" id="holding_costs" name="holding_costs"
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
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="holding-detail-body">
                            </tbody>
                        </table>
                        <div class="my-1">
                            <button class="btn btn-success btn-sm add-holding-detail-line">Add a Holding Cost</button>
                        </div>
                        <div class="detail-total-cost">
                            <div>Total Holding Costs</div>
                            <div>$<span class="holding-detail-total">0</span></div>
                        </div>
                    </div>
                    {{-- end Holding cost --}}

                    {{-- Selling cost --}}
                    <div class="form-group row">
                        <label for="selling_costs" class="col-sm-4 col-form-label">Selling Costs</label>
                        <div class="col-sm-6 my-auto">
                            <input type="text" class="form-control" id="selling_costs" name="selling_costs"
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
                            </tbody>
                        </table>
                        <div class="my-1">
                            <button class="btn btn-success btn-sm add-selling-detail-line">Add a Selling Cost</button>
                        </div>
                        <div class="detail-total-cost">
                            <div>Total Selling Costs</div>
                            <div>$<span class="selling-detail-total">0</span></div>
                        </div>
                    </div>
                    {{-- end Selling cost --}}

                    {{-- Financing cost --}}
                    <div class="form-group row">
                        <label for="financing_costs" class="col-sm-4 col-form-label">Financing Costs</label>
                        <div class="col-sm-6 my-auto">
                            <input type="text" class="form-control" id="financing_costs" name="financing_costs"
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
                            </tbody>
                        </table>
                        <div class="my-1">
                            <button class="btn btn-success btn-sm add-financing-detail-line">Add a Financing Cost</button>
                        </div>
                        <div class="detail-total-cost">
                            <div>Total Financing Costs</div>
                            <div>$<span class="financing-detail-total">0</span></div>
                        </div>
                    </div>
                    {{-- end Financing cost --}}
                </div>
                {{-- end flip entry --}}

                {{-- flip analysis --}}
                <div class="col-sm-6">
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
                {{-- end flip analysis --}}
            </div>
        </div>
    </div>

@endsection


@section('script')
    @include('scripts.flip.flip-js')
@endsection