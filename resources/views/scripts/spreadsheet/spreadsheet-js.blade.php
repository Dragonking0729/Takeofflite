<script>
    var _token = $("input[name=_token]").val();
    var projectId = '<?php echo $page_info['project_id']; ?>';
    var projectName = "<?php echo $page_info['project_name']; ?>";
    var globalTOQ = {};
    var interviewTOQ = {};
    var someBlock = $('.someBlock');

    function cancelInterview() {
        console.log('remove localstorage globalTOQ');
        $('#assembly_formula_modal').modal('hide');
        $('#formula_modal').modal('hide');
        document.getElementById('formula_body').innerHTML = '';
        document.getElementById('assembly_formula_body').innerHTML = '';
        localStorage.removeItem('globalTOQ');
    }

    function cancelPrice() {
        $('.get_price').fadeOut();
    }

    function addItemToSS(itemId, projectId) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('estimate/add_item_to_ss') }}",
            method: "POST",
            data: {
                _token: _token,
                item_id: itemId,
                project_id: projectId,
                sort_status: sortStatus
            },
            success: function(res) {
                someBlock.preloader('remove');
                SSData = res.data.ss_data;
                priceComments = res.data.price_comments;
                $("#ss_total_bar").html(res.data.total);
                updateSSAddons(projectId);

                spreadsheet.options.data = SSData;
                spreadsheet.refresh();

                hideMarkupCols();
                updateHiddenCols();
                setComment(priceComments);
                updateOverLine();
                updateSortStyle();
            }
        });
    }

    function setComment(comments) {
        Object.keys(comments).forEach(key => {
            spreadsheet.setComments(key, comments[key]);
        });
    }

    function addAssemblyItemToSS(itemId, projectId) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('estimate/add_assembly_item_to_ss') }}",
            method: "POST",
            data: {
                _token: _token,
                item_id: itemId,
                project_id: projectId,
                sort_status: sortStatus
            },
            success: function(res) {
                someBlock.preloader('remove');
                SSData = res.data.ss_data;
                priceComments = res.data.price_comments;
                $("#ss_total_bar").html(res.data.total);
                updateSSAddons(projectId);

                spreadsheet.options.data = SSData;
                spreadsheet.refresh();

                hideMarkupCols();
                updateHiddenCols();
                setComment(priceComments);
                updateOverLine();
                updateSortStyle();
            }
        });
    }

    function updateCell(temp) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('estimate/update_ss_items') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: projectId,
                data: temp,
                sort_status: sortStatus
            },
            success: function(res) {
                someBlock.preloader('remove');
                if (res.status === 'success') {
                    SSData = res.data.ss_data;
                    spreadsheet.options.data = SSData;
                    priceComments = res.data.price_comments;
                    $("#ss_total_bar").html(res.data.total);
                    updateSSAddons(projectId);
                    spreadsheet.refresh();
                    hideMarkupCols();
                    updateHiddenCols();
                    setComment(priceComments);
                    updateOverLine();
                    updateSortStyle();
                    toastr.success(res.message);
                    console.log('update all TOQ occurrences');
                    if (res.data.isExistOccur > 1 && (temp.columnName === "L" || temp.columnName === "T" ||
                            temp.columnName === "AB")) { // update all TOQ occurrences
                        swal({
                            title: 'Change price for all occurrences of this item?',
                            text: "",
                            icon: "warning",
                            buttons: {
                                cancel: {
                                    text: "NO",
                                    value: false,
                                    visible: true,
                                    className: "",
                                    closeModal: true,
                                },
                                confirm: {
                                    text: "YES",
                                    value: true,
                                    visible: true,
                                    className: "",
                                    closeModal: true
                                }
                            }
                        }).then((yes) => {
                            if (yes) {
                                $.ajax({
                                    url: "{{ url('estimate/update_all_price_ss') }}",
                                    method: "POST",
                                    data: {
                                        _token: _token,
                                        project_id: projectId,
                                        data: temp,
                                        sort_status: sortStatus
                                    },
                                    success: function(res) {
                                        SSData = res.data.ss_data;
                                        spreadsheet.options.data = SSData;
                                        priceComments = res.data.price_comments;
                                        $("#ss_total_bar").html(res.data.total);
                                        updateSSAddons(projectId);
                                        spreadsheet
                                            .refresh(); // frozen error when update material price????

                                        hideMarkupCols();
                                        updateHiddenCols();
                                        setComment(priceComments);
                                        updateOverLine();
                                        updateSortStyle();
                                    }
                                });
                            }
                        });
                    } else {
                        SSData = res.data.ss_data;
                        spreadsheet.options.data = SSData;
                        priceComments = res.data.price_comments;
                        $("#ss_total_bar").html(res.data.total);
                        updateSSAddons(projectId);
                        spreadsheet.refresh(); // frozen error when update material price????

                        hideMarkupCols();
                        updateHiddenCols();
                        setComment(priceComments);
                        updateOverLine();
                        updateSortStyle();
                    }
                }
            }
        });
    }

    function updateSSAddons(projectId) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('estimate/update_addon_to_ss') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: projectId,
            },
            success: function(res) {
                someBlock.preloader('remove');
                // update the addon data table
                let addonData = res.data.addons;
                addonData.forEach(element => {
                    $('#add_on_area tbody #eachaddon' + element.id + ' td:nth-child(5)').html(
                        element.ss_add_on_value);
                });

                // let prev = $('#ss_total_bar').html();
                // let afterIndex = prev.indexOf('ADDONS');
                // let aftertext = prev.slice(0, afterIndex);
                // let after = aftertext + "ADDONS - $" + res.data.compoundvalue;
                // $('#ss_total_bar').html(after);

                $('#ss_total_bar').html(res.data.totalestimate);
            }
        });
    }

    // update ss setting - column width, row height
    function updateSetting(tableSetting) {
        $.ajax({
            url: "{{ url('estimate/update_ss_setting') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: projectId,
                data: JSON.stringify(tableSetting)
            },
            success: function(res) {
                console.log(res);
                updateHiddenCols();
            }
        });
    }

    // update ss sidebar status
    $('#sidebarCollapse').on('click', function() {
        let sidebarStatus = $(this).data('sidebar_status') ? 0 : 1;
        $(this).data('sidebar_status', sidebarStatus);
        $('#sidebar').toggleClass('active');
        $(this).toggleClass('open');
        $.ajax({
            url: "{{ url('estimate/update_ss_sidebar_status') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: projectId,
                sidebar_status: sidebarStatus
            },
            success: function(res) {
                console.log(res);
            }
        });
    });


    const deleteCheckedRows = function() {
        if (selectedRows.length) {
            someBlock.preloader();
            $.ajax({
                url: "{{ url('estimate/remove_bulk_ss_items') }}",
                method: "POST",
                data: {
                    _token: _token,
                    project_id: projectId,
                    selectedRows: selectedRows,
                    sort_status: sortStatus
                },
                success: function(res) {
                    selectedRows = [];
                    if (res.status === 'success') {
                        $('.remove_items').addClass('disabled');
                        $('.checkAll').prop('checked', false);
                        selectedRows = [];
                        SSData = res.data.ss_data;
                        priceComments = res.data.price_comments;
                        $("#ss_total_bar").html(res.data.total);
                        updateSSAddons(projectId);
                        spreadsheet.options.data = SSData;
                        spreadsheet.refresh();

                        hideMarkupCols();
                        updateHiddenCols();
                        setComment(priceComments);
                        updateOverLine();
                        updateSortStyle();
                    } else {
                        toastr.error(res.message);
                    }
                    someBlock.preloader('remove');
                }
            });
        }
    };

    const sortSS = function(sort) {
        sortStatus = sort;
        someBlock.preloader();
        $.ajax({
            url: "{{ url('estimate/sort_ss') }}",
            method: "POST",
            data: {
                _token: _token,
                project_id: projectId,
                sort_status: sortStatus
            },
            success: function(res) {
                someBlock.preloader('remove');
                SSData = res.data.ss_data;
                priceComments = res.data.price_comments;
                $("#ss_total_bar").html(res.data.total);
                updateSSAddons(projectId);

                spreadsheet.options.data = SSData;
                spreadsheet.refresh();

                hideMarkupCols();
                updateHiddenCols();
                setComment(priceComments);
                updateOverLine();
                updateSortStyle();
            }
        });
    };

    function updateSortStyle() {
        let $_sortDefault = $('.sort_default');
        let $_sortQuote = $('.sort_quote');
        let $_sortVendor = $('.sort_vendor');
        let $_sortLocation = $('.sort_location');
        let $_sortGroup = $('.sort_group');
        $_sortGroup.css('opacity', '0.5');
        $_sortLocation.css('opacity', '0.5');
        $_sortVendor.css('opacity', '0.5');
        $_sortQuote.css('opacity', '0.5');
        $_sortDefault.css('opacity', '0.5');
        if (sortStatus === 'ss_item_cost_group_number') {
            $_sortGroup.css('opacity', '1.0');
        } else if (sortStatus === 'ss_location') {
            $_sortLocation.css('opacity', '1.0');
        } else if (sortStatus === 'ss_selected_vendor') {
            $_sortVendor.css('opacity', '1.0');
        } else if (sortStatus === 'ss_quote_or_invoice_item') {
            $_sortQuote.css('opacity', '1.0');
        } else {
            $_sortDefault.css('opacity', '1.0');
        }
    }


    function updateOverLine() {
        let rows = sheetDOM.querySelector('tbody').querySelectorAll('tr');
        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.cells;
            if (cells[1].innerHTML.includes('over_line')) {
                row.classList = 'over_line_parent';
                cells[2].classList = 'checkAllOverLine';
                for (let j = 3; j < cells.length; j++) {
                    cells[j].classList = 'readonly';
                }
                if (cells[1].innerHTML.includes('ss_item_cost_group_number')) {
                    cells[4].classList = 'readonly expandCollapse';
                    sortStatus = 'ss_item_cost_group_number';
                } else if (cells[1].innerHTML.includes('ss_location')) {
                    cells[56].classList = 'readonly expandCollapse';
                    sortStatus = 'ss_location';
                } else if (cells[1].innerHTML.includes('ss_selected_vendor')) {
                    cells[53].classList = 'readonly expandCollapse';
                    sortStatus = 'ss_selected_vendor';
                } else if (cells[1].innerHTML.includes('ss_quote_or_invoice_item')) {
                    cells[54].classList = 'readonly expandCollapse';
                    sortStatus = 'ss_quote_or_invoice_item';
                } else {
                    sortStatus = 'default';
                }
                cells[55].classList = 'readonly summary summary-hidden';
            } else if (cells[55].innerHTML === '0' || cells[55].innerHTML === '') {
                row.classList = 'total_cost_zero';
            }
        }
    }

    function hideMarkupCols() {
        for (let i = 0; i < MARKUP_COLUMNS.length; i++) {
            let index = MARKUP_COLUMNS[i];
            spreadsheet.hideColumn(index);
        }
    }

    function updateHiddenCols() {
        let laborColumnStart = 9,
            laborColumnEnd = 13,
            subcontractColumnStart = 25,
            subcontractColumnEnd = 31,
            ssColumnEnd = 57;
        let mainColSpan = 7,
            labColSpan = 5,
            matColSpan = 5,
            subColSpan = 5,
            endColSpan = 17;
        if (tableSetting.hiddenCols.length) {
            tableSetting.hiddenCols.sort(function(a, b) {
                return a - b;
            });
            let hiddenColCnt = tableSetting.hiddenCols.length - 1;
            for (let j = 0; j <= hiddenColCnt; j++) {
                let index = tableSetting.hiddenCols[j];
                spreadsheet.hideColumn(index);
                if (laborColumnStart === index + 1) {
                    laborColumnStart++;
                }
                if (subcontractColumnStart === index + 1) {
                    subcontractColumnStart++;
                }

                // calc nested header colspan
                if (1 < index && index < 8) {
                    mainColSpan--;
                }
                if (7 < index && index < 13) {
                    labColSpan--;
                }
                if (15 < index && index < 21) {
                    matColSpan--;
                }
                if (23 < index && index < 29) {
                    subColSpan--;
                }
                if (39 < index) {
                    endColSpan--;
                }

            }
            for (let j = hiddenColCnt; j >= 0; j--) {
                let index = tableSetting.hiddenCols[j];
                if (laborColumnEnd === index + 1) {
                    laborColumnEnd--;
                }
                if (subcontractColumnEnd === index + 1) {
                    subcontractColumnEnd--;
                }
                if (ssColumnEnd === index + 1) {
                    ssColumnEnd--;
                }
            }
            $('.show_all_columns').show();
        } else {
            $('.show_all_columns').hide();
        }
        // striped column style
        let rows = sheetDOM.querySelector('table').querySelectorAll('tr');
        for (let i = 1; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.cells;
            for (let j = 9; j <= 13; j++) {
                cells[j].classList = '';
            }
            for (let j = 25; j <= 29; j++) {
                cells[j].classList = '';
            }
            for (let j = 39; j < cells.length; j++) {
                cells[j].classList = '';
            }
        }

        for (let i = 1; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.cells;
            if (laborColumnStart === laborColumnEnd) {
                cells[laborColumnStart].classList = 'labor_column_start_end';
            } else {
                cells[laborColumnStart].classList = 'labor_column_start';
                cells[laborColumnEnd].classList = 'labor_column_end';
            }
            if (subcontractColumnStart === subcontractColumnEnd) {
                cells[subcontractColumnStart].classList = 'subcontract_column_start_end';
            } else {
                cells[subcontractColumnStart].classList = 'subcontract_column_start';
                cells[subcontractColumnEnd].classList = 'subcontract_column_end';
            }
            cells[ssColumnEnd].classList = 'ss_column_end';

        }

        let nestedTds = sheetDOM.querySelector('.jexcel_nested').querySelectorAll('td');
        if (mainColSpan === 0) {
            nestedTds[1].classList = 'hide_nested_header';
        } else {
            nestedTds[1].classList = '';
        }
        if (labColSpan === 0) {
            nestedTds[2].classList = 'hide_nested_header';
        } else {
            nestedTds[2].classList = '';
        }
        if (matColSpan === 0) {
            nestedTds[3].classList = 'hide_nested_header';
        } else {
            nestedTds[3].classList = '';
        }
        if (subColSpan === 0) {
            nestedTds[4].classList = 'hide_nested_header';
        } else {
            nestedTds[4].classList = '';
        }
        if (endColSpan === 0) {
            nestedTds[5].classList = 'hide_nested_header';
        } else {
            nestedTds[5].classList = '';
        }
        spreadsheet.updateNestedHeader(0, 0, {
            colspan: mainColSpan
        });
        spreadsheet.updateNestedHeader(1, 0, {
            colspan: labColSpan
        });
        spreadsheet.updateNestedHeader(2, 0, {
            colspan: matColSpan
        });
        spreadsheet.updateNestedHeader(3, 0, {
            colspan: subColSpan
        });
        spreadsheet.updateNestedHeader(4, 0, {
            colspan: endColSpan
        });

    }


    function updateColumnHeader() {
        // let headers = sheetDOM.querySelector('thead').querySelectorAll('tr')[1].querySelectorAll('td');
        let thead = sheetDOM.querySelector('thead');
        let tr = thead.querySelectorAll('tr')[1];
        let tds = tr.querySelectorAll('td');
        let eyeHtml = `<br><i class="fa fa-eye hide-column"></i>`;
        for (let i = 3; i < tds.length; i++) {
            let headerText = tds[i].innerHTML;
            tds[i].innerHTML = `${headerText} ${eyeHtml}`;
        }
    }


    // update checkbox after all checked/not
    function updateCheckbox(checkboxArray, that) {
        let status = false;
        selectedRows = [];
        if (that.is(":checked")) {
            status = true;
            for (let i = 0; i < SSData.length; i++) {
                let itemId = SSData[i][0];
                if (typeof itemId === 'number') {
                    selectedRows.push(itemId);
                }
            }
            if (selectedRows.length)
                $('.remove_items').removeClass('disabled');
        } else {
            $('.remove_items').addClass('disabled');
        }
        for (let i = 0; i < checkboxArray.length; i++) {
            checkboxArray[i].checked = status;
        }
    }

    // check all cost type over line
    function checkAllOverLine(cell, value) {
        let parent = $(cell).parent()[0];
        let rows = $(parent).nextUntil('tr.over_line_parent');
        for (let i = 0; i < rows.length; i++) {
            let itemId = rows[i].cells[1].innerHTML;
            let checkboxInput = rows[i].cells[2].querySelector('input');
            checkboxInput.checked = value;
            if (value) {
                if (!selectedRows.includes(itemId))
                    selectedRows.push(itemId);
            } else {
                let index = selectedRows.indexOf(itemId);
                if (index > -1) {
                    selectedRows.splice(index, 1);
                }
            }
        }
        // update check all status
        let cntChecked = sheetDOM.querySelectorAll("input[type=checkbox]:checked").length;
        let $_checkAll = $('.checkAll');
        if ($_checkAll.is(':checked')) {
            cntChecked--;
        }
        if (cntChecked === SSData.length) {
            $_checkAll.prop('checked', true);
        } else {
            $_checkAll.prop('checked', false);
        }
        // update trash icon style
        if (selectedRows.length) {
            $('.remove_items').removeClass('disabled');
        } else {
            $('.remove_items').addClass('disabled');
        }
    }


    $(document).on('change', '.checkAll', function() {
        let checkboxArray = sheetDOM.querySelectorAll("input[type='checkbox']");
        updateCheckbox(checkboxArray, $(this));
    });

    // expand/collapse over line
    $(document).on('click', '.expandCollapse', function() {
        let parent = $(this).parent()[0];
        let summaryCell = $(parent).find('.summary')[0];
        $(parent).nextUntil('tr.over_line_parent').toggle();
        $(summaryCell).toggleClass('summary-hidden');
    });


    // show/hide column
    $(document).on('click', '.hide-column', function() {
        let index = $(this).parent().index() - 1;
        spreadsheet.hideColumn(index);
        tableSetting.hiddenCols.push(index);
        $('.show_all_columns').show();
        updateSetting(tableSetting);
    });


    // get price
    /* $("#submit_get_price").click(function() {
        if (selectedRows.length) {
            $("#price_selected_rows").val(selectedRows);
            someBlock.preloader();
            $(this).closest("form").submit();
        } else {
            toastr.error("Please select item");
        }
    }); */

    $('#frmGetPriceLookup').on('submit', function(event) {
        event.preventDefault();
        if (selectedRows.length) {
            $("#price_selected_rows").val(selectedRows);
            someBlock.preloader();
            // $(this).closest("form").submit();
            var url = $(this).attr("action");
            var fd = new FormData(this);
            fd.append('sort_status', sortStatus);

            $.ajax({
                url: url,
                method: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    SSData = res.data.ss_data;
                    priceComments = res.data.price_comments;
                    $("#ss_total_bar").html(res.data.total);
                    updateSSAddons(projectId);

                    spreadsheet.options.data = SSData;
                    spreadsheet.refresh();

                    hideMarkupCols();
                    updateHiddenCols();
                    setComment(priceComments);
                    updateOverLine();
                    updateSortStyle();


                    someBlock.preloader('remove');
                    $('#price_modal').modal('hide');
                    if (res.status === 'success')
                        toastr.success(res.message);
                    else
                        toastr.error(res.message);
                }
            });


        } else {
            toastr.error("Please select item");
        }
    });


    var spreadsheet = null;
    var SSData = [];
    var sheetDOM;
    var selectedRows = [];
    var UOM = @json($uom);
    var invoiceItemList = @json($proposal_items_list);
    var priceComments = @json($price_comments);
    var tableSetting = {
        colWidth: {},
        rowHeight: {},
        hiddenCols: []
    };
    //    var rowHeight = [];
    var sortStatus = 'default';
    const MIN_COL_COUNT = 49;
    const MARKUP_COLUMNS = [13, 14, 15, 21, 22, 23, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39];

    function updateCellWidthHeight() {
        let colWidth = tableSetting.colWidth;
        let width = Object.keys(colWidth);
        for (let i = 0; i < width.length; i++) {
            let colIndex = width[i];
            spreadsheet.setWidth(colIndex, colWidth[colIndex].val);
        }
    }

    // prevent input zero for conversion factor cell
    const beforeChange = function(instance, cell, x, y, value) {
        if (x === "8" && value <= 0)
            return 1;
        if (x === "16" && value <= 0)
            return 1;
        if (x === "24" && value <= 0)
            return 1;
        if (x === "32" && value <= 0)
            return 1;
        return value;
    };

    // delete
    const beforeDeleteRow = function(el, rowNumber, numOfRows) {
        return SSData[rowNumber][0];
    };

    //    var blur = function(instance) {
    //      console.log('blur>>>', instance);
    //    };
    //
    //    // global event
    //    var eventHandler = function(event,a,b,c,d,e,f) {
    //        if($('.preloader').length){
    ////            event.preventDefault();
    //            blur();
    //        }
    //    };

    // update
    const changed = function(instance, cell, x, y, value) {
        // check all cost type over line
        if (cell.className.includes('checkAllOverLine')) {
            checkAllOverLine(cell, value);
            return true;
        }

        let itemId = SSData[y][0];
        // 9 12 25 28 17 20 46 33 36
        if (x === 9 || x === 12 || x === 17 || x === 20 || x === 25 ||
            x === 28 || x === 33 || x === 36 || x === 52) { // LOQ, LT, MOQ, MT, SOQ, ST, OOQ, OT, Total
            value = $(cell).text();
        }

        if (x === "1") { // checkbox
            if (value) {
                if (!selectedRows.includes(itemId) && (typeof itemId === 'number'))
                    selectedRows.push(itemId);
            } else {
                let index = selectedRows.indexOf(itemId);
                if (index > -1) {
                    selectedRows.splice(index, 1);
                }
            }
            // get over line rows
            let tr = $(cell).parent();
            // update over line check all checkbox
            let overLineRow = $(tr).prevAll('tr.over_line_parent').first();
            let $_checkAll = $('.checkAll');
            if (overLineRow.length) {
                let overLineRows = $(overLineRow).nextUntil('tr.over_line_parent');
                let cntCheckedOverLine = overLineRows.find("input:checkbox:checked").length;
                let overLineCheckbox = overLineRow[0].cells[2].querySelector('input');
                overLineCheckbox.checked = cntCheckedOverLine === overLineRows.length;
            }
            // update check all checkbox
            let cntChecked = sheetDOM.querySelectorAll("input[type=checkbox]:checked").length;
            if ($_checkAll.is(':checked')) {
                cntChecked--;
            }
            if (cntChecked === SSData.length) {
                $_checkAll.prop('checked', true);
            } else {
                $_checkAll.prop('checked', false);
            }
            if (selectedRows.length) {
                $('.remove_items').removeClass('disabled');
            } else {
                $('.remove_items').addClass('disabled');
            }
        } else {
            let cellName = jexcel.getColumnNameFromId([x, y]);
            let columnName = cellName.match(/[a-zA-Z]+/g)[0];
            if (x === 9 || x === 12 || x === 17 || x === 20 || x === 25 ||
                x === 28 || x === 33 || x === 36 || x === 54) { // LOQ, LT, MOQ, MT, SOQ, ST, OOQ, OT, Total
                console.log('no action');
            } else {
                if (x === '55' && value.length > 99) {
                    toastr.error('Location text should be less than 100 letters');
                } else if (itemId) {
                    if (x === '53' && value === "No Quote/Invoice") {
                        value = "";
                    }
                    let temp = {
                        id: itemId,
                        columnName: columnName,
                        // updateAllOccurPrice: false,
                        value: value,
                    };
                    updateCell(temp);
                }
            }
        }
    };

    // resize cost type column
    const resizeColumnWidth = function(instance, cell, width) {
        let cellIndex = cell[0];
        if (tableSetting.colWidth[cellIndex]) {
            tableSetting.colWidth[cellIndex] =
                Object.assign(tableSetting.colWidth[cellIndex], {
                    col: cellIndex,
                    val: width
                })
        } else {
            tableSetting.colWidth[cellIndex] = {
                col: cellIndex,
                val: width
            }
        }
        updateSetting(tableSetting);
    };

    // activate Get Measure button when highlighted TOQ field
    const selectionActive = function(instance, x1, y1, x2, y2, origin) {
        let SSItemId = 0;
        let $_get_measuring = $('.get_measuring');
        let $_get_price = $('.get_price');
        $_get_measuring.fadeOut();
        $_get_price.fadeOut();
        if (x1 === 6) {
            SSItemId = SSData[y1][0];
            globalTOQ['x'] = x1;
            globalTOQ['y'] = y1;
            globalTOQ['SSItemId'] = SSItemId;
            $_get_measuring.fadeIn();
        }
        if (x1 === 19) {
            SSItemId = SSData[y1][0];
            globalTOQ['x'] = x1;
            globalTOQ['y'] = y1;
            globalTOQ['SSItemId'] = SSItemId;
            $_get_price.fadeIn();
        }
        if (x1 === 11) {
            SSItemId = SSData[y1][0];
            globalTOQ['x'] = x1;
            globalTOQ['y'] = y1;
            globalTOQ['SSItemId'] = SSItemId;
            $_get_price.fadeIn();
        }
    };


    // update cost type sheet after loaded
    const sheetLoaded = function(instance) {
        sheetDOM = instance.querySelector('.jtabs-content').querySelector('.jtabs-selected');
        let thead = sheetDOM.querySelector('thead');
        let tr = thead.querySelectorAll('tr')[1];
        let checkAllDOM = tr.querySelector('td:nth-child(3)');
        checkAllDOM.innerHTML = "<input type='checkbox' class='checkAll'>";

        let total = @json($total);
        $("#ss_total_bar").html(total);
        $('.get_measuring').hide();
        $('.get_price').hide();
        $('.show_all_columns').hide();
        $('.sort_default').css('opacity', '1.0');

        updateColumnHeader();
        updateOverLine();
    };

    const showAllColumns = function() {
        for (let i = 1; i < MIN_COL_COUNT; i++) {
            if (!MARKUP_COLUMNS.includes(i))
                spreadsheet.showColumn(i);
        }

        $('.show_all_columns').hide();
        tableSetting.hiddenCols = [];
        updateSetting(tableSetting);
    };


    // config jexcel
    const columns = [{
            type: 'hidden',
            title: 'id'
        },
        {
            type: 'checkbox',
            title: 'Select all'
        },
        {
            type: 'text',
            title: 'Cost Group number',
            wordWrap: true,
            readOnly: true
        },
        {
            type: 'text',
            title: 'Cost Group description',
            wordWrap: true,
            readOnly: true
        },
        {
            type: 'text',
            title: 'Item number',
            wordWrap: true,
            readOnly: true
        },
        {
            type: 'text',
            title: 'Item description',
            wordWrap: true,
            readOnly: true
        },
        {
            type: 'text',
            strict: true,
            wordWrap: true,
            title: 'Takeoff Qty',
        },
        {
            type: 'dropdown',
            wordWrap: true,
            title: 'Takeoff UOM',
            source: UOM
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Conversion to Order UOM'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Order Qty',
            readOnly: true
        },
        {
            type: 'dropdown',
            wordWrap: true,
            title: 'Order UOM',
            source: UOM
        },
        {
            type: 'text',
            strict: true,
            wordWrap: true,
            title: 'Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Total',
            readOnly: true
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Markup %'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Markup amount'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Marked up total'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Conversion to Order UOM'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Order Qty',
            readOnly: true
        },
        {
            type: 'dropdown',
            wordWrap: true,
            title: 'Order UOM',
            source: UOM
        },
        {
            type: 'text',
            strict: true,
            wordWrap: true,
            title: 'Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Total',
            readOnly: true
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Markup %'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Markup amount'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Marked up total'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Conversion to Order UOM'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Order Qty',
            readOnly: true
        },
        {
            type: 'dropdown',
            wordWrap: true,
            title: 'Order UOM',
            source: UOM
        },
        {
            type: 'text',
            numericFormat: {
                pattern: '0,0'
            },
            strict: true,
            wordWrap: true,
            title: 'Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Total',
            readOnly: true
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Markup %'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Other markup amount'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Other marked up total'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Other Conversion Factor'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Other Order Qty',
            readOnly: true
        },
        {
            type: 'dropdown',
            wordWrap: true,
            title: 'Other UOM',
            source: UOM
        },
        {
            type: 'text',
            strict: true,
            wordWrap: true,
            title: 'Other Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Other Total',
            readOnly: true
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Other markup %'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Subcontract markup amount'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Other marked up total'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'HD SKU'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'HD Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Lowes SKU'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Lowes Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Whitecap SKU'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Whitecap Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'BLS Number'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'BLS Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Grainger Number'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Grainger Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'WCYW Number'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'WCYW Price'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Selected Vendor'
        },
        {
            type: 'dropdown',
            wordWrap: true,
            title: 'Quote/Invoice item',
            source: invoiceItemList
        },
        {
            type: 'text',
            wordWrap: true,
            readOnly: true,
            title: 'Total Cost'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Location'
        },
        {
            type: 'text',
            wordWrap: true,
            title: 'Notes'
        }
    ];

    $(document).on('click', '.email_pdf_jmodal', function() {
        $("#email_pdf_modal").modal();
    });

    const customToolbar = {
        items: [{
                type: 'i',
                content: '.',
                width: 50,
                class: 'ss_save',
                onclick: function() {
                    // file name
                    let updatedProjectName = projectName.replace(/["']/g, "");
                    jexcel.download(document.getElementById('spreadsheet'), updatedProjectName);
                    // jexcel.download(document.getElementById('spreadsheet'));
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                class: 'ss_print',
                onclick: function() {
                    console.log('print clicked', jexcel.current);
                    //                    jexcel.current.plugins.print.setRange("C:M");
                    jexcel.current.plugins.print.open();
                    let printBtnParentDiv = document.getElementById("print").parentNode;
                    printBtnParentDiv.innerHTML +=
                        '<input type="button" value="Email PDF" class="email_pdf_jmodal">';
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                class: 'remove_items disabled',
                onclick: deleteCheckedRows
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                title: 'Sort Default',
                class: 'sort_default',
                onclick: function() {
                    sortSS('default');
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                title: 'Sort Selected Vendor',
                class: 'sort_vendor',
                onclick: function() {
                    sortSS('ss_selected_vendor');
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                title: 'Sort Cost Group',
                class: 'sort_group',
                onclick: function() {
                    sortSS('ss_item_cost_group_number');
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                title: 'Sort Location',
                class: 'sort_location',
                onclick: function() {
                    sortSS('ss_location');
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                class: 'sort_quote',
                title: 'Sort Quote/Invoice Item',
                onclick: function() {
                    sortSS('ss_quote_or_invoice_item');
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                class: 'show_all_columns',
                onclick: showAllColumns
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                class: 'update_lib_price',
                onclick: function() {
                    console.log('update_lib_price');
                    someBlock.preloader();
                    $.ajax({
                        url: '{{ route('proposal.add_proposal_from_cost_items') }}',
                        type: 'POST',
                        data: {
                            _token: _token,
                            projectId: projectId
                        },
                        success: function(data) {
                            someBlock.preloader('remove');
                            if (data.status === 'success')
                                toastr.success(data.message);
                            else
                                toastr.error(data.message);
                        }
                    });
                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                class: 'get_measuring',
                onclick: function() {
                    console.log('get measuring clicked');
                    if (typeof globalTOQ.SSItemId === 'number') {
                        globalTOQ['redirect_url'] = window.location.href;
                        localStorage.setItem('globalTOQ', JSON.stringify(globalTOQ));
                        window.location.href = "{{ url('documents/' . $page_info['project_id']) }}";
                    } else {
                        console.log('Please select correct field', globalTOQ.SSItemId);
                        toastr.error('Please select correct field');
                    }

                }
            },
            {
                type: 'i',
                content: '.',
                width: 50,
                class: 'get_price',
                onclick: function() {
                    $('#price_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                }
            }
        ]
    };

    var LICENSE_KEY = '{{ env('JEXCEL_LICENSE_KEY') }}';
    var MIN_DIMENSION = [MIN_COL_COUNT, 0];


    const plugin = [{
        name: 'print',
        plugin: jexcel_print,
        options: {
            range: "D:M"
        }
    }];

    var nestedHeaders = [
        [{
                title: 'ITEM',
                colspan: '7'
            },
            {
                title: 'LABOR',
                colspan: '5'
            },
            {
                title: 'MATERIAL',
                colspan: '5'
            },
            {
                title: 'SUBCONTRACT',
                colspan: '5'
            },
            {
                title: '',
                colspan: '15'
            }
        ]
    ];

    const sheet = {
        data: [],
        columns: columns,
        nestedHeaders: nestedHeaders,
        tableWidth: "80vw",
        //        colWidths: [],
        //        rows: rowHeight,
        //        rowHeights: sheetRowHeight,
        //        rows: { 3: { height:'100px' }},
        //        worksheetName: 'By Cost Type',
        //        footers: [],
        toolbar: customToolbar,
        plugins: plugin,
        comments: priceComments,
        oncomments: function() {
            console.log('on comments>>>');
        },
        allowComments: true,
        rowResize: true,
        allowMoveWorksheet: false,
        allowRenameWorksheet: false,
        allowDeleteWorksheet: false,
        tableOverflow: true,
        textOverflow: false,
        allowRenameColumn: false,
        allowInsertColumn: false,
        allowManualInsertColumn: false,
        allowDeleteColumn: false,
        allowInsertRow: false,
        allowDeleteRow: false,
        allowManualInsertRow: false,

        onbeforedeleterow: beforeDeleteRow,
        onchange: changed,
        onbeforechange: beforeChange,
        onselection: selectionActive,
        onload: sheetLoaded,
        onresizecolumn: resizeColumnWidth,
        //        onresizerow: resizeRowHeight,
        //        onblur: blur,
        //        onevent: eventHandler,

        minDimensions: MIN_DIMENSION,
        license: LICENSE_KEY
    };
</script>
