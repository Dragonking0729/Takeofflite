<script>
    var project_id = '{{$page_info['project_id']}}';
    var _token = $('input[name="_token"]').val();
    var acquisition_detail = JSON.parse(@json($flip_info->detailed_acquisition_costs));
    var holding_detail = JSON.parse(@json($flip_info->detailed_holding_costs));
    var selling_detail = JSON.parse(@json($flip_info->detailed_selling_costs));
    var financing_detail = JSON.parse(@json($flip_info->detailed_financing_costs));
    var lastValid = '';

    console.log('flip detail...', acquisition_detail, holding_detail, selling_detail, financing_detail);

    function updateFlipDetail(field, data) {
        console.log('running updateFlipDetail');
        $.ajax({
            url: '{{route("flip.update_flip_detail")}}',
            method: 'POST',
            data: {
                _token: _token,
                project_id: project_id,
                field: field,
                data: JSON.stringify(data)
            },
            success: function (res) {
                console.log(res);
                // if (res.status === 'success') {
                //     toastr.success(res.message);
                // }
            }
        });
    }

    function updateFlipEntry(field, val) {
        console.log('running updateFlipEntry');
        $.ajax({
            url: '{{route("flip.update_entry")}}',
            method: 'POST',
            data: {
                _token: _token,
                project_id: project_id,
                field: field,
                val: val
            },
            success: function (res) {
                console.log(res);
                if (res.status === 'success') {
                    toastr.success(res.message);
                }
            }
        });
    }

    function getDetailLastId(data) {
        let maxId = Math.max.apply(Math, data.map(function (o) {
            return o.id;
        }));
        return maxId + 1;
    }

    function validateNumber(elem) {
        let validNumber = new RegExp(/^\d*\.?\d*$/);
        if (validNumber.test(elem.value)) {
            lastValid = elem.value;
        } else {
            elem.value = lastValid;
        }
    }

    function updateFlipAnalysis(field, val) {
        console.log('running updateFlipAnalysis');
        let toBeUpdatedAnalysisId = '#' + field + '__analysis';
        $(toBeUpdatedAnalysisId).html(val);

        let arv = parseFloat($('#arv').val());
        let purchase_price = parseFloat($('#purchase_price').val());
        let acquisition_costs = parseFloat($('#acquisition_costs').val());
        let repair_cost = parseFloat($('#repair_cost').val());
        let holding_costs = parseFloat($('#holding_costs').val());
        let selling_costs = parseFloat($('#selling_costs').val());
        let financing_costs = parseFloat($('#financing_costs').val());

        let profit = arv - (purchase_price + acquisition_costs + repair_cost + holding_costs + selling_costs + financing_costs);
        profit = Math.round(profit * 100) / 100;
        let maxPurchase = arv - (acquisition_costs + repair_cost + holding_costs + selling_costs + financing_costs);
        maxPurchase = Math.round(maxPurchase * 100) / 100;

        let profitSign = '';
        if (profit < 0) {
            $('.profit').addClass('negative');
            profitSign = '-';
        } else {
            $('.profit').removeClass('negative');
        }
        $(".profit_sign").html(profitSign);
        $(".profit_price").html(Math.abs(profit));

        let maxPurchaseSign = '';
        if (maxPurchase < 0) {
            $('.max_purchase_price').addClass('negative');
            maxPurchaseSign = '-';
        } else {
            $('.max_purchase_price').removeClass('negative');
        }
        $(".max_purchase_sign").html(maxPurchaseSign);
        $(".max_purchase").html(Math.abs(maxPurchase));

        updateFlipEntry(field, val);
    }

    function updateFlipDetailLineAmount(data, detail_total, flip_entry) {
        console.log('running updateFlipDetailLineAmount');
        let total = 0;
        data.forEach(item => {
            let amount = item.amount ? parseFloat(item.amount) : 0;
            total += amount;
        });
        $('#' + flip_entry).val(total);
        $('.' + detail_total).html(total);
        updateFlipAnalysis(flip_entry, total);
    }

    /**
     * change flip entry value
     */
    // change purchase_price value
    $(document).on('change', '#purchase_price', function () {
        let field = $(this).attr('id');
        let val = $(this).val();
        updateFlipAnalysis(field, val);
    });

    // change arv value
    $(document).on('change', '#arv', function () {
        let field = $(this).attr('id');
        let val = $(this).val();
        updateFlipAnalysis(field, val);
    });

    // change acquisition_costs value
    $(document).on('change', '#acquisition_costs', function () {
        let field = $(this).attr('id');
        let val = $(this).val();
        updateFlipAnalysis(field, val);
    });

    // change holding_costs value
    $(document).on('change', '#holding_costs', function () {
        let field = $(this).attr('id');
        let val = $(this).val();
        updateFlipAnalysis(field, val);
    });

    // change selling_costs value
    $(document).on('change', '#selling_costs', function () {
        let field = $(this).attr('id');
        let val = $(this).val();
        updateFlipAnalysis(field, val);
    });

    // change financing_costs value
    $(document).on('change', '#financing_costs', function () {
        let field = $(this).attr('id');
        let val = $(this).val();
        updateFlipAnalysis(field, val);
    });


    /**
     * detail
     */

    const DETAILED_ACQUISITION_COSTS = 'detailed_acquisition_costs';
    const DETAILED_HOLDING_COSTS = 'detailed_holding_costs';
    const DETAILED_SELLING_COSTS = 'detailed_selling_costs';
    const DETAILED_FINANCING_COSTS = 'detailed_financing_costs';

    function generateLine(item = null, from, id) {
        return `<tr>
                    <td>
                        <input type="text" class="form-control" value="${item ? item.item : ''}" data-from="item" data-index="${id}">
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${item ? item.amount : ''}" data-from="amount" data-index="${id}" oninput="validateNumber(this);">
                    </td>
                    <td><i class="fa fa-times row_remove" data-from="${from}" data-index="${id}"></i></td>
                </tr>`;
    }

    function removeRow(from, id) {
        console.log('removing...', id);
        let data = [];
        let objIndex = 0;
        switch (from) {
            case DETAILED_ACQUISITION_COSTS:
                objIndex = acquisition_detail.findIndex((obj => obj.id === id));
                if (acquisition_detail[objIndex]['amount'] !== '' && acquisition_detail[objIndex]['amount'] !== 0) {
                    acquisition_detail = acquisition_detail.filter(item => item.id !== id);
                    updateFlipDetailLineAmount(acquisition_detail, 'acquisition-detail-total', 'acquisition_costs');
                } else {
                    acquisition_detail = acquisition_detail.filter(item => item.id !== id);
                }

                data = acquisition_detail;
                console.log('remaining acquisition detail>>>', acquisition_detail);
                break;
            case DETAILED_HOLDING_COSTS:
                objIndex = holding_detail.findIndex((obj => obj.id === id));
                if (holding_detail[objIndex]['amount'] !== '' && holding_detail[objIndex]['amount'] !== 0) {
                    holding_detail = holding_detail.filter(item => item.id !== id);
                    updateFlipDetailLineAmount(holding_detail, 'holding-detail-total', 'holding_costs');
                } else {
                    holding_detail = holding_detail.filter(item => item.id !== id);
                }
                data = holding_detail;
                console.log('remaining holding detail>>>', holding_detail);
                break;
            case DETAILED_SELLING_COSTS:
                objIndex = selling_detail.findIndex((obj => obj.id === id));
                if (selling_detail[objIndex]['amount'] !== '' && selling_detail[objIndex]['amount'] !== 0) {
                    selling_detail = selling_detail.filter(item => item.id !== id);
                    updateFlipDetailLineAmount(selling_detail, 'selling-detail-total', 'selling_costs');
                } else {
                    selling_detail = selling_detail.filter(item => item.id !== id);
                }
                data = selling_detail;
                console.log('remaining selling detail>>>', selling_detail);
                break;
            case DETAILED_FINANCING_COSTS:
                objIndex = financing_detail.findIndex((obj => obj.id === id));
                if (financing_detail[objIndex]['amount'] !== '' && financing_detail[objIndex]['amount'] !== 0) {
                    financing_detail = financing_detail.filter(item => item.id !== id);
                    updateFlipDetailLineAmount(financing_detail, 'financing-detail-total', 'financing_costs');
                } else {
                    financing_detail = financing_detail.filter(item => item.id !== id);
                }
                data = financing_detail;
                console.log('remaining financing detail>>>', financing_detail);
                break;
            default:
                break;
        }

        updateFlipDetail(from, data);
    }

    // remove row
    $(document).on('click', '.row_remove', function () {
        let index = $(this).data('index');
        let from = $(this).data('from');
        $(this).parent().parent().remove();
        removeRow(from, index);
    });


    /**
     * acquisition detail
     */
    // show detail
    $('.show-acquisition-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');

        $('.acquisition-detail-body').html('');
        if (acquisition_detail && acquisition_detail.length) {
            let tbodyHTML = '';
            let total = 0;
            acquisition_detail.forEach(function (item) {
                let tr = generateLine(item, DETAILED_ACQUISITION_COSTS, item.id);
                let amount = item.amount ? parseFloat(item.amount) : 0;
                total += amount;
                tbodyHTML += tr;
            });
            $('.acquisition-detail-total ').html(total); // update detail total
            $('.acquisition-detail-body').html(tbodyHTML);
        }

        $('.acquisition-detail').toggle();
    });
    // add line
    $('.add-acquisition-detail-line').click(function () {
        let id = (acquisition_detail && acquisition_detail.length) ? getDetailLastId(acquisition_detail) : 0;
        let row_data = {
            id: id,
            item: '',
            amount: ''
        };
        let tr = generateLine(row_data, DETAILED_ACQUISITION_COSTS, id);
        $('.acquisition-detail-body').append(tr);
        if (!id) {
            acquisition_detail = [];
        }
        acquisition_detail.push(row_data);
        updateFlipDetail(DETAILED_ACQUISITION_COSTS, acquisition_detail);
    });
    $(document).on('focus', '.acquisition-detail-body input', function () {
        lastValid = '';
    });
    // update detail line field
    $(document).on('change', '.acquisition-detail-body input', function () {
        lastValid = '';
        let index = $(this).data('index');
        let from = $(this).data('from'); // amount or item
        let val = $(this).val();
        // console.log('changing acquisition detail field...', index, from, val);

        let objIndex = acquisition_detail.findIndex((obj => obj.id === index));
        // console.log('updated...', acquisition_detail);

        acquisition_detail[objIndex][from] = val;
        updateFlipDetail(DETAILED_ACQUISITION_COSTS, acquisition_detail);
        if (from === 'amount') {
            // console.log('update flip entry, flip analysis, profit, max purchase!!!');
            updateFlipDetailLineAmount(acquisition_detail, 'acquisition-detail-total', 'acquisition_costs');
        }
    });


    /**
     * holding detail
     */
    // show detail
    $('.show-holding-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');

        $('.holding-detail-body').html('');
        if (holding_detail && holding_detail.length) {
            let tbodyHTML = '';
            let total = 0;
            holding_detail.forEach(function (item) {
                let tr = generateLine(item, DETAILED_HOLDING_COSTS, item.id);
                let amount = item.amount ? parseFloat(item.amount) : 0;
                total += amount;
                tbodyHTML += tr;
            });
            $('.holding-detail-total ').html(total); // update detail total
            $('.holding-detail-body').html(tbodyHTML);
        }

        $('.holding-detail').toggle();
    });
    // add line
    $('.add-holding-detail-line').click(function () {
        let id = (holding_detail && holding_detail.length) ? getDetailLastId(holding_detail) : 0;
        let row_data = {
            id: id,
            item: '',
            amount: ''
        };
        let tr = generateLine(row_data, DETAILED_HOLDING_COSTS, id);
        $('.holding-detail-body').append(tr);
        if (!id) {
            holding_detail = [];
        }
        holding_detail.push(row_data);
        updateFlipDetail(DETAILED_HOLDING_COSTS, holding_detail);
    });
    $(document).on('focus', '.holding-detail-body input', function () {
        lastValid = '';
    });
    // update detail line field
    $(document).on('change', '.holding-detail-body input', function () {
        lastValid = '';
        let index = $(this).data('index');
        let from = $(this).data('from'); // amount or item
        let val = $(this).val();

        let objIndex = holding_detail.findIndex((obj => obj.id === index));

        holding_detail[objIndex][from] = val;
        updateFlipDetail(DETAILED_HOLDING_COSTS, holding_detail);
        if (from === 'amount') {
            updateFlipDetailLineAmount(holding_detail, 'holding-detail-total', 'holding_costs');
        }
    });

    /**
     * selling detail
     */
    // show detail
    $('.show-selling-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');

        $('.selling-detail-body').html('');
        if (selling_detail && selling_detail.length) {
            let tbodyHTML = '';
            let total = 0;
            selling_detail.forEach(function (item) {
                let tr = generateLine(item, DETAILED_SELLING_COSTS, item.id);
                let amount = item.amount ? parseFloat(item.amount) : 0;
                total += amount;
                tbodyHTML += tr;
            });
            $('.selling-detail-total ').html(total); // update detail total
            $('.selling-detail-body').html(tbodyHTML);
        }

        $('.selling-detail').toggle();
    });
    // add line
    $('.add-selling-detail-line').click(function () {
        let id = (selling_detail && selling_detail.length) ? getDetailLastId(selling_detail) : 0;
        let row_data = {
            id: id,
            item: '',
            amount: ''
        };
        let tr = generateLine(row_data, DETAILED_SELLING_COSTS, id);
        $('.selling-detail-body').append(tr);
        if (!id) {
            selling_detail = [];
        }
        selling_detail.push(row_data);
        updateFlipDetail(DETAILED_SELLING_COSTS, selling_detail);
    });
    $(document).on('focus', '.selling-detail-body input', function () {
        lastValid = '';
    });
    // update detail line field
    $(document).on('change', '.selling-detail-body input', function () {
        lastValid = '';
        let index = $(this).data('index');
        let from = $(this).data('from'); // amount or item
        let val = $(this).val();

        let objIndex = selling_detail.findIndex((obj => obj.id === index));

        selling_detail[objIndex][from] = val;
        updateFlipDetail(DETAILED_SELLING_COSTS, selling_detail);
        if (from === 'amount') {
            updateFlipDetailLineAmount(selling_detail, 'selling-detail-total', 'selling_costs');
        }
    });


    /**
     * financing detail
     */
    // show detail
    $('.show-financing-detail').click(function () {
        $(this).toggleClass('fa-plus');
        $(this).toggleClass('fa-minus');

        $('.financing-detail-body').html('');
        if (financing_detail && financing_detail.length) {
            let tbodyHTML = '';
            let total = 0;
            financing_detail.forEach(function (item) {
                let tr = generateLine(item, DETAILED_FINANCING_COSTS, item.id);
                let amount = item.amount ? parseFloat(item.amount) : 0;
                total += amount;
                tbodyHTML += tr;
            });
            $('.financing-detail-total ').html(total); // update detail total
            $('.financing-detail-body').html(tbodyHTML);
        }

        $('.financing-detail').toggle();
    });
    // add line
    $('.add-financing-detail-line').click(function () {
        let id = (financing_detail && financing_detail.length) ? getDetailLastId(financing_detail) : 0;
        let row_data = {
            id: id,
            item: '',
            amount: ''
        };
        let tr = generateLine(row_data, DETAILED_FINANCING_COSTS, id);
        $('.financing-detail-body').append(tr);
        if (!id) {
            financing_detail = [];
        }
        financing_detail.push(row_data);
        updateFlipDetail(DETAILED_FINANCING_COSTS, financing_detail);
    });
    $(document).on('focus', '.financing-detail-body input', function () {
        lastValid = '';
    });
    // update detail line field
    $(document).on('change', '.financing-detail-body input', function () {
        lastValid = '';
        let index = $(this).data('index');
        let from = $(this).data('from'); // amount or item
        let val = $(this).val();

        let objIndex = financing_detail.findIndex((obj => obj.id === index));

        financing_detail[objIndex][from] = val;
        updateFlipDetail(DETAILED_FINANCING_COSTS, financing_detail);
        if (from === 'amount') {
            updateFlipDetailLineAmount(financing_detail, 'financing-detail-total', 'financing_costs');
        }
    });


</script>