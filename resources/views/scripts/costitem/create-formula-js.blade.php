<script>
    {{-- Adding new formula--}}

    const addTagContainer = document.querySelector('.add-tag-container');

    var newTags = [];

    function removeOldCursorAdd() {
        document.querySelector('.new-input input').parentNode.classList.remove('add-current');
        document.querySelector('.new-input input').remove();
    }
    function addNewCursorAdd(index) {
        let newCursorInput = document.querySelectorAll('.new-input');
        newCursorInput[index].innerHTML = "<input type='text' size='1' autofocus />";
        newCursorInput[index].classList.add('add-current');
        $(".add-current input").focus();
    }
    function moveCursor2LeftAdd() {
        let index = parseInt(document.querySelector('.new-input input').parentNode.getAttribute('data-item'));
        if (index !== 0) {
            removeOldCursorAdd();
            addNewCursorAdd(index-1);
        }
    }
    function moveCursor2RightAdd() {
        let index = parseInt(document.querySelector('.new-input input').parentNode.getAttribute('data-item'));
        if (index < newTags.length) {
            removeOldCursorAdd();
            addNewCursorAdd(index+1);
        }
    }
    // move cursor by arrow key
    function removeByBackAdd() {
        let index = parseInt(document.querySelector('.new-input input').parentNode.getAttribute('data-item'));
        if (index !== 0) {
            newTags.splice(index-1, 1);
            addNewTags(index-1);
        }
    }
    function removeByDeleteAdd() {
        let index = parseInt(document.querySelector('.new-input input').parentNode.getAttribute('data-item'));
        if (index < newTags.length) {
            newTags.splice(index, 1);
            addNewTags(index);
        }
    }
    // get input cursor position
    function getCurrentCorsurPosAdd() {
        return parseInt(document.querySelector('.add-current').getAttribute('data-item'));
    }
    function createNewTag(obj, index) {
        const div = document.createElement('div');
        div.setAttribute('class', 'new '+obj.type);
        div.setAttribute('data-item', index);
        div.innerHTML = obj.val;
        return div;
    }
    function createSpanAdd(index) {
        const span = document.createElement('span');
        span.setAttribute('class', 'new-input');
        span.setAttribute('data-item', index);
        return span;
    }
    function resetAdding() {
        document.querySelectorAll('.new-input').forEach(function(span) {
            span.parentElement.removeChild(span);
        });
        document.querySelectorAll('.new').forEach(function(tag) {
            tag.parentElement.removeChild(tag);
        });
    }
    function addNewTags(currentCursorPos) {
        resetAdding();
        console.log('newTags...', newTags);
        newTags.slice().reverse().forEach(function(tag, index) {
            let i = newTags.length-index-1;
            const input = createNewTag(tag, i);
            const span = createSpanAdd(i);
            addTagContainer.prepend(input);
            addTagContainer.prepend(span);
        });
        const span = createSpanAdd(newTags.length);
        addTagContainer.append(span);

        document.querySelectorAll('.new-input')[currentCursorPos].innerHTML = "<input type='text' size='1' autofocus />";
        document.querySelectorAll('.new-input')[currentCursorPos].classList.add('add-current');
        $(".add-current input").focus();
    }


    $(document).on('select2:opening', '#add_variables', function (e) {
        $('#add_variables').val(null);
    });
    $(document).on('select2:select', '#add_variables', function() {
        inputConstant();
        let selected = $(this).children("option:selected").text();
        let questionType = $(this).children("option:selected").data('question_type');
        let helpNote = $(this).children("option:selected").data('note');

        let cursorPos = getCurrentCorsurPosAdd();
        let variable = {type: 'variable', questionType: questionType, val: selected.trim(), help: helpNote};
        newTags.splice(cursorPos, 0, variable);
        addNewTags(cursorPos+1);

        if (questionType !== 'standard') {
            if (questionType === 'category') {
                swal("This type of question requires that labor and material types be turned on", "", "info");
            } else {
                swal("This question requires that the subcontractor cost type is on", "", "info");
            }
        }
    });
    $(document).on('click', '.add_operators', function() {
        inputConstant();
        let cursorPos = getCurrentCorsurPosAdd();
        let selected = $(this).data('value');
        let operator = {type: 'operator', val: selected.trim()};
        newTags.splice(cursorPos, 0, operator);
        addNewTags(cursorPos+1);
        updateBtnClicked();
    });
    $(document).on('select2:opening', '#add_functions', function (e) {
        $('#add_functions').val(null);
    });
    $(document).on('select2:select', '#add_functions', function (e) {
        inputConstant();
        let selected = $(this).children("option:selected").text();

        let cursorPos = getCurrentCorsurPosAdd();
        let func = {type: 'function', val: selected.trim()};
        newTags.splice(cursorPos, 0, func);
        addNewTags(cursorPos + 1);
    });
    $(document).on('select2:opening', '#pre_defined_calc', function (e) {
        $('#pre_defined_calc').val(null);
    });
    $(document).on('select2:opening', '#add_pre_defined_calc', function (e) {
        $('#add_pre_defined_calc').val(null);
    });
    $(document).on('select2:select', '#add_pre_defined_calc', function() {
        inputConstant();

        let stored_formula = $(this).children("option:selected").data('formula_body');
        let openBracket = {type: 'operator', val: '('};
        let closeBracket = {type: 'operator', val: ')'};
        newTags.push(openBracket);
        for (let i = 0; i < stored_formula.length; i++) {
            newTags.push(stored_formula[i]);
        }
        newTags.push(closeBracket);

        addNewTags(newTags.length);
    });
    // left, right arrow, backspace, delete
    $(document).on('keyup', '.add-tag-container', function(e) {
        let keyNum;
        if (window.event) { // IE
            keyNum = e.keyCode;
        } else if(e.which) {
            keyNum = e.which;
        }
        // 37 ==> left arrow, 39 ==> right arrow, 8 ==> backspace, 46 ==> delete
        switch (keyNum) {
            case 37:
                moveCursor2LeftAdd();
                break;
            case 39:
                moveCursor2RightAdd();
                break;
            case 8:
                removeByBackAdd();
                break;
            case 46:
                removeByDeleteAdd();
                break;
            default:
                break;
        }
    });
    // remove tag
    $(document).on('click', '.add-remove-tags', function() {
        let selectedTags = document.querySelectorAll('.add-selected');
        if (selectedTags.length) {
            let removeVal = [];
            selectedTags.forEach(function(item) {
                let index = item.getAttribute('data-item');
                removeVal.push(index);
            });
            for (let i = removeVal.length - 1; i >= 0; i--) {
                newTags.splice(removeVal[i], 1);
                document.querySelectorAll('.new-input').forEach(function(span) {
                    let spanId = span.getAttribute('data-item');
                    if (spanId === removeVal[i]) {
                        span.parentElement.removeChild(span);
                    }
                });
            }
            addNewTags(newTags.length);
        }
    });

    // clear assembly item formula
    $(document).on('click', '#clear_formula_add', function () {
        newTags = [];
        addNewTags(newTags.length);
    });

</script>