<script>
    /**
     * Turn on interview for cost item
     */

    var formulaModalHTML = null;

    // get cost item formula
    function getItemFormula(id, itemTxt) {
        $.ajax({
            url: "{{ url('estimate/get_formula') }}",
            method: "POST",
            data: {
                _token: _token,
                item_id: id
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (res.data.length)
                        itemFormulaParse(id, itemTxt, res.data);
                    else
                        addItemToSS(id, projectId)
                } else {
                    toastr.error('Oops...Server error');
                }
            }
        });
    }

    // parse item formula
    function itemFormulaParse(itemId, itemTxt, formulaData) {
        let parsedResult = null;

        let tempTags = formulaData;
        let isIFFormula = formulaData.some(e => e.val === 'IF');
        if (tempTags[0].val === '(' && isIFFormula) {
            while (tempTags[0].val !== 'IF') {
                tempTags.splice(0, 1);
                tempTags.splice(tempTags.length - 1, 1);
            }
        }

        if (tempTags[0].type === 'operator' && tempTags[0].val === 'IF') {
            parsedResult = parseIfFormula(tempTags);
            globalItemFormula = {
                type: 'if',
                formula: parsedResult
            };
        } else {
            parsedResult = parseNormalFormula(tempTags);
            globalItemFormula = {
                type: 'normal',
                formula: parsedResult
            };
        }

        // generate formula form
        let formulaForm = genFormulaForm(globalItemVal, globalItemFormula, true);
        $('#ss_item_id').val(itemId);
        $('#item_interview_modal_title').html(itemTxt);
        $('#parsed_formula_div').html(formulaForm.formula);
        $('#formula_body').html(formulaForm.html);

        formulaModalHTML = {
            ss_item_id: itemId,
            itemTxt: itemTxt,
            parsed_formula_div: formulaForm.formula,
            formula_body: formulaForm.html
        };

        refreshModal();
    }


    // save TOQ by interview process
    function addCostItemByInterview(itemId, TOQ) {
        if (itemId) {
            //            someBlock.preloader();
            $.ajax({
                url: "{{ url('estimate/add_item_to_ss_by_interview') }}",
                method: "POST",
                data: {
                    _token: _token,
                    item_id: itemId,
                    project_id: projectId,
                    TOQ: TOQ,
                    sort_status: sortStatus
                },
                success: function(res) {
                    someBlock.preloader('remove');
                    // document.getElementById('formula_body').innerHTML = '';
                    // $('#formula_modal').modal('hide');

                    SSData = res.data.ss_data;
                    priceComments = res.data.price_comments;
                    $("#ss_total_bar").html(res.data.total);

                    spreadsheet.options.data = SSData;
                    spreadsheet.refresh();

                    updateOverLine();
                    hideMarkupCols();
                    updateHiddenCols();
                    setComment(priceComments);
                    updateSortStyle();
                }
            });
        } else {
            someBlock.preloader('remove');
            toastr.error('Unknown error');
        }
    }


    // add cost item to spreadsheet
    $('#costitem_list').on("select_node.jstree", function(e, data) {
        if (data.node.id.includes('costitem')) {
            e.preventDefault();
            localStorage.removeItem('globalTOQ'); // remove globalTOQ
            let itemId = data.node.id.replace('costitem-', '');
            let isTurnOnInterview = $('.turn-on-interview').data('checked');
            if (isTurnOnInterview) {
                let itemText = data.node.text;
                globalItemFormula = null;
                globalItemVal = [];
                globalItemNormalVal = [];
                getItemFormula(itemId, itemText);
            } else {
                addItemToSS(itemId, projectId);
            }
        }
    });

    // calculate condition clause
    $("#formula_modal").on('keyup change', '#formula_body input', function() {
        if ($(this).val()) {
            refreshModal();
        }
    });

    // draggable modal
    $('.modal-dialog').draggable({
        "handle": ".modal-header"
    });

    // resizable modal
    $('#formula_modal').find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

    $('#assembly_formula_modal').find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

    $('#price_modal').find('.modal-content')
        .resizable({
            handles: 'n, e, s, w, ne, sw, se, nw'
        });

    // show/hide measure icon of item interview
    $(document).on('focus', '#formula_body input', function() {
        lastValid = '';
        let focusedInputId = $(this).attr('id');
        let measureToolId = "#measure_" + focusedInputId;
        $(measureToolId).fadeIn();
    });
    $(document).on('focusout', '#formula_body input', function() {
        let focusedInputId = $(this).attr('id');
        let measureToolId = "#measure_" + focusedInputId;
        $(measureToolId).fadeOut();
    });

    // add cost item to spreadsheet by interview
    $('#save_interview').click(function() {
        let interviewTOQ = JSON.parse(localStorage.getItem('globalTOQ'));
        if (interviewTOQ) {
            globalItemFormula = globalTOQ['globalItemFormula'];
            globalItemVal = globalTOQ['globalItemVal'];
            globalItemNormalVal = globalTOQ['globalItemNormalVal'];
            formulaModalHTML = globalTOQ['formulaModalHTML'];
        }
        let calcResult = calcItemFormula();
        if (calcResult.error) {
            someBlock.preloader('remove');
            toastr.error('Error in calculating cost item interview');
            console.log('Error in calculating cost item interview')
        } else {
            let itemId = $('#ss_item_id').val();
            let result = calcResult.result;
            localStorage.removeItem('globalTOQ'); // remove globalTOQ after save
            if (result.val === 0) {
                someBlock.preloader('remove');
                // $('#formula_modal').modal('hide');
            } else {
                addCostItemByInterview(itemId, result);
            }
        }
    });

    // get measure by interview
    $(document).on('click', '.get_measuring_by_interview', function() {
        let measureId = $(this).data('id');
        if (measureId) {
            let interviewTOQ = JSON.parse(localStorage.getItem('globalTOQ'));
            let originURL = window.location.href;
            if (interviewTOQ) {
                interviewTOQ['measureId'] = measureId;
                interviewTOQ['values'] = [];
                globalItemVal.forEach(item => {
                    let temp_val;
                    if (item.label.endsWith("?")) {
                        temp_val = getRadioValue(item.id);
                    } else {
                        temp_val = document.getElementById(item.id).querySelector('input').value;
                    }
                    interviewTOQ['values'].push({
                        id: item.id,
                        label: item.label,
                        value: temp_val
                    });
                });
                localStorage.setItem('globalTOQ', JSON.stringify(interviewTOQ));
            } else {
                globalTOQ['measureId'] = measureId;
                globalTOQ['modalId'] = "formula_modal";
                globalTOQ['redirect_url'] = originURL;
                globalTOQ['formulaModalHTML'] = formulaModalHTML;

                globalTOQ['globalItemFormula'] = globalItemFormula;
                globalTOQ['globalItemVal'] = globalItemVal;
                globalTOQ['globalItemNormalVal'] = globalItemNormalVal;
                globalTOQ['values'] = [];

                globalItemVal.forEach(item => {
                    let temp_val;
                    if (item.label.endsWith("?")) {
                        temp_val = getRadioValue(item.id);
                    } else {
                        temp_val = document.getElementById(item.id).querySelector('input').value;
                    }
                    globalTOQ['values'].push({
                        id: item.id,
                        label: item.label,
                        value: temp_val
                    });
                });
                localStorage.setItem('globalTOQ', JSON.stringify(globalTOQ));
            }
            window.location.href = originURL + '/2';
        } else {
            console.error('Invalid id');
        }
    });


    /**
     * Turn on interview for assembly
     */
    var formulaModalHTMLAssembly = '';
    var globalAssemblyNormalVal = [];
    var globalAssemblyVal = [];
    var globalAssemblyFormula = [];

    function getFormulaAssembly(id, itemTxt) {
        $.ajax({
            url: "{{ url('estimate/get_formula_assembly') }}",
            method: "POST",
            data: {
                _token: _token,
                item_id: id
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (res.data.length) {
                        parseAssemblyFormula(id, itemTxt, res.data);
                    } else {
                        someBlock.preloader('remove');
                        addAssemblyItemToSS(id, projectId)
                    }
                } else {
                    someBlock.preloader('remove');
                    toastr.error('Oops...Server error');
                }
            }
        });
    }

    function parseAssemblyFormula(itemId, itemTxt, params) {
        for (let i = 0; i < params.length; i++) {
            let costItemId = params[i][0];

            let costItemFormula = params[i][1];
            let isIFFormula = costItemFormula.some(e => e.val === 'IF');
            if (costItemFormula[0].val === '(' && isIFFormula) {
                while (costItemFormula[0].val !== 'IF') {
                    costItemFormula.splice(0, 1);
                    costItemFormula.splice(costItemFormula.length - 1, 1);
                }
            }

            for (let j = 0; j < costItemFormula.length; j++) {
                let item = costItemFormula[j];
                if (item.type === 'variable') {
                    // parse normal formula
                    let formula = parseNormalCostItemClause(costItemFormula, costItemId);
                    if (item.questionType === 'standard') {
                        globalAssemblyFormula.push({
                            id: i,
                            type: 'normal',
                            costItemId: costItemId,
                            formula: formula
                        });
                    }
                    break;
                } else if (item.type === 'operator' && item.val === 'IF') {
                    // parse IF formula
                    let parseResult = parseIfCostItemClause(costItemFormula);
                    globalAssemblyFormula.push({
                        id: i,
                        type: 'if',
                        costItemId: costItemId,
                        formula: parseResult
                    });
                    break;
                }
            }
        }

        console.log(globalAssemblyFormula, globalAssemblyVal, globalAssemblyNormalVal);

        let formulaForm = genFormulaForm(globalAssemblyVal, null, true);
        $('#assembly_interview_modal_title').html(itemTxt);
        $('#ss_assembly_item_id').val(itemId);
        $('#assembly_formula_body').html(formulaForm.html);

        formulaModalHTMLAssembly = {
            ss_assembly_item_id: itemId,
            itemTxt: itemTxt,
            assembly_formula_body: formulaForm.html
        };

        someBlock.preloader('remove');
        refreshAssemblyModal();
    }

    function parseIfCostItemClause(params) {
        let conditionFormula = '',
            trueFormula = '',
            falseFormula = '';
        let conditionVal = [],
            trueVal = [],
            falseVal = [];
        let trueFormulaTarget = 'standard',
            falseFormulaTarget = 'standard';
        for (let i = 0; i < params.length; i++) {
            // get condition clause
            i++;
            let endCondition = ',';
            let countOfBracket = 0;
            while (params[i].val !== endCondition) {
                conditionFormula += params[i].val;
                if (params[i].val === '(') {
                    countOfBracket++;
                }
                if (params[i].val === 'AND' || params[i].val === 'OR') {
                    endCondition = ')';
                }
                let isDuplicated = globalAssemblyVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        questionType: params[i].questionType,
                        label: params[i].val,
                        help: params[i].help
                    };
                    globalAssemblyVal.push(temp);
                }
                isDuplicated = globalAssemblyNormalVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        questionType: params[i].questionType,
                        label: params[i].val,
                        help: params[i].help
                    };
                    globalAssemblyNormalVal.push(temp);
                }
                // get condition variables
                isDuplicated = conditionVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        questionType: params[i].questionType,
                        label: params[i].val,
                        help: params[i].help
                    };
                    conditionVal.push(temp);
                }
                i++;
            }
            for (let z = 0; z < countOfBracket; z++) {
                conditionFormula += ')';
            }
            if (endCondition === ')') {
                i++;
            }

            // get true clause
            i++;
            while (params[i].val !== ',') {
                trueFormula += params[i].val;
                let isDuplicated = globalAssemblyVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces
                    if (params[i].questionType === 'total') {
                        trueFormulaTarget = 'total';
                    }
                    let temp = {
                        id: temp_id,
                        questionType: params[i].questionType,
                        label: params[i].val,
                        help: params[i].help
                    };
                    globalAssemblyVal.push(temp);
                }
                // get true clause variables
                isDuplicated = trueVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        questionType: params[i].questionType,
                        label: params[i].val,
                        help: params[i].help
                    };
                    trueVal.push(temp);
                }
                i++;
            }

            // get false clause
            i++;
            while (params[i].val !== ')') {
                let isDuplicated = globalAssemblyVal.some(el => el.label === params[i].val);
                falseFormula += params[i].val;
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces
                    if (params[i].questionType === 'total') {
                        falseFormulaTarget = 'total';
                    }
                    let temp = {
                        id: temp_id,
                        questionType: params[i].questionType,
                        label: params[i].val,
                        help: params[i].help
                    };
                    globalAssemblyVal.push(temp);
                }
                // get false clause variables
                isDuplicated = falseVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        questionType: params[i].questionType,
                        label: params[i].val,
                        help: params[i].help
                    };
                    falseVal.push(temp);
                }
                i++;
            }
        }

        return {
            conditionFormula: conditionFormula,
            conditionVal: conditionVal,
            trueFormula: trueFormula,
            trueFormulaTarget: trueFormulaTarget,
            trueVal: trueVal,
            falseFormula: falseFormula,
            falseFormulaTarget: falseFormulaTarget,
            falseVal: falseVal
        };
    }

    function parseNormalCostItemClause(params, costItemId) {
        let formula = '';
        params.forEach(function(item) {
            formula += item.val;
            if (item.type === 'variable') {
                let isDuplicated = globalAssemblyVal.some(el => el.label === item.val);
                if (!isDuplicated) {
                    let temp_id = item.val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g,
                        '__'); // remove spaces, #
                    let temp = {
                        id: temp_id,
                        costItemId: costItemId,
                        questionType: item.questionType,
                        label: item.val,
                        help: item.help
                    };
                    globalAssemblyVal.push(temp);
                }
                isDuplicated = globalAssemblyNormalVal.some(el => el.label === item.val);
                if (!isDuplicated) {
                    let temp_id = item.val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__');
                    let temp = {
                        id: temp_id,
                        questionType: item.questionType,
                        label: item.val,
                        help: item.help
                    };
                    globalAssemblyNormalVal.push(temp);
                }
            }
        });
        return formula;
    }


    function refreshAssemblyModal() {
        let toBeHiddenVal = [];
        let toBeShownVal = [];
        someBlock.preloader();
        globalAssemblyFormula.forEach(item => {
            if (item.type === "if") {
                let conditionFormula = item.formula.conditionFormula;
                let conditionVal = item.formula.conditionVal;

                conditionVal.forEach(function(con) {
                    if (con.label.endsWith("?")) {
                        let checkedRadioVal = getRadioValue(con.id);
                        if (checkedRadioVal === 2) { // hide all true/false val
                            updateShowHideArray(item.formula.falseVal, [], toBeHiddenVal, toBeShownVal);
                            updateShowHideArray(item.formula.trueVal, [], toBeHiddenVal, toBeShownVal);
                        } else {
                            conditionFormula = conditionFormula.replaceAll(con.label, checkedRadioVal);
                        }
                    } else {
                        let val = document.getElementById(con.id).querySelector('input').value;
                        if (val === '') val = 0;
                        conditionFormula = conditionFormula.replaceAll(con.label, val);
                    }
                });
                let calcResult = parser.parse(conditionFormula);
                if (calcResult.error) {
                    let errorMsg = formulaErrorMesages.find(o => o.code === calcResult.error);
                    // toastr.error(errorMsg.text);
                    someBlock.preloader('remove');
                } else {
                    if (calcResult.result) {
                        updateShowHideArray(item.formula.falseVal, item.formula.trueVal, toBeHiddenVal,
                            toBeShownVal);
                    } else {
                        updateShowHideArray(item.formula.trueVal, item.formula.falseVal, toBeHiddenVal,
                            toBeShownVal);
                    }
                }
            }
        });

        toBeHiddenVal.forEach(e => {
            let isDuplicated = globalAssemblyNormalVal.some(el => el.id === e.id);
            if (!isDuplicated) {
                document.getElementById(e.id).style.display = 'none';
            }
        });
        toBeShownVal.forEach(e => {
            document.getElementById(e.id).style.display = 'flex';
        });

        $('#assembly_formula_modal').modal({
            backdrop: 'static',
            keyboard: false
        });
        someBlock.preloader('remove');
    }


    function calcAssemblyFormula() {
        someBlock.preloader();
        let formulas = globalAssemblyFormula;
        let assemblyVal = globalAssemblyVal;
        let values = [];
        let errorFlag = false;
        let data = [];

        // get values from user input
        assemblyVal.forEach(function(item) {
            if (!item.label.endsWith("?")) {
                if (item.questionType === 'total') {
                    let val = document.getElementById(item.id + '__total').value;
                    if (val === '') val = 0;
                    let temp = {
                        id: item.id,
                        label: item.label,
                        val: val
                    };
                    values.push(temp);
                    if (item.costItemId) {
                        data.push({
                            costItemId: item.costItemId,
                            DSQType: 'total',
                            val: val
                        });
                    }
                } else if (item.questionType === 'category') {
                    let categoryLab = document.getElementById(item.id + '__category_lab').value;
                    let categoryMat = document.getElementById(item.id + '__category_mat').value;
                    if (categoryLab === '') categoryLab = 0;
                    if (categoryMat === '') categoryMat = 0;
                    // let temp = {id: item.id, label: item.label, val: val};
                    // values.push(temp);
                    if (item.costItemId) {
                        data.push({
                            costItemId: item.costItemId,
                            DSQType: 'category',
                            val: {
                                categoryLab: categoryLab,
                                categoryMat: categoryMat
                            }
                        });
                    }
                } else if (item.questionType === 'tricky') {
                    let trickyOfUnites = document.getElementById(item.id + '__tricky_of_unites').value;
                    let trickyPerUnit = document.getElementById(item.id + '__tricky_per_unit').value;
                    if (trickyOfUnites === '') trickyOfUnites = 0;
                    if (trickyPerUnit === '') trickyPerUnit = 0;
                    // let temp = {id: item.id, label: item.label, val: val};
                    // values.push(temp);
                    if (item.costItemId) {
                        data.push({
                            costItemId: item.costItemId,
                            DSQType: 'tricky',
                            val: {
                                trickyOfUnites: trickyOfUnites,
                                trickyPerUnit: trickyPerUnit
                            }
                        });
                    }
                } else {
                    let val = document.getElementById(item.id).querySelector('input').value;
                    if (val === '') val = 0;
                    let temp = {
                        id: item.id,
                        label: item.label,
                        val: val
                    };
                    values.push(temp);
                }
            }
        });

        formulas.forEach(function(item) {
            let tempFormula = '';
            let DSQType = 'standard';
            if (item.type === 'if') {
                let conditionFormula = item.formula.conditionFormula;
                let conditionVal = item.formula.conditionVal;

                conditionVal.forEach(function(con) {
                    if (con.label.endsWith("?")) {
                        let checkedRadioVal = getRadioValue(con.id);
                        conditionFormula = conditionFormula.replaceAll(con.label, checkedRadioVal);
                    } else {
                        let val = document.getElementById(con.id).querySelector('input').value;
                        if (val === '') val = 0;
                        conditionFormula = conditionFormula.replaceAll(con.label, val);
                    }
                });

                let calcResult = parser.parse(conditionFormula);
                if (calcResult.error) {
                    let errorMsg = formulaErrorMesages.find(o => o.code === calcResult.error);
                    // toastr.error(errorMsg.text);
                    someBlock.preloader('remove');
                } else {
                    if (calcResult.result) {
                        tempFormula = item.formula.trueFormula;
                        DSQType = item.formula.trueFormulaTarget;
                    } else {
                        tempFormula = item.formula.falseFormula;
                        DSQType = item.formula.falseFormulaTarget;
                    }
                }
            } else {
                tempFormula = item.formula;
            }


            values.sort((a, b) => (b.label.length - a.label.length));

            values.forEach(function(e) {
                tempFormula = tempFormula.replaceAll(e.label, e.val);
            });
            let calcResult = parser.parse(tempFormula);
            if (calcResult.error) {
                errorFlag = true;
                let errorMsg = formulaErrorMesages.find(o => o.code === calcResult.error);
                toastr.error(errorMsg.text);
                someBlock.preloader('remove');
            } else {
                if (calcResult.result !== 0) {
                    data.push({
                        costItemId: item.costItemId,
                        DSQType: DSQType,
                        val: calcResult.result
                    });
                }
            }
        });

        if (!errorFlag) {
            let assemblyItemId = $('#ss_assembly_item_id').val();
            localStorage.removeItem('globalTOQ'); // remove globalTOQ after save
            addAssemblyByInterview(assemblyItemId, data);
        }

    }

    function addAssemblyByInterview(assemblyItemId, TOQ) {
        if (assemblyItemId && TOQ && TOQ.length) {
            let interviewId = localStorage.getItem('interviewId');
            let interviewLocation = $("#ss_interview_assem_location").val();
            let data = {
                _token: _token,
                item_id: assemblyItemId,
                project_id: projectId,
                interviewId: interviewId,
                TOQ: TOQ,
                sort_status: sortStatus
            };
            if (interviewLocation) {
                data['interviewLocation'] = interviewLocation;
            }

            $.ajax({
                url: "{{ url('estimate/add_assembly_to_ss_interview') }}",
                method: "POST",
                data: data,
                success: function(res) {
                    someBlock.preloader('remove');
                    // document.getElementById('assembly_formula_body').innerHTML = '';
                    // $('#assembly_formula_modal').modal('hide');
                    // localStorage.removeItem('interviewId');

                    SSData = res.data.ss_data;
                    priceComments = res.data.price_comments;
                    $("#ss_total_bar").html(res.data.total);

                    spreadsheet.options.data = SSData;
                    spreadsheet.refresh();

                    updateOverLine();
                    hideMarkupCols();
                    updateHiddenCols();
                    setComment(priceComments);
                    updateSortStyle();
                }
            });
        } else {
            someBlock.preloader('remove');
            console.log('all calc result is zero...');
            // toastr.error('Unknown error');
        }
    }


    // add assembly items to spreadsheet
    $('#assembly_list').on("select_node.jstree", function(e, data) {
        if (data.node.type === 'child') {
            e.preventDefault();
            localStorage.removeItem('globalTOQ'); // remove globalTOQ
            localStorage.removeItem('interviewId'); // remove interviewId
            let itemId = data.node.id;
            let isTurnOnInterview = $('.turn-on-interview').data('checked');
            if (isTurnOnInterview) {
                globalAssemblyNormalVal = [];
                globalAssemblyFormula = [];
                globalAssemblyVal = [];
                let itemText = data.node.text;
                someBlock.preloader();
                localStorage.setItem('interviewId', itemId); // assembly id to check qv
                getFormulaAssembly(itemId, itemText);
            } else {
                addAssemblyItemToSS(itemId, projectId);
            }

        }
    });

    // calculate condition formula to show/hide true/false form
    $(document).on('keyup change', '#assembly_formula_body input', function() {
        if ($(this).val()) {
            refreshAssemblyModal();
        }
    });

    // show/hide measure icon of assembly interview
    $(document).on('focus', '#assembly_formula_body input', function() {
        lastValid = '';
        let focusedInputId = $(this).data('id');
        let measureToolId = "#measure_" + focusedInputId;
        $(measureToolId).fadeIn();
    });
    $(document).on('focusout', '#assembly_formula_body input', function() {
        let focusedInputId = $(this).data('id');
        let measureToolId = "#measure_" + focusedInputId;
        $(measureToolId).fadeOut();
    });

    // add assembly items to spreadsheet by interview
    $('#save_assembly_interview').click(function(e) {
        e.preventDefault();
        someBlock.preloader();
        let interviewTOQ = JSON.parse(localStorage.getItem('globalTOQ'));
        if (interviewTOQ) {
            globalAssemblyVal = interviewTOQ.globalAssemblyVal;
            globalAssemblyFormula = interviewTOQ.globalAssemblyFormula;
            globalAssemblyNormalVal = interviewTOQ.globalAssemblyNormalVal;
        }
        calcAssemblyFormula();
    });

    // get measure by assembly interview

    $(document).on('click', '.get_measuring_by_assembly', function() {
        let measureId = $(this).data('id');
        if (measureId) {
            let interviewTOQ = JSON.parse(localStorage.getItem('globalTOQ'));
            let originURL = window.location.href;
            if (interviewTOQ) {
                interviewTOQ['measureId'] = measureId;
                interviewTOQ['values'] = [];
                globalAssemblyVal.forEach(item => {
                    let temp_val;
                    if (item.label.endsWith("?")) {
                        temp_val = getRadioValue(item.id);
                    } else {
                        temp_val = document.getElementById(item.id).querySelector('input').value;
                    }
                    interviewTOQ['values'].push({
                        id: item.id,
                        label: item.label,
                        value: temp_val
                    });
                });
                localStorage.setItem('globalTOQ', JSON.stringify(interviewTOQ));
            } else {
                globalTOQ['measureId'] = measureId;
                globalTOQ['modalId'] = "assembly_formula_modal";
                globalTOQ['redirect_url'] = originURL;
                globalTOQ['formulaModalHTMLAssembly'] = formulaModalHTMLAssembly;
                globalTOQ['globalAssemblyVal'] = globalAssemblyVal;
                globalTOQ['globalAssemblyFormula'] = globalAssemblyFormula;
                globalTOQ['globalAssemblyNormalVal'] = globalAssemblyNormalVal;
                globalTOQ['values'] = [];
                globalAssemblyVal.forEach(item => {
                    let temp_val;
                    if (item.label.endsWith("?")) {
                        temp_val = getRadioValue(item.id);
                    } else {
                        temp_val = document.getElementById(item.id).querySelector('input').value;
                    }
                    globalTOQ['values'].push({
                        id: item.id,
                        label: item.label,
                        value: temp_val
                    });
                });
                localStorage.setItem('globalTOQ', JSON.stringify(globalTOQ));
            }
            window.location.href = "{{ url('documents/' . $page_info['project_id']) }}";
        } else {
            console.error('Invalid id');
        }
    });


    $(document).ready(function() {
        SSData = @json($ss_data);
        sheet.data = SSData;
        spreadsheet = jexcel(document.getElementById('spreadsheet'), sheet);

        let savedSetting = @json($ss_setting);
        if (savedSetting) {
            tableSetting = JSON.parse(savedSetting);
            if (!tableSetting.hiddenCols)
                tableSetting['hiddenCols'] = [];
            // if (tableSetting.hiddenCols.length)
            //     updateHiddenCols();
            if (Object.keys(tableSetting.colWidth).length)
                updateCellWidthHeight();
        }
        hideMarkupCols();
        updateHiddenCols();

        let interviewTOQ = JSON.parse(localStorage.getItem('globalTOQ'));
        console.log('interviewTOQ', interviewTOQ);
        if (interviewTOQ) {
            $('.turn-on-interview').data('checked', true);
            let modalId = "#" + interviewTOQ.modalId;
            let values = interviewTOQ.values;

            if (interviewTOQ.modalId === "assembly_formula_modal") {
                formulaModalHTMLAssembly = interviewTOQ.formulaModalHTMLAssembly;
                globalAssemblyVal = interviewTOQ.globalAssemblyVal;
                globalAssemblyFormula = interviewTOQ.globalAssemblyFormula;
                globalAssemblyNormalVal = interviewTOQ.globalAssemblyNormalVal;
                $(modalId).modal('show');
                document.getElementById('assembly_formula_body').innerHTML = formulaModalHTMLAssembly
                    .assembly_formula_body;
                document.getElementById('assembly_interview_modal_title').innerHTML = formulaModalHTMLAssembly
                    .itemTxt;
                document.getElementById('ss_assembly_item_id').value = formulaModalHTMLAssembly
                    .ss_assembly_item_id;
                values.forEach(item => {
                    if (item.label.endsWith("?")) {
                        let tempRadioId = "#" + item.value + "_" + item.id;
                        $(tempRadioId).prop('checked', true);
                    } else {
                        document.getElementById(item.id).querySelector('input').value = item.value;
                    }
                });

                refreshAssemblyModal();

                $('.assembly-takeoff-btn').addClass('btn-success');
                $('.assembly-takeoff-btn').removeClass('btn-outline-secondary');

                $('.item-takeoff-btn').removeClass('btn-success');
                $('.item-takeoff-btn').addClass('btn-outline-secondary');

                $('#costitem_list_panel').hide();
                $('#assembly_list_panel').show();
            } else {
                formulaModalHTML = interviewTOQ.formulaModalHTML;
                globalItemFormula = interviewTOQ.globalItemFormula;
                globalItemVal = interviewTOQ.globalItemVal;
                globalItemNormalVal = interviewTOQ.globalItemNormalVal;

                $(modalId).modal('show');
                document.getElementById('formula_body').innerHTML = formulaModalHTML.formula_body;
                document.getElementById('parsed_formula_div').innerHTML = formulaModalHTML.parsed_formula_div;
                document.getElementById('item_interview_modal_title').innerHTML = formulaModalHTML.itemTxt;
                document.getElementById('ss_item_id').value = formulaModalHTML.ss_item_id;

                values.forEach(item => {
                    if (item.label.endsWith("?")) {
                        let tempRadioId = "#" + item.value + "_" + item.id;
                        $(tempRadioId).prop('checked', true);
                    } else {
                        document.getElementById(item.id).querySelector('input').value = item.value;
                    }
                });

                refreshModal();

                $('.assembly-takeoff-btn').removeClass('btn-success');
                $('.assembly-takeoff-btn').addClass('btn-outline-secondary');

                $('.item-takeoff-btn').addClass('btn-success');
                $('.item-takeoff-btn').removeClass('btn-outline-secondary');

                $('#assembly_list_panel').hide();
                $('#costitem_list_panel').show();
            }

        }

        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if (session('info'))
            toastr.info("{{ session('error') }}");
        @endif

        @if (session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    });
</script>
