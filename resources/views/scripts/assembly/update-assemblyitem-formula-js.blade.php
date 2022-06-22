<script>
    var nextAssemblyItem = "";
    var prevAssemblyItem = "";
    {{-- cost item formula update --}}
    let tagContainer = document.querySelector('.tag-container');

    let tags = [];

    function removeOldCursor() {
        document.querySelector('.cursor-input input').parentNode.classList.remove('current');
        document.querySelector('.cursor-input input').remove();
    }

    function addNewCursor(index) {
        let newCursorInput = document.querySelectorAll('.cursor-input');
        newCursorInput[index].innerHTML = "<input type='text' size='1' autofocus />";
        newCursorInput[index].classList.add('current');
        $(".current input").focus();
    }

    function moveCursor2Left() {
        let index = parseInt(document.querySelector('.cursor-input input').parentNode.getAttribute('data-item'));
        if (index !== 0) {
            removeOldCursor();
            addNewCursor(index - 1);
        }
    }

    function moveCursor2Right() {
        let index = parseInt(document.querySelector('.cursor-input input').parentNode.getAttribute('data-item'));
        if (index < tags.length) {
            removeOldCursor();
            addNewCursor(index + 1);
        }
    }

    function removeByBack() {
        let index = parseInt(document.querySelector('.cursor-input input').parentNode.getAttribute('data-item'));
        if (index !== 0) {
            tags.splice(index - 1, 1);
            addTags(index - 1);
        }
    }

    function removeByDelete() {
        let index = parseInt(document.querySelector('.cursor-input input').parentNode.getAttribute('data-item'));
        if (index < tags.length) {
            tags.splice(index, 1);
            addTags(index);
        }
    }

    function removeSelectedTags() {
        // remove from existing formula
        let selectedTags = document.querySelectorAll('.selected');
        if (selectedTags.length) {
            let removeVal = [];
            selectedTags.forEach(function(item) {
                let index = item.getAttribute('data-item');
                removeVal.push(index);
            });
            for (let i = removeVal.length - 1; i >= 0; i--) {
                tags.splice(removeVal[i], 1);
                document.querySelectorAll('.cursor-input').forEach(function(span) {
                    let spanId = span.getAttribute('data-item');
                    if (spanId === removeVal[i]) {
                        span.parentElement.removeChild(span);
                    }
                });
            }
            addTags(tags.length);
        }
        // remove from new formula
        let addSelectedTags = document.querySelectorAll('.add-selected');
        if (addSelectedTags.length) {
            let removeVal = [];
            addSelectedTags.forEach(function(item) {
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
    }

    function inputConstant() {
        let constant = document.querySelector('.current input').value;
        if (constant) {
            let cursorPos = getCurrentCursorPos();
            let val = {
                type: 'constant',
                val: constant
            };
            tags.splice(cursorPos, 0, val);
            addTags(cursorPos + 1);
        }
    }

    // get input cursor position
    function getCurrentCursorPos() {
        return parseInt(document.querySelector('.current').getAttribute('data-item'));
    }

    function createTag(obj, index) {
        const div = document.createElement('div');
        div.setAttribute('class', 'tag ' + obj.type);
        div.setAttribute('data-item', index);
        div.innerHTML = obj.val;
        return div;
    }

    function createSpan(index) {
        const span = document.createElement('span');
        span.setAttribute('class', 'cursor-input');
        span.setAttribute('data-item', index);
        return span;
    }

    function reset() {
        document.querySelectorAll('.cursor-input').forEach(function(span) {
            span.parentElement.removeChild(span);
        });
        document.querySelectorAll('.tag').forEach(function(tag) {
            tag.parentElement.removeChild(tag);
        });
        tagContainer = document.querySelector('.tag-container');
    }

    function addTags(currentCursorPos) {
        reset();
        console.log('tags...', tags);
        tags.slice().reverse().forEach(function(tag, index) {
            let i = tags.length - index - 1;
            const input = createTag(tag, i);
            const span = createSpan(i);
            tagContainer.prepend(input);
            tagContainer.prepend(span);
        });
        const span = createSpan(tags.length);
        tagContainer.append(span);

        let test = document.querySelectorAll('.cursor-input');
        document.querySelectorAll('.cursor-input')[currentCursorPos].innerHTML =
            "<input type='text' size='1' autofocus />";
        document.querySelectorAll('.cursor-input')[currentCursorPos].classList.add('current');
        $(".current input").focus();
    }

    function generateFormulaContainer(tags) {
        let html = '';
        if (tags.length) {
            tags.forEach((tag, index) => {
                html += `<span class="cursor-input" data-item="${index}"></span>
                        <div class="tag ${tag['type']}" data-item="${index}">
                            ${tag['val']}
                        </div>`;
            });
            html += `<span class="cursor-input current" data-item="${tags.length}">
                        <input type="text" size="1" autofocus/>
                    </span>`;
        } else {
            html += `<span class="cursor-input current" data-item="0">
                        <input type="text" size="1" autofocus/>
                    </span>`;
        }
        $('.tag-container').html(html);

        $('.frm_modal_assembly_meta_string').html(meta);


        $('#assembly_item_formula_modal').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    function getAssemblyItemFormula(id) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('assembly/get_assembly_item_formula') }}",
            type: 'POST',
            data: {
                _token: _token,
                assemblyId: id
            },
            success: function(res) {
                someBlock.preloader('remove');
                if (res.status === 'success') {
                    tags = res.result.formula;
                    meta = res.result.meta_data;
                    generateFormulaContainer(tags, meta);
                } else {
                    console.log('error');
                }
            }
        });
    }


    function saveAssemblyItemFormula(id, tags, pageNumber) {
        someBlock.preloader();
        $.ajax({
            url: "{{ url('assembly/save_assembly_item_formula') }}",
            type: 'POST',
            data: {
                _token: _token,
                assemblyId: id,
                formula: JSON.stringify(tags)
            },
            success: function(res) {
                someBlock.preloader('remove');
                if (res.status === 'success') {
                    tags = [];
                    toastr.success(res.message);
                    fetch_data(pageNumber);
                } else {
                    console.log('error');
                }
            }
        });
    }

    // entry point !!! open assembly item formula modal
    $(document).on('click', '.formula_modal_open', function() {
        let assemblyItemId = $(this).data('id');
        let pageNumber = $(this).data('page_number');

        nextAssemblyItem = $(this).data('next');
        prevAssemblyItem = $(this).data('previous');

        $('#save_assembly_item_formula').data('id', assemblyItemId);
        $('#save_assembly_item_formula').data('page_number', pageNumber);
        getAssemblyItemFormula(assemblyItemId);
    });


    $(document).on('click', '#next_formula_item', function() {
        var el = $(`.formula_modal_open[data-id='${nextAssemblyItem}']`);
        el.trigger('click');
    });

    $(document).on('click', '#previous_formula_item', function() {
        var el = $(`.formula_modal_open[data-id='${prevAssemblyItem}']`);
        el.trigger('click');
    });

    // create formula when add assembly item
    $(document).on('click', '.a_formula_modal_open', function() {
        let assemblyItemId = $(this).data('id');
        tags = $(this).data('formula_body');
        $('#save_assembly_item_formula').data('id', assemblyItemId);
        generateFormulaContainer(tags);
    });

    $(document).on('select2:opening', '#variables', function(e) {
        $('#variables').val(null);
    });
    $(document).on('select2:select', '#variables', function(e) {
        inputConstant();
        let selected = $(this).children("option:selected").text();
        let questionType = $(this).children("option:selected").data('question_type');
        let helpNote = $(this).children("option:selected").data('note');

        let cursorPos = getCurrentCursorPos();
        let variable = {
            type: 'variable',
            questionType: questionType,
            val: selected.trim(),
            help: helpNote
        };
        tags.splice(cursorPos, 0, variable);
        addTags(cursorPos + 1);

        if (questionType !== 'standard') {
            if (questionType === 'category') {
                swal("This type of question requires that subcontractor and material types be turned on", "",
                    "info");
            } else {
                swal("This question requires that the subcontractor cost type is on", "", "info");
            }
        }
    });
    $(document).on('click', '.operators', function() {
        inputConstant();
        let selected = $(this).data('value');
        let cursorPos = getCurrentCursorPos();
        let operator = {
            type: 'operator',
            val: selected.trim()
        };
        tags.splice(cursorPos, 0, operator);
        addTags(cursorPos + 1);
    });
    $(document).on('select2:opening', '#functions', function(e) {
        $('#functions').val(null);
    });
    $(document).on('select2:select', '#functions', function(e) {
        inputConstant();
        let selected = $(this).children("option:selected").text();

        let cursorPos = getCurrentCursorPos();
        let func = {
            type: 'function',
            val: selected.trim()
        };
        tags.splice(cursorPos, 0, func);
        addTags(cursorPos + 1);
    });
    $(document).on('select2:opening', '#pre_defined_calc', function(e) {
        $('#pre_defined_calc').val(null);
    });
    $(document).on('select2:select', '#pre_defined_calc', function() {
        inputConstant();

        let stored_formula = $(this).children("option:selected").data('formula_body');
        let openBracket = {
            type: 'operator',
            val: '('
        };
        let closeBracket = {
            type: 'operator',
            val: ')'
        };
        tags.push(openBracket);
        for (let i = 0; i < stored_formula.length; i++) {
            tags.push(stored_formula[i]);
        }
        tags.push(closeBracket);

        addTags(tags.length);
    });
    // select tag, cursor
    $(document).on('click', '#assembly_item_formula_modal', function(e) {
        let selectedClass = e.target.getAttribute('class');
        if (selectedClass) {
            if (selectedClass === 'tag variable' || selectedClass === 'tag operator' || selectedClass ===
                'tag function' || selectedClass === 'tag constant' ||
                selectedClass === 'tag variable selected' || selectedClass === 'tag operator selected' ||
                selectedClass === 'tag function selected' || selectedClass === 'tag constant selected') {
                e.target.classList.toggle('selected');
                let selectedTags = document.querySelectorAll('.selected');
                if (selectedTags.length) {
                    $('.remove-tags').addClass('text-dark');
                } else {
                    $('.remove-tags').removeClass('text-dark');
                }
            } else if (selectedClass === 'new variable' || selectedClass === 'new operator' || selectedClass ===
                'new function' || selectedClass === 'new constant' ||
                selectedClass === 'new variable add-selected' || selectedClass ===
                'new operator add-selected' || selectedClass === 'new function add-selected' ||
                selectedClass === 'new constant add-selected') {
                e.target.classList.toggle('add-selected');
                let selectedTags = document.querySelectorAll('.add-selected');
                if (selectedTags.length) {
                    $('.add-remove-tags').addClass('text-dark');
                } else {
                    $('.add-remove-tags').removeClass('text-dark');
                }
            } else if (selectedClass === 'cursor-input') {
                removeOldCursor();
                e.target.innerHTML = "<input type='text' size='1' autofocus />";
                e.target.classList.add('current');
                $(".current input").focus();
            } else if (selectedClass === 'new-input') {
                removeOldCursorAdd();
                e.target.innerHTML = "<input type='text' size='1' autofocus />";
                e.target.classList.add('add-current');
                $(".add-current input").focus();
            } else {
                let constant = document.querySelector('.current input').value;
                if (constant) {
                    inputConstant();
                }
            }
        }
    });
    // delete selected tag by delete key
    document.addEventListener('keyup', function(e) {
        let keyNum;
        if (window.event) { // IE
            keyNum = e.keyCode;
        } else if (e.which) {
            keyNum = e.which;
        }
        if (keyNum === 46) {
            removeSelectedTags();
        }
    });
    // left, right arrow, backspace, delete
    $(document).on('keyup', '.tag-container', function(e) {
        let keyNum;
        if (window.event) { // IE
            keyNum = e.keyCode;
        } else if (e.which) {
            keyNum = e.which;
        }
        // 37 ==> left arrow, 39 ==> right arrow, 8 ==> backspace, 46 ==> delete
        switch (keyNum) {
            case 37:
                moveCursor2Left();
                break;
            case 39:
                moveCursor2Right();
                break;
            case 8:
                removeByBack();
                break;
            case 46:
                removeByDelete();
                break;
            default:
                break;
        }
    });
    // remove tag
    $(document).on('click', '.remove-tags', function() {
        let selectedTags = document.querySelectorAll('.selected');
        if (selectedTags.length) {
            let removeVal = [];
            selectedTags.forEach(function(item) {
                let index = item.getAttribute('data-item');
                removeVal.push(index);
            });
            for (let i = removeVal.length - 1; i >= 0; i--) {
                tags.splice(removeVal[i], 1);
                document.querySelectorAll('.cursor-input').forEach(function(span) {
                    let spanId = span.getAttribute('data-item');
                    if (spanId === removeVal[i]) {
                        span.parentElement.removeChild(span);
                    }
                });
            }
            addTags(tags.length);
        }
    });
    // save assembly item formula
    $(document).on('click', '#save_assembly_item_formula', function() {
        let assemblyId = $(this).data('id');
        let pageNumber = $(this).data('page_number');
        if (addBtnFlag) {
            for (let i = 0; i < added_items.length; i++) {
                if (added_items[i].id === assemblyId) {
                    added_items[i].formula_body = JSON.stringify(tags);
                    document.getElementById("formula_body__" + assemblyId).setAttribute('data-formula_body',
                        JSON.stringify(tags));
                    if (tags.length) {
                        document.getElementById("formula-exist-icon__" + assemblyId).style.display = 'initial';
                    } else {
                        document.getElementById("formula-exist-icon__" + assemblyId).style.display = 'none';
                    }
                }
            }
            tags = [];
            $("#assembly_item_formula_modal").modal('hide');
        } else if (updateBtnFlag) {
            for (let i = 0; i < updated_items.length; i++) {
                if (updated_items[i].id === assemblyId) {
                    updated_items[i].formula_body = JSON.stringify(tags);
                    document.getElementById("formula_body__" + assemblyId).setAttribute('data-formula_body',
                        JSON.stringify(tags));
                    if (tags.length) {
                        document.getElementById("formula-exist-icon__" + assemblyId).style.display = 'initial';
                    } else {
                        document.getElementById("formula-exist-icon__" + assemblyId).style.display = 'none';
                    }
                }
            }
            tags = [];
            $("#assembly_item_formula_modal").modal('hide');
        } else {
            inputConstant();
            saveAssemblyItemFormula(assemblyId, tags, pageNumber);
        }

    });

    // close assembly item formula modal
    $(document).on('click', '#close_assembly_item_formula_modal', function() {
        tags = [];
    });

    // clear assembly item formula
    $(document).on('click', '#clear_formula', function() {
        tags = [];
        addTags(tags.length);
    });
</script>
