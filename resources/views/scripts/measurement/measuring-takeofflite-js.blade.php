<script>
    /**
     * Global variables
     */

        // sheet information variables
    var img_url = '{!! asset($sheet->file) !!}';
    var sheet_x = '{{$sheet->x}}';
    var sheet_y = '{{$sheet->y}}';
    var sheet_width = '{{$sheet->width}}';
    var sheet_height = '{{$sheet->height}}';
    console.log('sheet info: x, y, width, height', sheet_x, sheet_y, sheet_width, sheet_height);
    var segmentObjIdFromURL = '{{$segment_id}}';
    var canvas = null;
    var gfx_holder = null;


    /**
     * Ajax call
     * @param url
     * @param data
     * @param type
     **/

    var _token = $("input[name=_token]").val();
    var sheet_id = '{{$sheet->id}}';

    function ajaxCall(url, data, type) {
        console.log('ajax call>>>', url, data, type);

        $.ajax({
            url: url,
            method: "POST",
            data: {
                _token: _token,
                sheet_id: sheet_id,
                data: data
            },
            success: function (res) {
                console.log('ajax call return result>>>', res);
                let object_id;
                if (data.object_id) {
                    object_id = data.object_id;
                }

                if (type === 'create_measurement') {
                    tempMeasurementId = 'measurement_' + res.id;

                    // add measurement item to left sidebar
                    let html = `<li>
                                    <i class="fa fa-eye measurement-eye" id="total_segment_ids_${tempMeasurementId}" data-id="${totalSegmentIds}"></i>
                                    <a href="#${tempMeasurementId}" data-toggle="collapse" aria-expanded="true" class="dropdown-toggle">
                                        ${measuring_name}
                                    </a>
                                    <div class="total_measurement_info" id="info_${tempMeasurementId}">`;

                    if (isMeasuring === 'line' || isMeasuring === 'area' || isMeasuring === 'polyline') {
                        let total_length_span_id = 'total_length_span_' + tempMeasurementId;
                        let total_length_toq_id = 'total_length_toq_' + tempMeasurementId;
                        let tempText = isMeasuring === 'line' || isMeasuring === 'polyline' ? 'Total Length' : 'Total Perimeter';

                        html += `<span onclick="getTOQByMeasurement(this, 'line')" data-selected_ids='${totalSegmentIds}' data-length="${totalLength}" id="${total_length_toq_id}">
                             ${tempText}: <span id="${total_length_span_id}"></span></span>`;

                        if (isMeasuring === 'area') {
                            let total_area_span_id = 'total_area_span_' + tempMeasurementId;
                            let total_area_toq_id = 'total_area_toq_' + tempMeasurementId;

                            html += `&nbsp;<span onclick="getTOQByMeasurement(this, 'area')" data-selected_ids='${totalSegmentIds}' data-area="${totalArea}" id="${total_area_toq_id}">
                                    Total Area: <span id="${total_area_span_id}"></span></span>`;
                        }
                    } else if (isMeasuring === 'point') {
                        let total_count_span_id = 'total_count_span_' + tempMeasurementId;
                        let total_count_toq_id = 'total_count_toq_' + tempMeasurementId;
                        let tempText = 'Total Count';

                        html += `<span onclick="getTOQByMeasurement(this, 'point')" data-selected_ids='${totalSegmentIds}' data-count="${totalCount}" id="${total_count_toq_id}">
                             ${tempText}: <span id="${total_count_span_id}"></span></span>`;
                    }


                    html += `</div>
                                <ul class="list-unstyled collapse show" id="${tempMeasurementId}" style=""></ul>
                            </li>`;

                    $('#measurement_list').append(html);
                }


                if (type === 'get_measurement_TOQ') {
                    let tmpLocalStorage = JSON.parse(localStorage.getItem('globalTOQ'));
                    let redirect_url = tmpLocalStorage['redirect_url'];
                    localStorage.removeItem('globalTOQ');
                    window.location.href = redirect_url;
                }
            }
        });
    }


    /**
     * Show/Hide segment
     **/

    $('#sheet_sidebar').on('click', '.eye', function () {
        $(this).toggleClass("fa-eye fa-eye-slash");
        let sheetObjectId = $(this).data('id');
        let obj = canvas.getFigure(sheetObjectId);
        if (!obj) {
            obj = canvas.getLine(sheetObjectId);
        }
        let status = !obj.isVisible();
        obj.setVisible(status);

        // toggle parent fa-eye, fa-eye-slash
        let cntEyes = 0;
        let cntSlashEyes = 0;
        let cntEyeClass = 0;
        let allChildren = $(this).parent().parent().parent().find('.eye');
        allChildren.each(function (idx, val) {
            cntEyes++;
            let hasEyeSlash = $(val).hasClass('fa-eye-slash');
            if (hasEyeSlash) {
                cntSlashEyes++;
            }

            let hasEye = $(val).hasClass('fa-eye');
            if (hasEye) {
                cntEyeClass++;
            }
        });
        let parentEye = $(this).parent().parent().parent().parent().find('.measurement-eye');
        if (cntEyes === cntSlashEyes) {
            if ($(parentEye).hasClass('fa-eye')) {
                $(parentEye).toggleClass("fa-eye fa-eye-slash");
            }
        } else {
            if ($(parentEye).hasClass('fa-eye-slash')) {
                $(parentEye).toggleClass("fa-eye fa-eye-slash");
            }
        }
    });
    $('#sheet_sidebar').on('click', '.measurement-eye', function () {
        $(this).toggleClass("fa-eye fa-eye-slash");
        // toggle children fa-eye, fa-eye-slash
        let eyes = $(this).parent().find('.eye');
        eyes.each(function (idx, val) {
            $(val).toggleClass("fa-eye fa-eye-slash");
        });

        let sheetObjectId = $(this).data('id');
        let sheetObjectIds = sheetObjectId.split(',');
        for (let i = 0; i < sheetObjectIds.length; i++) {
            let obj = canvas.getFigure(sheetObjectIds[i]);
            if (!obj) {
                obj = canvas.getLine(sheetObjectIds[i]);
            }
            let status = !obj.isVisible();
            obj.setVisible(status);
        }
    });


    /**
     * Set zoomIn/Out
     */

    function updateZoom(zoom) {
        $(".zoomResetBtn").text((parseInt((1.0 / zoom) * 100)) + "%");
        $.ajax({
            url: '{{route("sheet.update_zoom")}}',
            method: "POST",
            data: {
                _token: _token,
                sheet_id: sheet_id,
                zoom: zoom,
                // x: sheet_x * zoom,
                // y: sheet_y * zoom
            },
            success: function (res) {
                console.log(res);
            }
        });
    }

    $('.zoomInBtn').click(function () {
        // let zoom = Math.round(canvas.getZoom() * 1.1 * 100) / 100;
        let wheelDelta = 120;
        wheelDelta = wheelDelta / 1024;
        let zoom = (Math.min(5, Math.max(0.1, canvas.getZoom() + wheelDelta)) * 10000 | 0) / 10000;
        canvas.setZoom(zoom, true);
        updateZoom(zoom);
    });
    $('.zoomResetBtn').click(function () {
        let zoom = 1.0;
        canvas.setZoom(zoom, true);
        updateZoom(zoom);
    });
    $('.zoomOutBtn').click(function () {
        let wheelDelta = -120;
        wheelDelta = wheelDelta / 1024;
        let zoom = (Math.min(5, Math.max(0.1, canvas.getZoom() + wheelDelta)) * 10000 | 0) / 10000;
        // let zoom = Math.round(canvas.getZoom() * 0.9 * 100) / 100;
        canvas.setZoom(zoom, true);
        updateZoom(zoom);
    });


    /**
     * Scale
     */

    var scaleLine = null;
    var feet = '{{$sheet->feet}}' ? '{{$sheet->feet}}' : 0;
    var inch = '{{$sheet->inch}}' ? '{{$sheet->inch}}' : 0;
    var scale = '{{$sheet->scale}}' ? '{{$sheet->scale}}' : 1;
    var isSetScale = false;

    function stopDrawingScale(event) {
        if (!isFirstPoint) {
            let coords = getMousePosition(event);
            let x = coords.x;
            let y = coords.y;
            scaleLine.setEndPoint(x, y);
        }
    }

    function startDrawingScale(event) {
        if (isFirstPoint) {
            let coords = getMousePosition(event);
            let x = coords.x;
            let y = coords.y;

            scaleLine = new draw2d.shape.basic.Line({color: "#eb7006", deleteable: false});
            scaleLine.setStroke(2);
            scaleLine.setStartPoint(x, y);
            scaleLine.setEndPoint(x, y);
            canvas.add(scaleLine);

            gfx_holder.onmouseout = null;
            gfx_holder.onmousemove = stopDrawingScale;
            isFirstPoint = false;
        } else {
            // second point clicked
            gfx_holder.onmousemove = null;
            isFirstPoint = true;
            exitMeasuring();
//            canvas.getCommandStack().commitTransaction();
            $('#set_scale_modal').modal({backdrop: 'static', keyboard: false});
        }
    }

    // entering set scale mode
    function enteringSetScale() {
        $("#confirm_scale_modal").modal('hide');
        isMeasuring = 'scale';
        let measuring_status = startMeasuring();
        if (measuring_status) {
            isFirstPoint = true;
            gfx_holder.onmousedown = startDrawingScale;
        }

        $("#current_function_in").html('SET SCALE IN PROGRESS');
        $("#current_function_in").fadeIn();
    }

    // open set scale modal
    function openSetScaleModal() {
        $('#confirm_scale_modal').modal({backdrop: 'static', keyboard: false});
    }

    // cancel set scale
    function cancelSetScale() {
        canvas.remove(scaleLine);
        scaleLine.unselect();
        scaleLine = null;
        cancelMeasuring();
    }

    // set scale
    function setScale() {
        let feet = parseFloat($('#scale_feet').val());
        let inch = parseFloat($('#scale_inch').val());
        let lineLength = scaleLine.getLength().toFixed(2);
        if (isNaN(inch) || isNaN(feet)) {
            toastr.error('Invalid feet and inch');
        } else {
            isSetScale = true;
            let scale_txt = feet + "' " + inch + '"';
            $("#scale_span").html(scale_txt);
            $("#set_scale_modal").modal('hide');


            let tempScale = parseFloat((12 * parseInt(feet) + parseInt(inch)) / lineLength).toFixed(4);
            scale = tempScale;
            canvas.remove(scaleLine);
            scaleLine = null;

            let url = "{{ url('sheet/set_scale') }}";
            let data = {
                feet: feet,
                inch: inch,
                scale: tempScale
            };
            let type = 'setscale';
            ajaxCall(url, data, type);
            if (isMeasuring) {
                openCreateMeasurementModal();
            }
        }

    }

    function showScale() {
        if ((feet !== '0' && feet) || (inch !== '0' && inch)) {
            $("#scale_span").html(feet + "' " + inch + '"');
            isSetScale = true;
        } else {
            isSetScale = false;
        }
    }


    /**
     * Start, Exit, Cancel, Abort measuring
     **/

    var currentMeasuringObj = null;

    function startMeasuring() {
        console.log('start measuring ...', isMeasuring);
        $("#gfx_holder").css('cursor', 'crosshair');
        $(".draw2d_shape_basic_Image").css('cursor', 'crosshair');
        $("#drawing-toolbar ul li").css("color", "darkgrey");
        $('.abort-measuring').css("color", "orangered");
        $('.stop-measuring').css("color", "orangered");
        let activeTool = `.${isMeasuring}`;
        $(activeTool).css("color", "white");
        let current_progress_in = '';

        // show what function you are performing
        switch (isMeasuring) {
            case 'point':
                current_progress_in = 'POINT MEASUREMENT IN PROGRESS';
                break;
            case 'line':
                current_progress_in = 'LINE MEASUREMENT IN PROGRESS';
                break;
            case 'polyline':
                current_progress_in = 'POLYLINE MEASUREMENT IN PROGRESS';
                break;
            case 'area':
                current_progress_in = 'AREA MEASUREMENT IN PROGRESS';
                break;
            default:
                break;
        }
        $("#current_function_in").html(current_progress_in);
        $("#current_function_in").fadeIn();
        return true;
    }

    function exitMeasuring() {
        if (isMeasuring) {
            console.log('exit measuring');

            // remove if orphaned measurement
            let measurementLeaf = $("#measurement_list li a").filter(function () {
                return this.innerHTML.trim() === measuring_name;
            });
            let measurementEye = $(measurementLeaf).parent().find('.measurement-eye');
            let segmentIds = measurementEye.attr('data-id');
            if (!segmentIds && isMeasuring !== 'scale') {
                let measurementId = measurementEye.attr('id').replace('total_segment_ids_measurement_', '');
                // remove measurement name from saved measurementNameList
                let tempIndex = measurementNameList.indexOf(measuring_name);
                if (tempIndex > -1) {
                    measurementNameList.splice(tempIndex, 1);
                }

                if (line) {
                    canvas.remove(line);
                }

                if (polyLine) {
                    canvas.remove(polyLine);
                }

                selectedOrphanedId = measurementId;
                deleteOrphanedMeasurement();
            }

            $("#gfx_holder").css('cursor', 'pointer');
            $(".draw2d_shape_basic_Image").css('cursor', 'pointer');
            $("#drawing-toolbar ul li").css("color", "white");

            $('.abort-measuring').css("color", "white");
            $('.stop-measuring').css("color", "white");

            let activeTool = `.${isMeasuring}`;
            $(activeTool).css("color", "inherit");

            isMeasuring = '';
            isFirstPoint = true;
            measuring_name = '';

            gfx_holder.onmousedown = null;
            gfx_holder.onmousemove = null;
            gfx_holder.onmouseout = null;
            gfx_holder.onclick = null;
            gfx_holder.ondblclick = null;

            polyLine = null;
            line = null;
            toBeUpdatedObj = null;
            vertex = [];
            $('.tooltip').remove();

            $("#current_function_in").fadeOut();
        }
    }

    function cancelMeasuring() {
        if (isMeasuring) {
            console.log('cancel measuring');
            $("#gfx_holder").css('cursor', 'pointer');
            $(".draw2d_shape_basic_Image").css('cursor', 'pointer');
            $("#drawing-toolbar ul li").css("color", "white");

            $('.abort-measuring').css("color", "white");
            $('.stop-measuring').css("color", "white");

            let activeTool = `.${isMeasuring}`;
            $(activeTool).css("color", "inherit");

            isMeasuring = '';
            isFirstPoint = true;
            measuring_name = '';

            gfx_holder.onmousedown = null;
            gfx_holder.onmousemove = null;
            gfx_holder.onmouseout = null;
            gfx_holder.onclick = null;
            gfx_holder.ondblclick = null;

            polyLine = null;
            line = null;
            toBeUpdatedObj = null;
            vertex = [];
            polygon = null;
            $('.tooltip').remove();

            $("#current_function_in").fadeOut();
        }
    }

    function abortMeasuring() {
        if (isMeasuring) {
            console.log('abort measuring');
            $("#gfx_holder").css('cursor', 'pointer');
            $(".draw2d_shape_basic_Image").css('cursor', 'pointer');
            $("#drawing-toolbar ul li").css("color", "white");

            $('.abort-measuring').css("color", "white");
            $('.stop-measuring').css("color", "white");

            let activeTool = `.${isMeasuring}`;
            $(activeTool).css("color", "inherit");
            $('.tooltip').remove();

            let id = '', leftSegmentId, parent, countOfLeaf, measurementId, deletedObjectsId = [];
            if (line) {
                id = line.id;
                canvas.remove(line);
            }

            if (polyLine) {
                canvas.remove(polyLine);
                id = polyLine.id;
            }

            if (id) {
                leftSegmentId = document.getElementById(id);
                parent = leftSegmentId.parentElement;
                leftSegmentId.remove();
                countOfLeaf = parent.childElementCount;
                if (countOfLeaf === 0) {
                    measurementId = parent.id.split('_')[1];
                    deletedObjectsId.push({segment: 0, id: measurementId});

                    // remove measurement name from saved measurementNameList
                    let measurementLeafName = parent.parentElement.querySelector('li a').textContent.trim();
                    let tempIndex = measurementNameList.indexOf(measurementLeafName);
                    if (tempIndex > -1) {
                        measurementNameList.splice(tempIndex, 1);
                    }

                    parent.parentElement.remove();
                    let url = '{{url('sheet/remove_object')}}';
                    let data = {
                        ids: deletedObjectsId
                    };
                    let typeg = 'remove_object';
//                console.log('removing measurement leaf on the left...', data);
                    ajaxCall(url, data, type);
                }
            }

            isMeasuring = '';
            isFirstPoint = true;
            measuring_name = '';

            gfx_holder.onmousedown = null;
            gfx_holder.onmousemove = null;
            gfx_holder.onmouseout = null;
            gfx_holder.onclick = null;
            gfx_holder.ondblclick = null;

            polyLine = null;
            line = null;
            toBeUpdatedObj = null;
            countOfPolygonVertex = 0;
            isAddedAreaToLeft = false;

            $("#current_function_in").fadeOut();
        }
    }


    /**
     * Mouse move
     **/

    var scrollLeft;
    var scrollTop;

    function MoveMousemoveX(e) {
        scrollLeft = $('#gfx_holder').scrollLeft() + (20);
        $('#gfx_holder').animate({
            scrollLeft: scrollLeft
        }, 1, 'linear');
    }

    function MoveMousemove2X(e) {
        scrollLeft = $('#gfx_holder').scrollLeft() - (20);
        $('#gfx_holder').animate({
            scrollLeft: scrollLeft
        }, 1, 'linear');
    }

    function MoveMousemoveY(e) {
        scrollTop = $('#gfx_holder').scrollTop() + (20);
        $('#gfx_holder').animate({
            scrollTop: scrollTop
        }, 1, 'linear');
    }

    function MoveMousemove2Y(e) {
        scrollTop = $('#gfx_holder').scrollTop() - (20);
        $('#gfx_holder').animate({
            scrollTop: scrollTop
        }, 1, 'linear');
    }


    /**
     * Format text with scale
     **/

    function formatValToFeetInch(param) {
        let val = parseFloat(param);
        let quotient = Math.floor(val / 12);
        let remainder = (val % 12).toFixed(0);
        let result = '';
        if (quotient === 0) {
            result = remainder + '"';
        } else {
            result = quotient + "' " + remainder + '"';
        }
        return result;
    }


    /**
     * Select segment by measurement on the left
     **/

    var selectedMultiSegements = [];
    var selectedOrphanedId = null;

    function selectSegment(id) {
        let element = document.getElementById(id);
        let obj = canvas.getFigure(id);
        if (!obj) {
            obj = canvas.getLine(id);
        }
        if (element.classList.contains('segment-selected')) {
            obj.unselect();
            element.classList.remove('segment-selected');
        } else {
            let selectedSegmentsLeaf = document.querySelectorAll('.segment-selected');
            selectedSegmentsLeaf.forEach(function (item) {
                item.classList.remove('segment-selected');
            });
            element.classList.add('segment-selected');
            canvas.setCurrentSelection(obj);
            selectedMultiSegements.push(id);
        }
    }


    /**
     * select segments by clicking on measurement leaf
     **/

    function selectMultiSegments(param) {
        let figures = new draw2d.util.ArrayList();
        let ids = param.split(',');
        let selectedSegmentsLeaf = document.querySelectorAll('.segment-selected');
        selectedSegmentsLeaf.forEach(function (item) {
            item.classList.remove('segment-selected');
        });
        for (let i = 0; i < ids.length; i++) {
            let element = document.getElementById(ids[i]);
            element.classList.add('segment-selected');
            selectedMultiSegements.push(ids[i]);
            let obj = canvas.getFigure(ids[i]);
            if (!obj) {
                obj = canvas.getLine(ids[i]);
            }
            figures.add(obj);
        }
        canvas.setCurrentSelection(figures);
    }


    /**
     * Handling orphaned measurement
     **/
    function deleteOrphanedMeasurement() {
        if (selectedOrphanedId) {
            let url = '{{url('sheet/remove_orphaned_measurement')}}';
            $.ajax({
                url: url,
                method: "POST",
                data: {
                    _token: _token,
                    measurement_id: selectedOrphanedId
                },
                success: function (res) {
                    console.log(res);
                    $("#total_segment_ids_measurement_" + selectedOrphanedId).parent().remove();
                    selectedOrphanedId = null;
                }
            });
        }
    }

    $('#measurement_list').on('click', '.dropdown-toggle', function () {
        let measurementEye = $(this).parent().find('.measurement-eye');
        let segmentIds = measurementEye.attr('data-id');

        // if orphaned measurement
        if (!segmentIds) {
            let measurementId = measurementEye.attr('id').replace('total_segment_ids_measurement_', '');
            $(this).parent().addClass('segment-selected');
            selectedOrphanedId = measurementId;
        }
    });

    // delete key event
    document.addEventListener('keydown', function (event) {
        const key = event.key;
        if (key === "Delete") {
            deleteOrphanedMeasurement();
        }
    });


    /**
     * Remove selected objects
     **/

    function removeObjects() {
        let deletedObjectsId = [];
        selectedMultiSegements.forEach(function (id) {
            let leftSegmentId = document.getElementById(id);
            let parent = leftSegmentId.parentElement;
            if (leftSegmentId) leftSegmentId.remove();
            let countOfLeaf = parent.childElementCount;
            if (countOfLeaf === 0) {
                // remove measurement name from saved measurementNameList
                let measurementLeafName = parent.parentElement.querySelector('li a').textContent.trim();
                let tempIndex = measurementNameList.indexOf(measurementLeafName);
                if (tempIndex > -1) {
                    measurementNameList.splice(tempIndex, 1);
                }

                let measurementId = parent.id.split('_')[1];
                parent.parentElement.remove();
                deletedObjectsId.push({segment: 0, id: measurementId});
            }

            let obj = canvas.getFigure(id);
            if (!obj) {
                obj = canvas.getLine(id);
            }
            deletedObjectsId.push({segment: 1, id: id});
            canvas.remove(obj);
        });

        if (deletedObjectsId.length) {
            selectedMultiSegements = [];
            let url = '{{url('sheet/remove_object')}}';
            let data = {
                ids: deletedObjectsId
            };
            let type = 'remove_object';
            console.log('deletedObjectsId....', deletedObjectsId);
            //console.log('remove selected objects', url, data, type);
            ajaxCall(url, data, type);
        } else {
            // delete orphaned measurement label
            deleteOrphanedMeasurement();
        }
    }


    /**
     * Move object (point, area)
     **/

    function moveObject(event) {
        console.log('moveObject...', event);
        let obj = event.figure;
        if (obj.cssClass === "draw2d_shape_basic_Image") {
            console.log('don move sheet image');
        } else {
            let url = '{{url('sheet/move_object')}}';
            let type = 'move_object';

            let data = {
                object_id: obj.id,
                newX: obj.x,
                newY: obj.y
            };

            if (obj.cssClass === "draw2d_shape_basic_Polygon") {
                data['verticles'] = [];
                obj.vertices.data.forEach(function (item) {
                    data['verticles'].push({x: item.x, y: item.y});
                });
            } else if (obj.cssClass === "draw2d_shape_composite_Group") {
                let figures = obj.getAssignedFigures();
                console.log('moving figures>>>', figures);
                data['type'] = 'move_sheet';
                data['figures_data'] = [];
                figures.data.forEach(function (figure) {

                    if (figure.cssClass === "draw2d_shape_composite_Group") {
                        console.log('please don move composite group.... :(');
                    } else {
                        if (figure.cssClass === "draw2d_shape_basic_Image") {
                            sheet_x = figure.x;
                            sheet_y = figure.y;

                            data['figures_data'].push({
                                x: figure.x,
                                y: figure.y,
                                figure_type: figure.cssClass,
                                figure_id: sheet_id
                            });
                        } else if (figure.cssClass === "draw2d_shape_basic_Polygon") {
                            let verticles = [];
                            figure.vertices.data.forEach(function (item) {
                                verticles.push({x: item.x, y: item.y});
                            });
                            data['figures_data'].push({
                                x: figure.x,
                                y: figure.y,
                                figure_type: figure.cssClass,
                                figure_id: figure.id,
                                verticles: verticles
                            });
                        } else if (figure.cssClass === "draw2d_shape_basic_Polygon") {
                            let vertex = [];
                            figure.vertices.data.forEach(function (item) {
                                vertex.push({x: item.x, y: item.y});
                            });
                            data['figures_data'].push({
                                x: figure.x,
                                y: figure.y,
                                figure_type: figure.cssClass,
                                figure_id: figure.id,
                                vertex: vertex
                            });
                        } else {
                            data['figures_data'].push({
                                x: figure.x,
                                y: figure.y,
                                figure_type: figure.cssClass,
                                figure_id: figure.id
                            });
                        }
                    }

                });

            }

            console.log('moveObject data...', data);
            ajaxCall(url, data, type);
        }

    }


    /**
     * Move line object
     **/

    function moveLine(command) {
        console.log('move line...', command);
        let obj = command.line;
        let url = '{{url('sheet/move_object')}}';
        let type = 'move_object';
        let data = {
            object_id: obj.id,
            newX: obj.x,
            newY: obj.y
        };
        data['vertex'] = [];
        obj.vertices.data.forEach(function (item) {
            data['vertex'].push({x: item.x, y: item.y});
        });

        console.log('moveLine data...', data);
        ajaxCall(url, data, type);
    }


    /**
     * Update Line (perimeter, point)
     **/

    function updateLine(command) {
        console.log('update line...', command);
        let obj = command.line;
        let url = '{{url('sheet/update_line')}}';
        let type = 'update_line';
        let perimeter, perimeterTxt;
        if (obj.userData.name === 'Polyline') {
            let updated_vertex = [];
            obj.vertices.data.forEach(function (item) {
                updated_vertex.push({x: item.x, y: item.y});
            });

            let calcResult = perimeterAreaCalc(updated_vertex);
            perimeter = calcResult.perimeter;
            perimeterTxt = formatValToFeetInch(perimeter);
        } else { // Line
            perimeter = (obj.getLength() * scale).toFixed(2);
            perimeterTxt = formatValToFeetInch(perimeter);
        }

        obj.userData.perimeter = perimeter;
        obj.userData.perimeterTxt = perimeterTxt;

        let data = {
            object_id: obj.id,
            newX: obj.x,
            newY: obj.y,
            perimeter: perimeter,
            perimeterTxt: perimeterTxt
        };
        data['vertex'] = [];
        obj.vertices.data.forEach(function (item) {
            data['vertex'].push({x: item.x, y: item.y});
        });

        console.log('updateLine data...', data);

        ajaxCall(url, data, type);
    }


    /**
     * Update Area
     **/

    function updateArea(command) {
        console.log('update area...', command);
        let updated_vertex = [];
        let obj = command.commands.data[0].line;
        let url = '{{url('sheet/update_area')}}';
        let type = 'update_area';
        let data = {};

        if (obj.cssClass === "draw2d_shape_basic_PolyLine") {
            obj.vertices.data.forEach(function (item) {
                updated_vertex.push({x: item.x, y: item.y});
            });

            let calcResult = perimeterAreaCalc(updated_vertex);
            let perimeter = calcResult.perimeter;
            let perimeterTxt = formatValToFeetInch(perimeter);

            obj.userData.perimeter = perimeter;
            obj.userData.perimeterTxt = perimeterTxt;

            data = {
                object_id: obj.id,
                perimeter: perimeter,
                perimeterTxt: perimeterTxt,
                vertex: updated_vertex
            };

        } else if (obj.cssClass === "draw2d_shape_basic_Polygon") {
            obj.vertices.data.forEach(function (item) {
                updated_vertex.push({x: item.x, y: item.y});
            });

            let calcResult = perimeterAreaCalc(updated_vertex);
            let area = calcResult.area;
            let areaTxt = formatValToFeetInch(area);
            let perimeter = calcResult.perimeter;
            let perimeterTxt = formatValToFeetInch(perimeter);

            obj.userData.perimeter = perimeter;
            obj.userData.perimeterTxt = perimeterTxt;
            obj.userData.area = area;
            obj.userData.areaTxt = areaTxt;

            data = {
                object_id: obj.id,
                perimeter: perimeter,
                perimeterTxt: perimeterTxt,
                area: area,
                areaTxt: areaTxt,
                vertex: updated_vertex
            };
        }

        console.log('updateArea data...', data);

        ajaxCall(url, data, type);
    }


    /**
     * Get mouse position
     **/

    function getMousePosition(event) {
        let position = canvas.fromDocumentToCanvasCoordinate(event.clientX, event.clientY);
        // console.log('mouse position...', position);
        return position;
    }


    /**
     * Add or remove tooltip to new object
     **/

    function addToolTip(obj) {
        obj.on("mouseenter", function () {
            if (!currentMeasuringObj) {
                showTooltip(obj);
            }
        });
        obj.on("mouseleave", function () {
            hideTooltip();
        });
        obj.on("dragstart", function () {
            hideTooltip(true);
        });
    }


    /**
     * add segment to left sidebar
     * type: addpoint, add_line, add_area
     * data: point, line, area object
     */

    function addSegmentToLeft(type, data) {
        console.log('type data...', type, data);
        // add segment to left sidebar
        let leafText = '';
        let tempText = '';
        switch (type) {
            case 'addpoint':
                leafText = 'Point';
                break;
            case 'add_line':
                leafText = 'Line';
                tempText = 'Length';
                break;
            case 'add_area':
                leafText = 'Area';
                tempText = 'Perimeter';
                break;
            case 'add_polyline':
                leafText = 'Polyline';
                tempText = 'Length';
                break;
            default:
                break;
        }
        let html = `<li id="${data.id}">
                        <a href="javascript:;"><i class="fa fa-eye eye" data-id="${data.id}"></i>
                    <span onclick="selectSegment('${data.id}')">${leafText}</span></a>
                    <br><div class="measurement_info" id="info_${data.id}">`;
        if (type === 'add_line' || type === 'add_area' || type === 'add_polyline') {
            let length_span_id = 'length_span_' + data.id;
            let length_toq_id = 'length_toq_' + data.id;
            html += `<span onclick="getTOQ(this, '${data.id}')" data-length="" id="${length_toq_id}">
                                     ${tempText}: <span id="${length_span_id}"></span></span>`;
            if (type === 'add_area') {
                let area_span_id = 'area_span_' + data.id;
                let area_toq_id = 'area_toq_' + data.id;
                html += `&nbsp;<span onclick="getTOQ(this, '${data.id}')" data-area="" id="${area_toq_id}">
                            Area: <span id="${area_span_id}"></span></span>`;
            }
        }
        html += `</div></li>`;
        document.getElementById(tempMeasurementId).innerHTML += html;
    }


    /**
     * Create measurement
     */

    var measuring_name = '';
    var measuring_color = '#000000';
    var tempMeasurementId; // it will used to add to created measurement html to left sheet sidebar
    var measurementNameList = @json($measurement_name_list);
    var totalLength = 0; // total length of measurement
    var totalArea = 0; // total area of measurement
    var totalSegmentIds = ''; // select all segments of the measurement
    function openCreateMeasurementModal() {
        let title = '';
        switch (isMeasuring) {
            case 'point':
                title = 'Point';
                break;
            case 'line':
                title = 'Line';
                break;
            case 'polyline':
                title = 'Polyline';
                break;
            case 'area':
                title = 'Area';
                break;
            default:
                break;
        }
        // console.log('measurement name list...', measurementNameList);
        document.getElementById('measurement_title_span').innerHTML = title;
        $('#segment_name').val("");
        $('#create_measurement_modal').modal({backdrop: 'static', keyboard: false});
    }

    function createMeasurement() {
        measuring_name = $('#segment_name').val();
        measuring_color = $('#segment_color').val();

        if (measurementNameList.includes(measuring_name)) {
            console.log('measurement name already exists');
            toastr.error('Measurement name already exists');
        } else {
            if (measuring_name) {
                measurementNameList.push(measuring_name);
                $("#create_measurement_modal").modal('hide');

                totalLength = 0;
                totalArea = 0;
                totalCount = 0;
                totalSegmentIds = '';

                // save new measurement
                let url = '{{url('sheet/create_measurement')}}';
                let data = {
                    name: measuring_name,
                    color: measuring_color
                };
                let type = 'create_measurement';
                ajaxCall(url, data, type);

                switch (isMeasuring) {
                    case 'point':
                        createPoint();
                        break;
                    case 'line':
                        createLine();
                        break;
                    case 'area':
                        createArea();
                        break;
                    case 'polyline':
                        createPolyline();
                        break;
                    default:
                        toastr.info('Coming soon...');
                        break;
                }
            } else {
                toastr.error('Please type the measuring name');
            }
        }
    }


    /**
     * Create points (counts)
     */

    var pointData = {
        "type": "draw2d.shape.basic.Circle",
        "id": "",
        "x": 0,
        "y": 0,
        "width": 5,
        "height": 5,
        "alpha": 1,
        "selectable": true,
        "draggable": true,
        "start": null,
        "end": null,
        "angle": 0,
        "userData": {},
        "cssClass": "draw2d_shape_basic_Circle",
        "ports": [],
        "bgColor": "rgba(255,0,100,0.5)",
        "color": "rgba(27,27,27,1)",
        "stroke": 1,
        "dasharray": null,
        "resizeable": false,
        "deleteable": false
    };
    var point = null;
    var totalCount = 0; // total count of measurement
    function addPoint(event) {
        if (isMeasuring) {
            let coords = getMousePosition(event);
            let x = coords.x;
            let y = coords.y;
            point = new draw2d.shape.basic.Circle({
                diameter: 10, x: x, y: y, color: measuring_color,
                bgColor: measuring_color, resizeable: false, deleteable: false
            });
            point.userData = {name: 'Point'};
            console.log('point>>>', point);

            canvas.add(point);

            addToolTip(point);
            addSegmentToLeft('addpoint', point);

            let object_id = point.id;
            pointData.id = object_id;
            pointData.x = x;
            pointData.y = y;
            pointData.width = 10;
            pointData.height = 10;
            pointData.color = measuring_color;
            pointData.bgColor = measuring_color;
            pointData.userData.name = 'Point';

            // add segment ids to measurement Id list
            totalSegmentIds += totalSegmentIds.length ? ',' + object_id : object_id;
            let total_segment_ids = 'total_segment_ids_' + tempMeasurementId;
            document.getElementById(total_segment_ids).dataset.id = totalSegmentIds;
            // add total count to measurement amount part
            totalCount++;
            let total_count_span_id = 'total_count_span_' + tempMeasurementId;
            let total_count_toq_id = 'total_count_toq_' + tempMeasurementId;
            document.getElementById(total_count_span_id).innerHTML = totalCount.toString();
            document.getElementById(total_count_toq_id).dataset.count = totalCount;
            document.getElementById(total_count_toq_id).dataset.selected_ids = totalSegmentIds;

            // save added points
            let url = '{{url('sheet/add_point')}}';
            let data = {
                name: 'Point',
                color: measuring_color,
                object_id: object_id,
                info: JSON.stringify(pointData)
            };
            let type = 'addpoint';
            ajaxCall(url, data, type);
        }
    }

    function createPoint() {
        let measuring_status = startMeasuring();
        if (measuring_status) {
            gfx_holder.onmousedown = addPoint;
        }
    }


    /**
     * Create line
     */

    var line = null;
    var isFirstPoint = true;
    var lineData = {
        "type": "draw2d.shape.basic.Line",
        "id": "",
        "alpha": 1,
        "userData": {},
        "cssClass": "draw2d_shape_basic_Line",
        "stroke": 2,
        "color": "#000000",
        "outlineStroke": 0,
        "outlineColor": "none",
        "vertex": []
    };

    function saveLine() {
        let object_id = line.id;

        // update total measurement
        totalSegmentIds += totalSegmentIds.length ? ',' + object_id : object_id;
        totalLength += parseFloat(line.userData.perimeter);
        let total_length_span_id = 'total_length_span_' + tempMeasurementId;
        let total_length_toq_id = 'total_length_toq_' + tempMeasurementId;
        document.getElementById(total_length_span_id).innerHTML = formatValToFeetInch(totalLength);
        document.getElementById(total_length_toq_id).dataset.length = totalLength;
        // add segment ids to measurement Id list
        let total_segment_ids = 'total_segment_ids_' + tempMeasurementId;
        document.getElementById(total_segment_ids).dataset.id = totalSegmentIds;
        document.getElementById(total_length_toq_id).dataset.selected_ids = totalSegmentIds;


        console.log('line>>>', line);

        lineData.id = object_id;
        lineData.color = measuring_color;

        let x1 = line.vertices.data[0].x;
        let y1 = line.vertices.data[0].y;
        let x2 = line.vertices.data[1].x;
        let y2 = line.vertices.data[1].y;

        lineData.vertex.push({x: x1, y: y1});
        lineData.vertex.push({x: x2, y: y2});
        lineData.userData = line.userData;

        let url = '{{url('sheet/add_line')}}';
        let data = {
            name: 'Line',
            color: measuring_color,
            object_id: object_id,
            perimeter: line.userData.perimeter,
            info: JSON.stringify(lineData)
        };
        let type = 'add_line';
        console.log('saveLine>>>', data);
        $('.tooltip').remove();
        ajaxCall(url, data, type);
    }

    function stopDrawingLine(event) {
        if (isMeasuring) {
            if (!isFirstPoint) {
                let coords = getMousePosition(event);
                let x = coords.x;
                let y = coords.y;
                line.setEndPoint(x, y);
                // update measurement amount while measuring
                let length = (line.getLength() * scale).toFixed(2);
                let formatedLength = formatValToFeetInch(length);
                line.userData = {name: 'Line', perimeter: length, perimeterTxt: formatedLength};

                // update left leaf length
                let length_span_id = 'length_span_' + line.id;
                let length_toq_id = 'length_toq_' + line.id;
                document.getElementById(length_span_id).innerHTML = formatedLength;
                document.getElementById(length_toq_id).dataset.length = length;

                // update tooltip measurement
                let length_tooltip_id = 'length_tooltip_' + line.id;
                if (document.getElementById(length_tooltip_id)) {
                    document.getElementById(length_tooltip_id).innerHTML = formatedLength;
                }
            }
        }
    }

    function startDrawingLine(event) {
        if (isMeasuring) {
            if (isFirstPoint) {
                lineData.vertex = [];
                let coords = getMousePosition(event);
                let x = coords.x;
                let y = coords.y;

                line = new draw2d.shape.basic.Line({color: measuring_color, deleteable: false});
                line.setStartPoint(x, y);
                line.setEndPoint(x, y);
                line.setStroke(2);
                let formatedLength = formatValToFeetInch(0);
                line.userData = {name: 'Line', perimeter: 0, perimeterTxt: formatedLength};

                canvas.add(line);

                gfx_holder.onmouseout = null;
                gfx_holder.onmousemove = stopDrawingLine;
                isFirstPoint = false;

                addSegmentToLeft('add_line', line);
                currentMeasuringObj = line;
                showTooltip(line);
            } else {
                // second point clicked
                gfx_holder.onmousemove = null;
                isFirstPoint = true;

                currentMeasuringObj = null;
                addToolTip(line);

                saveLine();
            }
        }
    }

    function createLine() {
        let measuring_status = startMeasuring();
        if (measuring_status) {
            isFirstPoint = true;
            gfx_holder.onmousedown = startDrawingLine;
        }
    }


    /**
     * Measuring area
     **/

    var Rectangle = draw2d.shape.basic.Polygon.extend({
        init: function init(attr, setter, getter) {
            this._super(extend({bgColor: measuring_color, color: measuring_color}, attr), setter, getter);
            let pos = this.getPosition();
            this.resetVertices();
            for (let s = 0; s < vertex.length; s++) {
                this.addVertex(vertex[s]['x'], vertex[s]['y']);
            }
            this.setPosition(pos);
        }
    });
    var polygonData = {
        "type": "draw2d.shape.basic.Polygon",
        "id": "",
        "x": 0,
        "y": 0,
        "minX": 0,
        "minY": 0,
        "maxX": 0,
        "maxY": 0,
        "width": 0,
        "height": 0,
        "alpha": 0.7,
        "angle": 0,
        "userData": {},
        "cssClass": "draw2d_shape_basic_Polygon",
        "bgColor": "#000000",
        "color": "#303030",
        "stroke": 2,
        "radius": 0,
        "dasharray": null,
        "vertices": null
    };
    var timer;
    var polygon = null;
    var countOfPolygonVertex = 0;
    var polyLine = null;
    var vertex = [];
    var isAddedAreaToLeft = false;

    function perimeterAreaCalc(vertex) {
        let x = [];
        let y = [];
        let vertices = vertex.length;
        let digits = 2;
        let area;
        let perimeter;

        // Read and store the vertex x and y values
        for (var k = 0; k < vertices; k++) {
            x[k] = parseFloat(vertex[k]['x']);
            y[k] = parseFloat(vertex[k]['y']);
        }

        // Copy the values x[0] and y[0]
        // to the values x[vertices] and y[vertices]
        // to simplfy the area and perimeter calculations
        x[vertices] = x[0];
        y[vertices] = y[0];
        // console.log('updated x, y...', x, y);

        // Calculate the area of a polygon
        // using the data stored
        // in the arrays x and y
        area = 0.0;
        for (k = 0; k < vertices; k++) {
            var xDiff = x[k + 1] - x[k];
            var yDiff = y[k + 1] - y[k];
            area = area + x[k] * yDiff - y[k] * xDiff;
        }
        area = 0.5 * area * scale * scale / 12;
        area = Math.abs(area).toFixed(digits);

        // Calculate the perimeter
        // of a polygon using the data stored
        // in the arrays x and y
        perimeter = 0.0;
        for (k = 0; k < vertices; k++) {
            xDiff = x[k + 1] - x[k];
            yDiff = y[k + 1] - y[k];
            perimeter = perimeter + Math.pow(xDiff * xDiff + yDiff * yDiff, 0.5);
        }
        perimeter = (perimeter * scale).toFixed(digits);

        return {area: area, perimeter: perimeter};
    }

    function saveArea(obj) {
        console.log('save area...', obj);

        polygonData.id = obj.id;
        polygonData.x = obj.x;
        polygonData.y = obj.y;
        polygonData.color = measuring_color;
        polygonData.bgColor = measuring_color;
        polygonData.minX = obj.minX;
        polygonData.minY = obj.minY;
        polygonData.maxX = obj.maxX;
        polygonData.maxY = obj.maxY;
        polygonData.width = obj.width;
        polygonData.height = obj.height;
        polygonData.vertices = vertex;
        polygonData.userData = obj.userData;

        vertex = [];
        polygon = null;

        // update total measurement
        let object_id = obj.id;
        totalSegmentIds += totalSegmentIds.length ? ',' + object_id : object_id;
        totalLength += parseFloat(obj.userData.perimeter);
        totalArea += parseFloat(obj.userData.area);
        // perimeter
        let total_length_span_id = 'total_length_span_' + tempMeasurementId;
        let total_length_toq_id = 'total_length_toq_' + tempMeasurementId;
        document.getElementById(total_length_span_id).innerHTML = formatValToFeetInch(totalLength);
        document.getElementById(total_length_toq_id).dataset.length = totalLength;
        // area
        let total_area_span_id = 'total_area_span_' + tempMeasurementId;
        let total_area_toq_id = 'total_area_toq_' + tempMeasurementId;
        document.getElementById(total_area_span_id).innerHTML = formatValToFeetInch(totalArea);
        document.getElementById(total_area_toq_id).dataset.length = totalArea;

        // add segment ids to measurement Id list
        let total_segment_ids = 'total_segment_ids_' + tempMeasurementId;
        document.getElementById(total_segment_ids).dataset.id = totalSegmentIds;
        document.getElementById(total_length_toq_id).dataset.selected_ids = totalSegmentIds;
        document.getElementById(total_area_toq_id).dataset.selected_ids = totalSegmentIds;

        let url = '{{url('sheet/add_area')}}';
        let data = {
            name: 'Area',
            color: measuring_color,
            object_id: obj.id,
            perimeter: obj.userData.perimeter,
            area: obj.userData.area,
            info: JSON.stringify(polygonData)
        };
        let type = 'add_area';
        ajaxCall(url, data, type);
    }

    function stopDrawingArea() {
        clearTimeout(timer);
        if (countOfPolygonVertex < 2) {
            console.log('Invalid polygon');
            toastr.error('Invalid polygon');
        } else {
            console.log('stop drawing area');
            document.getElementById(polyLine.id).remove();
            canvas.remove(polyLine);
            polyLine = null;
            currentMeasuringObj = null;
            $('.tooltip').remove();

            let calcResult = perimeterAreaCalc(vertex);
            let area = calcResult.area;
            let perimeter = calcResult.perimeter;

            let formatedPerimeter = formatValToFeetInch(perimeter);
            let formatedArea = formatValToFeetInch(area);

            let userData = {
                name: 'Area', perimeter: perimeter, perimeterTxt: formatedPerimeter,
                area: area, areaTxt: formatedArea
            };

            polygon = new Rectangle();
            polygon.attr({bgColor: measuring_color, color: measuring_color, alpha: 0.7, userData: userData});

            addSegmentToLeft('add_area', polygon);
            addToolTip(polygon);

            let length_span_id = 'length_span_' + polygon.id;
            let length_toq_id = 'length_toq_' + polygon.id;
            let area_span_id = 'area_span_' + polygon.id;
            let area_toq_id = 'area_toq_' + polygon.id;
            document.getElementById(length_span_id).innerHTML = formatedPerimeter;
            document.getElementById(length_toq_id).dataset.length = perimeter;
            document.getElementById(area_span_id).innerHTML = formatedArea;
            document.getElementById(area_toq_id).dataset.area = area;

            let minarrx = [];
            let minarry = [];
            for (let s = 0; s < vertex.length; s++) {
                minarrx[s] = vertex[s]['x'];
                minarry[s] = vertex[s]['y'];
            }
            let minx = Math.min.apply(Math, minarrx);
            let miny = Math.min.apply(Math, minarry);

            canvas.add(polygon, minx, miny);

            gfx_holder.onmousemove = null;
            countOfPolygonVertex = 0;
            isFirstPoint = true;
            isAddedAreaToLeft = false;
            saveArea(polygon);
        }
    }

    function continueDrawingArea() {
        let coords = getMousePosition(event);
        let x = coords.x;
        let y = coords.y;
        vertex[countOfPolygonVertex] = {x: x, y: y};
        polyLine.setVertices(vertex);
        canvas.add(polyLine);

        // update measurement amount while measuring
        let calcResult = perimeterAreaCalc(vertex);
        let area = calcResult.area;
        let perimeter = calcResult.perimeter;

        let formatedPerimeter = formatValToFeetInch(perimeter);
        let formatedArea = formatValToFeetInch(area);

        let length_span_id = 'length_span_' + polyLine.id;
        let length_toq_id = 'length_toq_' + polyLine.id;
        let area_span_id = 'area_span_' + polyLine.id;
        let area_toq_id = 'area_toq_' + polyLine.id;
        document.getElementById(length_span_id).innerHTML = formatedPerimeter;
        document.getElementById(length_toq_id).dataset.length = perimeter;
        document.getElementById(area_span_id).innerHTML = formatedArea;
        document.getElementById(area_toq_id).dataset.area = area;

        let length_tooltip_id = 'length_tooltip_' + polyLine.id;
        let area_tooltip_id = 'area_tooltip_' + polyLine.id;

        if (document.getElementById(length_tooltip_id)) {
            document.getElementById(length_tooltip_id).innerHTML = formatedPerimeter;
            document.getElementById(area_tooltip_id).innerHTML = formatedArea;
        }
    }

    function stopDrawingAreaLine(event) {
        if (isMeasuring) {
            if (!isFirstPoint) {
                let coords = getMousePosition(event);
                let x = coords.x;
                let y = coords.y;
                line.setEndPoint(x, y);
                // update measurement amount while measuring
                let length = (line.getLength() * scale).toFixed(2);
                let formatedLength = formatValToFeetInch(length);
                let formatedArea = formatValToFeetInch(0);
                line.userData = {
                    name: 'Area',
                    perimeter: length,
                    perimeterTxt: formatedLength,
                    area: 0,
                    areaTxt: formatedArea
                };

                let length_span_id = 'length_span_' + line.id;
                let length_toq_id = 'length_toq_' + line.id;
                document.getElementById(length_span_id).innerHTML = formatedLength;
                document.getElementById(length_toq_id).dataset.length = length;

                let length_tooltip_id = 'length_tooltip_' + polyLine.id;
                if (document.getElementById(length_tooltip_id)) {
                    document.getElementById(length_tooltip_id).innerHTML = formatedLength;
                }
            }
        }
    }

    function startDrawingArea(event) {
        if (timer) clearTimeout(timer);

        timer = setTimeout(function () {
            let coords = getMousePosition(event);
            let x = coords.x;
            let y = coords.y;
            if (isFirstPoint) {
                console.log('start drawing area...');
                isFirstPoint = false;
                vertex = [];
                vertex[countOfPolygonVertex] = {x: x, y: y};

                polyLine = new draw2d.shape.basic.PolyLine({
                    bgColor: measuring_color,
                    color: measuring_color,
                    alpha: 0.7
                });
                polyLine.userData = {
                    name: 'Area',
                    perimeter: 0,
                    perimeterTxt: 0,
                    area: 0,
                    areaTxt: 0
                };
                line = new draw2d.shape.basic.Line({color: measuring_color, deleteable: false});

                line.setStartPoint(x, y);
                line.setEndPoint(x, y);
                line.setStroke(2);
                let formatedLength = formatValToFeetInch(0);
                line.userData = {name: 'Area', perimeter: 0, perimeterTxt: formatedLength, area: 0, areaTxt: 0};

                canvas.add(line);

                addSegmentToLeft('add_line', line);
                currentMeasuringObj = polyLine;
                showTooltip(currentMeasuringObj);

                gfx_holder.onmouseout = null;
                gfx_holder.onmousemove = stopDrawingAreaLine;
            } else {
                vertex[countOfPolygonVertex] = {x: x, y: y};
                polyLine.setStroke(2);
                polyLine.setVertices(vertex);


                let calcResult = perimeterAreaCalc(vertex);
                let area = calcResult.area;
                let perimeter = calcResult.perimeter;
                let formatedLength = formatValToFeetInch(perimeter);
                let formatedArea = formatValToFeetInch(area);
                polyLine.userData = {
                    name: 'Area',
                    perimeter: perimeter,
                    perimeterTxt: formatedLength,
                    area: area,
                    areaTxt: formatedArea
                };

                canvas.add(polyLine);
                if (line) {
                    document.getElementById(line.id).remove();
                    canvas.remove(line);
                    line = null;
                    gfx_holder.onmousemove = null;
                }

                if (!isAddedAreaToLeft) {
                    isAddedAreaToLeft = true;
                    addSegmentToLeft('add_area', polyLine);
                }

                gfx_holder.onmousemove = continueDrawingArea;
            }

            countOfPolygonVertex++;
        }, 400);
    }

    function createArea() {
        let measuring_status = startMeasuring();
        if (measuring_status) {
            gfx_holder.onclick = startDrawingArea;
            gfx_holder.ondblclick = stopDrawingArea;
        }
    }


    /**
     * Create poly line
     */
    let countOfPolylineVertex = 0;
    let polylineData = {
        "type": "draw2d.shape.basic.PolyLine",
        "id": "",
        "alpha": 0.7,
        "angle": 0,
        "userData": {},
        "cssClass": "draw2d_shape_basic_PolyLine",
        "stroke": 2,
        "color": "#303030",
        "vertex": [],
        "radius": ""
    };

    function savePolyline(obj) {
        console.log('save polyline...', obj);

        polylineData.id = obj.id;
        polylineData.color = measuring_color;
        polylineData.vertex = vertex;
        polylineData.userData = obj.userData;

        vertex = [];

        // update total measurement
        let object_id = obj.id;
        totalSegmentIds += totalSegmentIds.length ? ',' + object_id : object_id;
        totalLength += parseFloat(obj.userData.perimeter);
        // perimeter
        let total_length_span_id = 'total_length_span_' + tempMeasurementId;
        let total_length_toq_id = 'total_length_toq_' + tempMeasurementId;
        document.getElementById(total_length_span_id).innerHTML = formatValToFeetInch(totalLength);
        document.getElementById(total_length_toq_id).dataset.length = totalLength;

        // add segment ids to measurement Id list
        let total_segment_ids = 'total_segment_ids_' + tempMeasurementId;
        document.getElementById(total_segment_ids).dataset.id = totalSegmentIds;
        document.getElementById(total_length_toq_id).dataset.selected_ids = totalSegmentIds;

        let url = '{{url('sheet/add_polyline')}}';
        let data = {
            name: 'Polyline',
            color: measuring_color,
            object_id: object_id,
            perimeter: obj.userData.perimeter,
            info: JSON.stringify(polylineData)
        };
        let type = 'add_polyline';
        ajaxCall(url, data, type);
    }

    function continueDrawingPolyline() {
        let coords = getMousePosition(event);
        let x = coords.x;
        let y = coords.y;
        vertex[countOfPolylineVertex] = {x: x, y: y};
        polyLine.setVertices(vertex);
        canvas.add(polyLine);

        // update measurement amount while measuring
        let perimeter = (polyLine.getLength() * scale).toFixed(2);
        let formatedPerimeter = formatValToFeetInch(perimeter);

        let length_span_id = 'length_span_' + polyLine.id;
        let length_toq_id = 'length_toq_' + polyLine.id;
        document.getElementById(length_span_id).innerHTML = formatedPerimeter;
        document.getElementById(length_toq_id).dataset.length = perimeter;

        // update tooltip amount
        let length_tooltip_id = 'length_tooltip_' + polyLine.id;
        if (document.getElementById(length_tooltip_id)) {
            document.getElementById(length_tooltip_id).innerHTML = formatedPerimeter;
        }
    }

    function stopDrawingPolyline(event) {
        clearTimeout(timer);
        if (countOfPolylineVertex < 1) {
            console.log('Invalid polyline');
            toastr.error('Invalid polyline');
        } else {
            console.log('stop drawing polyline');
            // save last point
            let coords = getMousePosition(event);
            let x = coords.x;
            let y = coords.y;
            vertex[countOfPolylineVertex] = {x: x, y: y};
            let perimeter = (polyLine.getLength() * scale).toFixed(2);
            let formatedLength = formatValToFeetInch(perimeter);
            polyLine.userData = {name: 'Polyline', perimeter: perimeter, perimeterTxt: formatedLength};
            canvas.add(polyLine);

            currentMeasuringObj = null;
            $('.tooltip').remove();
            addToolTip(polyLine);

            // save poly line
            gfx_holder.onmousemove = null;
            countOfPolylineVertex = 0;
            isFirstPoint = true;
            savePolyline(polyLine);
        }
    }

    function startDrawingPolyline(event) {
        if (timer) clearTimeout(timer);
        timer = setTimeout(function () {
            let coords = getMousePosition(event);
            let x = coords.x;
            let y = coords.y;
            if (isFirstPoint) {
                console.log('start drawing poly line...');
                isFirstPoint = false;
                vertex = [];
                vertex[countOfPolylineVertex] = {x: x, y: y};

                polyLine = new draw2d.shape.basic.PolyLine({
                    bgColor: measuring_color,
                    color: measuring_color,
                    alpha: 0.7
                });
                polyLine.setStroke(2);
                polyLine.setVertices(vertex);

                let formatedLength = formatValToFeetInch(0);
                polyLine.userData = {name: 'Polyline', perimeter: 0, perimeterTxt: formatedLength};

                canvas.add(polyLine);

                addSegmentToLeft('add_polyline', polyLine);
                currentMeasuringObj = polyLine;
                showTooltip(polyLine);

                gfx_holder.onmouseout = null;
                gfx_holder.onmousemove = continueDrawingPolyline;
            } else {
                vertex[countOfPolylineVertex] = {x: x, y: y};

                let perimeter = (polyLine.getLength() * scale).toFixed(2);
                let formatedLength = formatValToFeetInch(perimeter);
                polyLine.userData = {name: 'Polyline', perimeter: perimeter, perimeterTxt: formatedLength};

                canvas.add(polyLine);
            }

            countOfPolylineVertex++;
        }, 200);
    }

    function createPolyline() {
        let measuring_status = startMeasuring();
        if (measuring_status) {
            gfx_holder.onclick = startDrawingPolyline;
            gfx_holder.ondblclick = stopDrawingPolyline;
        }
    }


    /**
     * Main entry point
     * @param type
     */

    var isMeasuring = '';

    function checkRun() {
        if (isSetScale) {
            if (!measuring_name) {
                openCreateMeasurementModal();
            }
        } else {
            openSetScaleModal();
        }
    }

    function createSegment(type) {
        if (!isMeasuring) {
            if (type === 'point'
                || type === 'line'
                || type === 'area'
                || type === 'polyline') {
                isMeasuring = type;
                checkRun();
            } else {
                toastr.info('Comming soon...');
            }
        }
    }


    /**
     * Tooltip
     */

    var tooltip = null;

    function mouseMoveOnCanvas(x, y) {
//        console.log(x, y);
        if (isMeasuring && currentMeasuringObj) {
            if (tooltip) {
                if (y > 530 || x > 885) {
                    tooltip.css({'top': y - 80, 'left': x - 150, 'opacity': 1});
                } else {
                    tooltip.css({'top': y + 20, 'left': x - 40, 'opacity': 1});
                }
            }
        } else {
            if (tooltip) {
                if (y > 530 || x > 885) {
                    tooltip.css({'top': y - 80, 'left': x - 150, 'opacity': 1});
                } else {
                    tooltip.css({'top': y + 20, 'left': x - 40, 'opacity': 1});
                }
            }
        }
    }

    function hideTooltip(fast) {
        if (tooltip !== null) {
            if (fast) {
                tooltip.remove()
            } else {
                tooltip.fadeOut(500, function () {
                    $(this).remove()
                })
            }
            tooltip = null;
        }
    }

    function showTooltip(obj) {
        let infoText = '';
        if (obj.cssClass !== 'draw2d_shape_basic_Image' && obj.cssClass !== 'draw2d_shape_composite_Group') {
            if (obj.userData.name) {
                infoText = obj.userData.name;

                if (obj.userData.name === 'Line') {
                    let length_tooltip_id = 'length_tooltip_' + obj.id;
                    infoText += '<br>Length: ' + '<span id="' + length_tooltip_id + '">' + obj.userData.perimeterTxt + '</span>';
                }

                if (obj.userData.name === 'Polyline') {
                    let length_tooltip_id = 'length_tooltip_' + obj.id;
                    infoText += '<br>Length: ' + '<span id="' + length_tooltip_id + '">' + obj.userData.perimeterTxt + '</span>';
                }

                if (obj.userData.name === 'Area') {
                    let length_tooltip_id = 'length_tooltip_' + obj.id;
                    infoText += '<br>Perimeter: ' + '<span id="' + length_tooltip_id + '">' + obj.userData.perimeterTxt + '</span>';
                    let area_tooltip_id = 'area_tooltip_' + obj.id;
                    infoText += '<br>Area: ' + '<span id="' + area_tooltip_id + '">' + obj.userData.areaTxt + '</span>';
                }
            }

            let tooltipDiv = infoText ? '<div class="tooltip">' + infoText + '</div>' : '<div class="tooltip">segment</div>';
            tooltip = $(tooltipDiv).appendTo('#gfx_holder');
        }
    }

    function initShowHideTooltip() {
        let figures = canvas.getFigures();
        for (let i = 0; i < figures.data.length; i++) {
            let figure = figures.data[i];
            figure.on("mouseenter", function () {
                showTooltip(figure);
            });
            figure.on("mouseleave", function () {
                hideTooltip();
            });
            figure.on("move", function () {
//                console.log('move figure tooltip');
//                positionTooltip();
            });
            figure.on("dragstart", function () {
                hideTooltip(true);
            });
        }
        let objects = canvas.getLines();
        for (let i = 0; i < objects.data.length; i++) {
            let object = objects.data[i];
            object.on("mouseenter", function () {
                showTooltip(object);
            });
            object.on("mouseleave", function () {
                hideTooltip();
            });
            object.on("move", function () {
//                console.log('move line tooltip');
//                positionTooltip();
            });
            object.on("dragstart", function () {
                hideTooltip(true);
            });
        }
    }


    /**
     * Remove object by delete key
     **/

    function deleteObject(command) {
        let objId = [], id, objects, leftSegmentId, parent, countOfLeaf, measurementId;
        if (command.commands) { // delete several objects
            objects = command.commands.data;
            objects.forEach(function (item) {
                if (item.figure.cssClass !== 'draw2d_shape_basic_Image') {
                    id = item.figure.id;
                    leftSegmentId = document.getElementById(id);
                    parent = leftSegmentId.parentElement;
                    leftSegmentId.remove();
                    countOfLeaf = parent.childElementCount;
                    if (countOfLeaf === 0) {
                        // remove measurement name from saved measurementNameList
                        let measurementLeafName = parent.parentElement.querySelector('li a').textContent.trim();
                        let tempIndex = measurementNameList.indexOf(measurementLeafName);
                        if (tempIndex > -1) {
                            measurementNameList.splice(tempIndex, 1);
                        }

                        measurementId = parent.id.split('_')[1];
                        parent.parentElement.remove();
                        objId.push({segment: 0, id: measurementId});
                    }
                    objId.push({segment: 1, id: id});
                }
            });
        } else { // delete one object
            if (command.figure.cssClass !== 'draw2d_shape_basic_Image') {
                id = command.figure.id;
                leftSegmentId = document.getElementById(id);
                parent = leftSegmentId.parentElement;
                leftSegmentId.remove();
                countOfLeaf = parent.childElementCount;
                if (countOfLeaf === 0) {
                    // remove measurement name from saved measurementNameList
                    let measurementLeafName = parent.parentElement.querySelector('li a').textContent.trim();
                    let tempIndex = measurementNameList.indexOf(measurementLeafName);
                    if (tempIndex > -1) {
                        measurementNameList.splice(tempIndex, 1);
                    }

                    measurementId = parent.id.split('_')[1];
                    parent.parentElement.remove();
                    objId.push({segment: 0, id: measurementId});
                }
                objId.push({segment: 1, id: id});
            }
        }

        if (objId.length) {
            let url = '{{url('sheet/remove_object')}}';
            let data = {
                ids: objId
            };
            let type = 'remove_object';
//            console.log('remove selected objects', url, data, type);
            ajaxCall(url, data, type);
        }
    }


    /**
     * Update feet,inch while moving vertex of line, area, polyline
     **/

    var toBeUpdatedObj = null;

    function moveVertex() {
        let obj = toBeUpdatedObj;
        if (obj) {
            console.log('move vertex...');
            if (obj.cssClass === "draw2d_shape_basic_Line") {
                // when line is updated
                let perimeter = (obj.getLength() * scale).toFixed(2);
                let formatedLengh = formatValToFeetInch(perimeter);
                let length_span_id = 'length_span_' + obj.id;
                let length_toq_id = 'length_toq_' + obj.id;
                document.getElementById(length_span_id).innerHTML = formatedLengh;
                document.getElementById(length_toq_id).dataset.length = perimeter;
            } else if (obj.cssClass === "draw2d_shape_basic_Polygon") {
                // when area is updated
                let updatedVertex = [];
                obj.vertices.data.forEach(function (item) {
                    updatedVertex.push({x: item.x, y: item.y});
                });
                let calcResult = perimeterAreaCalc(updatedVertex);
                let area = calcResult.area;
                let perimeter = calcResult.perimeter;

                let formatedPerimeter = formatValToFeetInch(perimeter);
                let formatedArea = formatValToFeetInch(area);

                let length_span_id = 'length_span_' + obj.id;
                let length_toq_id = 'length_toq_' + obj.id;
                let area_span_id = 'area_span_' + obj.id;
                let area_toq_id = 'area_toq_' + obj.id;
                document.getElementById(length_span_id).innerHTML = formatedPerimeter;
                document.getElementById(length_toq_id).dataset.length = perimeter;
                document.getElementById(area_span_id).innerHTML = formatedArea;
                document.getElementById(area_toq_id).dataset.area = area;
            } else if (obj.cssClass === "draw2d_shape_basic_PolyLine") {
                // when polyline is updated
                let updatedVertex = [];
                obj.vertices.data.forEach(function (item) {
                    updatedVertex.push({x: item.x, y: item.y});
                });
                let calcResult = perimeterAreaCalc(updatedVertex);
                let perimeter = calcResult.perimeter;

                let formatedPerimeter = formatValToFeetInch(perimeter);

                let length_span_id = 'length_span_' + obj.id;
                let length_toq_id = 'length_toq_' + obj.id;
                document.getElementById(length_span_id).innerHTML = formatedPerimeter;
                document.getElementById(length_toq_id).dataset.length = perimeter;
            }
        }
    }


    /**
     * Initialize measuring
     */

    function composeGroup() {
        console.log('composing group...');
        baseGroup = new draw2d.shape.composite.Group();
        canvas.getFigures().data.forEach(function (figure) {
            baseGroup.assignFigure(figure);
        });
        canvas.getLines().data.forEach(function (figure) {
            baseGroup.assignFigure(figure);
        });

        canvas.add(baseGroup);
        console.log('grouped!', baseGroup);
    }

    function unGroup() {
        console.log('ungrouping baseGroup...', baseGroup);
        if (baseGroup) {
            canvas.getFigures().data.forEach(function (figure) {
                baseGroup.unassignFigure(figure);
            });
            canvas.getLines().data.forEach(function (figure) {
                baseGroup.unassignFigure(figure);
            });
            canvas.remove(baseGroup);

            baseGroup = null;
            console.log('ungrouped!');
        }
    }

    var baseGroup = null;

    var selectedSegmentIds = new draw2d.util.ArrayList();

    var myPolicy = draw2d.policy.EditPolicy.extend({
        NAME: "TKLPolicy",

        /**
         * @constructor
         * Creates a new Router object
         */
        init: function () {
            this._super();
        },

        onInstall: function (canvas) {
        },

        onUninstall: function (canvas) {
        },

        onDragStart: function (canvas, figure, x, y, shiftKey, ctrlKey) {
            console.log('>>>onDragStart');
            figure.shape.attr({cursor: "move"});

            // this happens if you drag&drop the shape outside of the screen and
            // release the mouse button outside the window. We restore the alpha
            // with the next drag&drop operation
            if (figure.isMoving === true) {
                figure.setAlpha(figure.originalAlpha);
            }

            figure.originalAlpha = figure.getAlpha();
            figure.isMoving = false;

            // return value since 6.1.0
            return true;
        },

        // onDrag: function (draggedDomNode, x, y ) {
        //     console.log('>>>onDrag', draggedDomNode);
        // },

        onDrag: function (canvas, figure) {
            console.log('>>>onDrag');
            // enable the alpha blending of the first real move of the object
            //
            if (figure.isMoving === false) {
                figure.isMoving = true;
                figure.setAlpha(figure.originalAlpha * 0.4);
            }
        },


        onDragEnd: function (canvas, figure, x, y, shiftKey, ctrlKey) {
            console.log('>>>onDragEnd');
            figure.shape.attr({cursor: "default"});
            figure.isMoving = false;
            figure.setAlpha(figure.originalAlpha);
        },


        onMouseMove: function (canvas, x, y, shiftKey, ctrlKey) {
            // console.log('>>>onMouseMove');
            x /= canvas.getZoom();
            y /= canvas.getZoom();
            mouseMoveOnCanvas(x, y);
        },


        onDoubleClick: function (figure, mouseX, mouseY, shiftKey, ctrlKey) {
        },


        snap: function (canvas, figure, clientPos) {
            console.log('>>>snap');
            return clientPos;
        },


        onMouseDown: function (canvas, x, y, shiftKey, ctrlKey) {
            console.log('>>>onMouseDown',x,y);
            if (!isMeasuring) {
                let selectedObj = canvas.getPrimarySelection();
                console.log('selectedObj>>>', selectedObj ? selectedObj.cssClass : selectedObj);
                if (selectedObj) {
                    if (selectedObj.cssClass === 'draw2d_shape_composite_Group') {
                        console.log('ready for moving sheet...');
                    } else if (selectedObj.cssClass === 'draw2d_shape_basic_Image') {
                        composeGroup();
                        console.log('should stop event propagation...');
                    } else {
                        unGroup();
                        toBeUpdatedObj = selectedObj;
                    }
                } else {
                    if (baseGroup) {
                        unGroup();
                    }
                    // composeGroup();
                }
            }
        },


        onMouseDrag: function (canvas, dx, dy, dx2, dy2) {
            console.log('>>>onMouseDrag');
            if (!isMeasuring && isMeasuring !== 'line') {
                moveVertex();
            }
        },


        onMouseUp: function (figure, x, y, shiftKey, ctrlKey) {
            console.log('>>>onMouseUp');
            if (!isMeasuring) {
                let selectedSegments = canvas.getSelection();
                if (selectedSegments.all.data.length) {
                    selectedSegments.all.data.forEach(function (segment) {
                        if (segment.cssClass !== 'draw2d_shape_basic_Image' && segment.cssClass !== 'draw2d_shape_composite_Group') {
                            document.getElementById(segment.id).classList.add('segment-selected');
                            selectedSegmentIds.add(segment);
                            if (!selectedMultiSegements.includes(segment.id)) {
                                selectedMultiSegements.push(segment.id);
                            }
                        }
                    });
                } else {
                    selectedSegmentIds = new draw2d.util.ArrayList();
                    selectedMultiSegements = [];
                }
            }
        },


        onClick: function (figure, mouseX, mouseY, shiftKey, ctrlKey) {
            console.log('>>>onClick', figure);
            // select multiple segments
            if (!isMeasuring) {
                if (ctrlKey) {
                    if (figure && figure.cssClass !== 'draw2d_shape_basic_Image') {
                        document.getElementById(figure.id).classList.add('segment-selected');
                        selectedSegmentIds.add(figure);
                        if (!selectedMultiSegements.includes(figure.id)) {
                            selectedMultiSegements.push(figure.id);
                        }

                        canvas.setCurrentSelection(selectedSegmentIds);
                    }
                } else {
                    selectedSegmentIds = new draw2d.util.ArrayList();
                    selectedMultiSegements = [];
                    selectedOrphanedId = null;
                    let selectedSegmentsLeaf = document.querySelectorAll('.segment-selected');
                    selectedSegmentsLeaf.forEach(function (item) {
                        item.classList.remove('segment-selected');
                    });
                    if (figure) {
                        if (figure.cssClass === 'draw2d_shape_basic_Image') {
                            composeGroup();
                        } else {
                            unGroup();
                            if (figure.cssClass !== "draw2d_shape_composite_Group") {
                                selectedMultiSegements.push(figure.id);
                                document.getElementById(figure.id).classList.add('segment-selected');
                            }
                        }
                    }
                }
            }
        },


        // created custom
        onMouseWheel: function (wheelDelta, x, y, shiftKey, ctrlKey) {
            wheelDelta = wheelDelta / 1024;
            console.log(wheelDelta,x,y,canvas.getZoom(),canvas.getWidth(),canvas.getHeight());
            console.log(canvas.getScrollLeft(),canvas.getScrollArea( ));
            let zoom = (Math.min(5, Math.max(0.1, canvas.getZoom() + wheelDelta)) * 10000 | 0) / 10000;
            // setZoom
            updateZoom(zoom);
        },


        onRightMouseDown: function (figure, x, y, shiftKey, ctrlKey) {
        },
    });


    function initializeCanvas() {
        let zoom = 1.0;
        // zoom = parseFloat('{{$sheet->zoom}}');

        canvas = new draw2d.Canvas("gfx_holder");

        canvas.setScrollArea("#gfx_holder");
        canvas.installEditPolicy(new draw2d.policy.canvas.WheelZoomPolicy());                // Responsible for zooming with mouse wheel
        canvas.installEditPolicy(new draw2d.policy.canvas.DefaultKeyboardPolicy());          // Handles the keyboard interaction
        canvas.installEditPolicy(new draw2d.policy.canvas.BoundingboxSelectionPolicy());     // Responsible for selection handling
        canvas.installEditPolicy(new draw2d.policy.canvas.DropInterceptorPolicy());

        // canvas.installEditPolicy(new draw2d.policy.canvas.ShowGridEditPolicy());
        canvas.installEditPolicy(new draw2d.policy.canvas.SnapToGeometryEditPolicy());
        canvas.installEditPolicy(new draw2d.policy.canvas.SnapToInBetweenEditPolicy());
        canvas.installEditPolicy(new draw2d.policy.canvas.SnapToCenterEditPolicy());
        canvas.installEditPolicy(new myPolicy);

        gfx_holder = document.getElementById("gfx_holder");

        // add png to svg
        console.log(sheet_x,sheet_y);
        let image = new draw2d.shape.basic.Image({
            path: img_url,
            // x: sheet_x * zoom,
            // y: sheet_y * zoom,
            x: sheet_x,
            y: sheet_y,
            width: sheet_width/1.5,
            height: sheet_height/1.5,
            // width: 540,
            // height: 750,
            keepAspectRatio: false,
            resizeable: false,
            selectable: true,
            // onClick: onClickImage,
        });
        canvas.add(image);

        // display stored objects
        let reader = new draw2d.io.json.Reader();
        let sheetObjects = {!! json_encode($sheet_objects) !!};
        // console.log('sheet objects>>>', sheetObjects);
        reader.unmarshal(canvas, sheetObjects);

        // create base group with existing figures
        baseGroup = new draw2d.shape.composite.Group();
        canvas.getFigures().data.forEach(function (figure) {
            baseGroup.assignFigure(figure);
        });
        canvas.getLines().data.forEach(function (figure) {
            baseGroup.assignFigure(figure);
        });
        canvas.add(baseGroup);
        console.log('baseGroup>>>', baseGroup);

        // set zoom
        $(".zoomResetBtn").text((parseInt((1.0 / zoom) * 100)) + "%");
        canvas.setZoom(zoom, true);
    }


    /**
     * Exit measuring by clicking out of gfx_holder
     */


    $(document).mouseup(function (e) {
        let modal_btn = $(".modal-footer .btn");
        let stop_measuring_btn = $('.stop-measuring');
        let tooltip = $(".tooltip");
        let gfx_holder = $("#gfx_holder");
        let modal_backdrop = $(".modal-backdrop");
        let zoomOutBtn = $(".zoomOutBtn");
        let zoomInBtn = $(".zoomInBtn");
        let zoomResetBtn = $(".zoomResetBtn");
        let rect = "rect";
//        console.log(e.target, modal_backdrop.is(e.target));
        console.log('isMeasuring', isMeasuring)
        if (isMeasuring && measuring_name && isSetScale && isMeasuring === '') {
            if (!modal_btn.is(e.target) && !tooltip.is(e.target) && !stop_measuring_btn.is(e.target)) {
                if ($(e.target).parents('#gfx_holder').length === 0 && $(e.target).parents('.modal').length === 0) {
                    if (!gfx_holder.is(e.target) && !modal_backdrop.is(e.target) && !zoomOutBtn.is(e.target) && !zoomInBtn.is(e.target) && !zoomResetBtn.is(e.target)) {
//                        console.log(e.target, e.target.tagName === rect);
                        if (e.target.tagName !== rect) {
                            console.log('exit from measuring', e.target);
                            exitMeasuring();
                        }
                    }
                }
            }
        }
    });

    // draggable modal
    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // resizable modal
    $('#create_measurement_modal').find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

    $('#set_scale_modal').find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

    $('#confirm_scale_modal').find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });


    /**
     * Canvas
     */

    document.addEventListener("DOMContentLoaded", function () {
        initializeCanvas();
        showScale();
        initShowHideTooltip();

        // set select to segment object if segment id exist in URL
        if (segmentObjIdFromURL) {
            let obj = canvas.getFigure(segmentObjIdFromURL);
            if (!obj) {
                obj = canvas.getLine(segmentObjIdFromURL);
            }
            canvas.setCurrentSelection(obj);
            document.getElementById(segmentObjIdFromURL).classList.add('segment-selected');
        }

        // delete/move/update object -- get command stack on canvas
        canvas.getCommandStack().addEventListener(function (e) {
            let cmdType = e.getCommand().label;
            let command = e.getCommand();
//            console.log('cmdType...', cmdType);
//            console.log('command...', command);
//            console.log('isPostChangeEvent...', e.isPostChangeEvent());
            if (!isMeasuring) {
                if (e.isPostChangeEvent()) {
                    switch (cmdType) {
                        case 'Delete Shape': // delete by key
                            deleteObject(command);
                            break;
                        case 'Move Shape': // move point or area
                            moveObject(command);
                            break;
                        case 'Move Vertex': // update line
                            updateLine(command);
                            break;
                        case "Move Vertices": // move line
                            moveLine(command);
                            break;
                        case "Execute Commands": // Execute Command
                            updateArea(command);
                            break;
                        default: // Add Vertex
                            console.log('no update action...', cmdType, command);
                            break;
                    }
                }
            }

        });

    });

</script>