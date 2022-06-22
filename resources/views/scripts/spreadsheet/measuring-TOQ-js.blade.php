<script>
    var globalTOQ = JSON.parse(localStorage.getItem('globalTOQ'));
    {{--var checkSheetPage = {{$page_info['page_id']}};--}}

    // display get measuring TOQ
    if (globalTOQ) {
        var getTOQHtml = `<div class="btn-group-sm" id="TOQ_html"
                    style="position: absolute; top: 25px; left: 800px; box-shadow: 2px 2px 4px 5px #b7d0e9">
                <button type="button" class="btn btn-outline-danger cancelTOQ">Cancel</button>
                <button type="button" class="btn btn-outline-success">Ready to get measurement</button>
            </div>`;
        document.write(getTOQHtml);
    }

    // cancel get measuring TOQ
    $(document).on('click', '.cancelTOQ', function () {
        let redirectURL = globalTOQ.redirect_url;
        localStorage.removeItem('globalTOQ');
        document.getElementById('TOQ_html').remove();
        window.location.href = redirectURL;
    });


    // get TOQ by clicking measurement value from leaf left panel
    function getTOQ(param, id) {
        if (globalTOQ) {
            let val = param.dataset.length ? param.dataset.length : param.dataset.area;
            val = Math.round(val/12*100)/100;
            console.log('get TOQ...', val);
            if (globalTOQ.measureId) { // get measuring by interview
                let measureId = globalTOQ.measureId;
                let values = globalTOQ.values;
                values.forEach(el => {
                    if (el.id === measureId) {
                        el.value = val;
                    }
                });
                globalTOQ['values'] = values;
                localStorage.setItem('globalTOQ', JSON.stringify(globalTOQ));
                window.location.href = globalTOQ.redirect_url;
            } else {
                let url = '{{url('sheet/get_measurement_TOQ')}}';
                let type = 'get_measurement_TOQ';
                let data = {}; // SSItemId, TOQ
                data.SSItemId = globalTOQ.SSItemId;
                data.TOQ = val;
                console.log('get TOQ ajax call...', url, data, type);
                ajaxCall(url, data, type);
            }
        } else {
            // select segment on the left
            selectSegment(id);
        }
    }


    // get TOQ by clicking total on measurement leaf
    // type is point, line, area
    function getTOQByMeasurement(param, type) {
        if (globalTOQ) {
            let val = 0;
            if (type === 'point') {
                val = param.dataset.count;
            } else if (type === 'line') {
                val = param.dataset.length;
                val = Math.round(val/12*100)/100;
            } else if (type === 'area') {
                val = param.dataset.area;
                val = Math.round(val/12*100)/100;
            }
            console.log('get TOQ...', val);
            if (globalTOQ.measureId) { // get measuring by interview
                let measureId = globalTOQ.measureId;
                let values = globalTOQ.values;
                values.forEach(el => {
                    if (el.id === measureId) {
                        el.value = val;
                    }
                });
                globalTOQ['values'] = values;
                localStorage.setItem('globalTOQ', JSON.stringify(globalTOQ));
                window.location.href = globalTOQ.redirect_url;
            } else {
                let url = '{{url('sheet/get_measurement_TOQ')}}';
                let type = 'get_measurement_TOQ';
                let data = {}; // SSItemId, TOQ
                data.SSItemId = globalTOQ.SSItemId;
                data.TOQ = val;
                console.log('get TOQ ajax call...', url, data, type);
                ajaxCall(url, data, type);
            }
        } else {
            // select segment on the left
            let ids = param.dataset.selected_ids;
            if (ids.length) {
                selectMultiSegments(ids);
            } else {
                // orphaned measurement
                console.log('orphaned measurement');
            }

        }
    }

</script>