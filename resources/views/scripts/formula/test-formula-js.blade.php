<script>
    {{-- Test formula script --}}
    console.log('testing formula', tags);

    // testing formula
    $(document).on('click', '#test_formula', function () {
        inputConstant();
        if (tags.length) {
            $('#test_formula_result_div').html('');
            let parsedResult = null;
            globalItemFormula = null;
            globalItemVal = [];
            globalItemNormalVal = [];

            let tempTags = tags;
            let isIFFormula = tempTags.some(e => e.val === 'IF');
            if (tempTags[0].val === '(' && isIFFormula) {
                while(tempTags[0].val !== 'IF') {
                    tempTags.splice(0, 1);
                    tempTags.splice(tempTags.length - 1, 1);
                }
            }
            if (tempTags[0].type === 'operator' && tempTags[0].val === 'IF') {
                parsedResult = parseIfFormula(tempTags);
                globalItemFormula = {type: 'if', formula: parsedResult};
            } else {
                parsedResult = parseNormalFormula(tempTags);
                globalItemFormula = {type: 'normal', formula: parsedResult};
            }

            // generate formula form
            let formulaForm = genFormulaForm(globalItemVal, globalItemFormula, false);
            $('#parsed_formula_div').html(formulaForm.formula);
            $('#test_formula_body').html(formulaForm.html);

            refreshModal();
        } else {
            toastr.error('No exists formula');
        }
    });

    // calculate condition clause
    $("#formula_modal").on('keyup change', '#test_formula_body input', function () {
        refreshModal();
    });

    $(document).on('focus', '#test_formula_body input', function () {
        lastValid = '';
    });

    // test formula result
    $('#test_formula_result').click(function () {
        let calcResult = calcItemFormula();
        if (!calcResult.error) {
            $('#test_formula_result_div').html(calcResult.result.val);
        }
        someBlock.preloader('remove');
    });


</script>