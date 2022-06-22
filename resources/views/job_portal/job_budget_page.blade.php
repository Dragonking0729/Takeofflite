<div id="job_budget_page" class="tab-pane fade">
    <div class="row mt-3 job_budget">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table class="budget_table">
                    <thead>
                    <tr>
                        <th style="width: 40%">Description</th>
                        <th style="width: 15%">Qty</th>
                        <th style="width: 10%">UOM</th>
                        <th style="width: 15%">Price</th>
                        <th style="width: 20%">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($budget as $item)
                        <tr>
                            <td>{{$item['desc']}}</td>
                            <td>{{$item['qty']}}</td>
                            <td>{{$item['uom']}}</td>
                            <td>{{$item['price']}}</td>
                            <td>{{$item['amount']}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>