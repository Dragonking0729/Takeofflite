<script>
    var parser = new formulaParser.Parser();
    var formulaErrorMesages = [
        {code: '#ERROR!', text: 'General error'},
        {code: '#DIV/0!', text: 'Divide by zero error'},
        {code: '#NAME?', text: 'Not recognised function name or variable name'},
        {code: '#N/A', text: 'Indicates that a value is not available to a formula'},
        {code: '#NUM!', text: 'Invalid number'},
        {code: '#VALUE!', text: 'One of formula arguments is of the wrong type'},
    ];

    // global variables for item formula
    var globalItemFormula = null;
    var globalItemVal = [];
    var globalItemNormalVal = [];

    var lastValid = '';

    // only allow input number
    function validateNumber(elem) {
        let validNumber = new RegExp(/^\d*\.?\d*$/);
        if (validNumber.test(elem.value)) {
            lastValid = elem.value;
        } else {
            elem.value = lastValid;
        }
    }

    // get yes or no status value
    function getRadioValue(id) {
        let radios = document.getElementsByName(id);
        let checkedRadioVal = 2; // neither is selected
        for (let i = 0; i < radios.length; i++) {
            if (radios[i].checked) {
                checkedRadioVal = radios[i].value;
                break;
            }
        }
        return checkedRadioVal; // '1' or '0' or '2'
    }

    function updateShowHideArray(falseArray, trueArray, toBeHiddenVal, toBeShownVal) {
        if (falseArray.length) {
            let isExist = false;
            falseArray.forEach(e => {
                isExist = toBeHiddenVal.some(el => el.id === e.id);
                if (!isExist) {
                    toBeHiddenVal.push(e);
                }
            });
        }
        if (trueArray.length) {
            let isExist = false;
            trueArray.forEach(e => {
                isExist = toBeShownVal.some(el => el.id === e.id);
                if (!isExist) {
                    toBeShownVal.push(e);
                }
            });
        }
    }

    // parse normal item formula
    function parseNormalFormula(params) {
        let formula = '';
        params.forEach(function (item) {
            formula += item.val;
            if (item.type === 'variable') {
                let isDuplicated = globalItemVal.some(el => el.label === item.val);
                if (!isDuplicated) {
                    let temp_id = item.val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces, #
                    let temp = {id: temp_id, label: item.val, questionType: item.questionType, help: item.help};
                    globalItemVal.push(temp);
                }
                isDuplicated = globalItemNormalVal.some(el => el.label === item.val);
                if (!isDuplicated) {
                    let temp_id = item.val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__');
                    let temp = {id: temp_id, label: item.val, questionType: item.questionType, help: item.help};
                    globalItemNormalVal.push(temp);
                }
            }
        });
        return formula;
    }

    // parse IF item formula
    function parseIfFormula(params) {
        let conditionFormula = '', trueFormula = '', falseFormula = '';
        let conditionVal = [], trueVal = [], falseVal = [];
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
                let isDuplicated = globalItemVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        label: params[i].val,
                        questionType: params[i].questionType,
                        help: params[i].help
                    };
                    globalItemVal.push(temp);
                }
                isDuplicated = globalItemNormalVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        label: params[i].val,
                        questionType: params[i].questionType,
                        help: params[i].help
                    };
                    globalItemNormalVal.push(temp);
                }
                // get condition variables
                isDuplicated = conditionVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        label: params[i].val,
                        questionType: params[i].questionType,
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
                let isDuplicated = globalItemVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        label: params[i].val,
                        questionType: params[i].questionType,
                        help: params[i].help
                    };
                    globalItemVal.push(temp);
                }
                // get true clause variables
                isDuplicated = trueVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        label: params[i].val,
                        questionType: params[i].questionType,
                        help: params[i].help
                    };
                    trueVal.push(temp);
                }
                i++;
            }

            // get false clause
            i++;
            while (params[i].val !== ')') {
                let isDuplicated = globalItemVal.some(el => el.label === params[i].val);
                falseFormula += params[i].val;
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        label: params[i].val,
                        questionType: params[i].questionType,
                        help: params[i].help
                    };
                    globalItemVal.push(temp);
                }
                // get false clause variables
                isDuplicated = falseVal.some(el => el.label === params[i].val);
                if (!isDuplicated && params[i].type === 'variable') {
                    let temp_id = params[i].val.replace(/\s+/g, '').replaceAll(/[&\/\\#,+()$~%.'":*?<>{}]/g, '__'); // remove spaces
                    let temp = {
                        id: temp_id,
                        label: params[i].val,
                        questionType: params[i].questionType,
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
            trueVal: trueVal,
            falseFormula: falseFormula,
            falseVal: falseVal
        };
    }

    // gen formula form
    // values, formula, mode=true when interview, false when test formula
    function genFormulaForm(inputValues, inputFormula, mode) {
        let html = '';
        let $_testFormulaResult = document.getElementById('test_formula_result');
        let $_testFormulaResultDivArea = document.getElementById('test_formula_result_div_area');
        inputValues.forEach(function (item) {
            let helpPrompt = '';
            if (item.help) {
                helpPrompt = `<div class="d-flex help_question">&nbsp;${item.help}</div>`
            }
            let getMeasureIcon = '';
            if (mode) {
                getMeasureIcon = `<i class="get_measuring_by_assembly get_measuring_other" id="measure_${item.id}" data-id="${item.id}">
                                </i>`;
                                
            }
            if (item.label.endsWith("?")) {
                html += `<div class="form-group row">
                    <label for="${item.id}" class="col-sm-8 my-auto">${item.label} </label>
                    <div class="col-sm-4">
                        <div class="d-flex justify-content-around my-auto">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="${item.id}" value="1" id="true_${item.id}">
                                <label for="true_${item.id}" class="mb-0">&nbsp;&nbsp;Yes</label>
                            </div>
                            <div class="text-center">
                                <input type="radio" name="${item.id}" value="0" id="false_${item.id}">
                                <label for="false_${item.id}" class="mb-0">&nbsp;&nbsp;No</label>
                            </div>
                        </div>
                    </div>
                    ${helpPrompt}
                </div>`;
            } else {
                if (item.questionType === 'total') {
                    html += `<div class="form-group row" id="${item.id}">
                        <label class="col-sm-8 my-auto">${item.label}</label>
                        <div class="col-sm-4">
                            <div class="d-flex input-group my-auto">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" id="${item.id}__total" data-id="${item.id}" data-questionType="${item.questionType}" oninput="validateNumber(this);">
                            </div>
                        </div>
                        ${helpPrompt}
                    </div>`;
                    $_testFormulaResult !== null ? $_testFormulaResult.style.display = 'none' : '';
                    $_testFormulaResultDivArea !== null ? $_testFormulaResultDivArea.style.display = 'none' : '';
                } else if (item.questionType === 'category') {
                    html += `<div class="form-group row" id="${item.id}">
                        <label class="col-sm-8 my-auto">Lab ${item.label}</label>
                        <div class="col-sm-4 mb-2">
                            <div class="d-flex input-group my-auto">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" id="${item.id}__category_lab" data-id="${item.id}" data-questionType="${item.questionType}" oninput="validateNumber(this);">
                            </div>
                        </div>
                        <label class="col-sm-8 my-auto">Mat ${item.label}</label>
                        <div class="col-sm-4 mb-2">
                            <div class="d-flex input-group my-auto">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" id="${item.id}__category_mat" data-id="${item.id}" data-questionType="${item.questionType}" oninput="validateNumber(this);">
                            </div>
                        </div>
                        ${helpPrompt}
                    </div>`;
                    $_testFormulaResult !== null ? $_testFormulaResult.style.display = 'none' : '';
                    $_testFormulaResultDivArea !== null ? $_testFormulaResultDivArea.style.display = 'none' : '';
                } else if (item.questionType === 'tricky') {
                    html += `<div class="form-group row" id="${item.id}">
                        <label class="col-sm-8 my-auto"># of Units&nbsp;${item.label}</label>
                        <div class="col-sm-4 mb-2">
                            <div class="d-flex my-auto">
                                <input type="text" class="form-control" id="${item.id}__tricky_of_unites" data-id="${item.id}" data-questionType="${item.questionType}" oninput="validateNumber(this);">
                            </div>
                        </div>
                        <label class="col-sm-8 my-auto">$ Per Unit&nbsp;${item.label}</label>
                        <div class="col-sm-4 mb-2">
                            <div class="d-flex my-auto">
                                <input type="text" class="form-control" id="${item.id}__tricky_per_unit" data-id="${item.id}" data-questionType="${item.questionType}" oninput="validateNumber(this);">
                            </div>
                        </div>
                        ${helpPrompt}
                    </div>`;
                    $_testFormulaResult !== null ? $_testFormulaResult.style.display = 'none' : '';
                    $_testFormulaResultDivArea !== null ? $_testFormulaResultDivArea.style.display = 'none' : '';
                } else {
                    html += `<div class="form-group row" id="${item.id}">
                        <label class="col-sm-8 my-auto">${item.label} </label>
                        <div class="col-sm-4">
                            <div class="d-flex my-auto">
                                <input type="text" class="form-control" data-id="${item.id}" data-questionType="${item.questionType}" oninput="validateNumber(this);">
                                ${getMeasureIcon}
                            </div>
                        </div>
                        ${helpPrompt}
                    </div>`;
                }
            }
        });

        let formula = '';
        if (inputFormula) {
            if (inputFormula.type === 'normal') {
                formula = inputFormula.formula;
            } else {
                let tempFormula = inputFormula.formula;
                formula = `IF ${tempFormula.conditionFormula}, ${tempFormula.trueFormula}, ${tempFormula.falseFormula})`;
            }
        }

        return {html: html, formula: formula};
    }

    // refresh test formula form
    function refreshModal() {
        let toBeHiddenVal = [];
        let toBeShownVal = [];
        someBlock.preloader();
        if (globalItemFormula.type === 'if') {
            let conditionFormula = globalItemFormula.formula.conditionFormula;
            let conditionVal = globalItemFormula.formula.conditionVal;

            conditionVal.forEach(function (item) {
                if (item.label.endsWith("?")) {
                    let checkedRadioVal = getRadioValue(item.id);
                    if (checkedRadioVal === 2) { // hide all true/false val
                        updateShowHideArray(globalItemFormula.formula.falseVal, [], toBeHiddenVal, toBeShownVal);
                        updateShowHideArray(globalItemFormula.formula.trueVal, [], toBeHiddenVal, toBeShownVal);
                    } else {
                        conditionFormula = conditionFormula.replaceAll(item.label, checkedRadioVal);
                    }
                } else {
                    let val = document.getElementById(item.id).querySelector('input').value;
                    if (val === '') val = 0;
                    conditionFormula = conditionFormula.replaceAll(item.label, val);
                }
            });
            let calcResult = parser.parse(conditionFormula);
            if (calcResult.error) {
                let errorMsg = formulaErrorMesages.find(o => o.code === calcResult.error);
                // toastr.error(errorMsg.text);
                someBlock.preloader('remove');
            } else {
                if (calcResult.result) {
                    updateShowHideArray(globalItemFormula.formula.falseVal, globalItemFormula.formula.trueVal, toBeHiddenVal, toBeShownVal);
                } else {
                    updateShowHideArray(globalItemFormula.formula.trueVal, globalItemFormula.formula.falseVal, toBeHiddenVal, toBeShownVal);
                }
            }
        }


        toBeHiddenVal.forEach(e => {
            let isDuplicated = globalItemNormalVal.some(el => el.id === e.id);
            if (!isDuplicated) {
                document.getElementById(e.id).style.display = 'none';
            }
        });
        toBeShownVal.forEach(e => {
            document.getElementById(e.id).style.display = 'flex';
        });

        $('#formula_modal').modal({
            backdrop: 'static',
            keyboard: false
        });
        someBlock.preloader('remove');
    }

    // calculate item formula
    function calcItemFormula() {
        someBlock.preloader();
        let values = [], formula = null, calcResult = null;
        for (let ii = 0; ii < globalItemVal.length; ii++) {
            let item = globalItemVal[ii], val = '';
            if (item.label.endsWith("?")) {
                val = getRadioValue(item.id);
            } else {
                if (item.questionType === 'standard') {
                    val = document.getElementById(item.id).querySelector('input').value;
                } else if (item.questionType === 'total') {
                    let val = document.getElementById(item.id + '__total').value;
                    if (val === '') val = 0;
                    return {
                        error: false,
                        result: {
                            DSQType: 'total',
                            val: val
                        }
                    };
                    // break;
                } else if (item.questionType === 'category') {
                    let categoryLab = document.getElementById(item.id + '__category_lab').value;
                    let categoryMat = document.getElementById(item.id + '__category_mat').value;
                    if (categoryLab === '') categoryLab = 0;
                    if (categoryMat === '') categoryMat = 0;
                    return {
                        error: false,
                        result: {
                            DSQType: 'category',
                            val: {
                                categoryLab: categoryLab,
                                categoryMat: categoryMat
                            }
                        }
                    };
                    // break;
                } else if (item.questionType === 'tricky') {
                    let trickyOfUnites = document.getElementById(item.id + '__tricky_of_unites').value;
                    let trickyPerUnit = document.getElementById(item.id + '__tricky_per_unit').value;
                    if (trickyOfUnites === '') trickyOfUnites = 0;
                    if (trickyPerUnit === '') trickyPerUnit = 0;
                    return calcResult = {
                        error: false,
                        result: {
                            DSQType: 'tricky',
                            val: {
                                trickyOfUnites: trickyOfUnites,
                                trickyPerUnit: trickyPerUnit
                            }
                        }
                    };
                    // break;
                }
            }
            if (val === '') val = 0;
            let temp = {id: item.id, label: item.label, val: val};
            values.push(temp);
        }
        // get formula
        if (globalItemFormula.type === 'normal') {
            formula = globalItemFormula.formula;
        } else {
            let conditionFormula = globalItemFormula.formula.conditionFormula;
            let conditionVal = globalItemFormula.formula.conditionVal;
            conditionVal.forEach(function (item) {
                if (item.label.endsWith("?")) {
                    let checkedRadioVal = getRadioValue(item.id);
                    conditionFormula = conditionFormula.replaceAll(item.label, checkedRadioVal);
                } else {
                    let val = document.getElementById(item.id).querySelector('input').value;
                    if (val === '') val = 0;
                    conditionFormula = conditionFormula.replaceAll(item.label, val);
                }
            });
            let calcResult = parser.parse(conditionFormula);
            if (calcResult.error) {
                let errorMsg = formulaErrorMesages.find(o => o.code === calcResult.error);
                toastr.error(errorMsg.text);
                someBlock.preloader('remove');
            } else {
                if (calcResult.result) {
                    formula = globalItemFormula.formula.trueFormula;
                } else {
                    formula = globalItemFormula.formula.falseFormula;
                }
            }
        }
        // replace variables with values
        values.forEach(function (item) {
            formula = formula.replaceAll(item.label, item.val);
        });
        calcResult = parser.parse(formula);
        if (calcResult.error) {
            let errorMsg = formulaErrorMesages.find(o => o.code === calcResult.error);
            toastr.error(errorMsg.text);
            someBlock.preloader('remove');
        }
        return {
            error: calcResult.error,
            result: {
                DSQType: 'standard',
                val: calcResult.result
            }
        };
    }

</script>