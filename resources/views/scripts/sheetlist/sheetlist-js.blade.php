<script>

    var _token = $("input[name=_token]").val();
    var editableStatus = false;
    var sheetNameInput = null;
    var someBlock = $('.someBlock');

    function updateSheetName(sheet_name, sheet_id) {
        editableStatus = false;
        sheetNameInput = null;
        // we could send an ajax request to update the field
        $.ajax({
            url: "{{ url('documents/update_sheet_name') }}",
            method: "POST",
            data: {
                _token: _token,
                sheet_id: sheet_id,
                sheet_name: sheet_name
            },
            success: function (data) {
                toastr.success(data.message);
                // update sheet name on the tab
                let sheetTabId = 'sheet_tab_' + sheet_id;
                let sheetTabNameEle = document.getElementById(sheetTabId);
                if (sheetTabNameEle) {
                    sheetTabNameEle.innerHTML = sheet_name;
                }
                // update sheet name on the left sidebar
                let sheetNameIdInSidebar = "sheet_group_txt_sheet_" + sheet_id;
                let sheetNameInSidebarEle = document.getElementById(sheetNameIdInSidebar);
                if (sheetNameInSidebarEle) {
                    sheetNameInSidebarEle.innerHTML = sheet_name;
                }
            }
        });
    }


    document.addEventListener('keydown', function (event) {
        var esc = event.which == 27,
            nl = event.which == 13,
            el = event.target,
            input = el.nodeName != 'INPUT' && el.nodeName != 'TEXTAREA';

        if (input) {
            if (el.nodeName === 'DIV') {
                sheetNameInput = el;
                editableStatus = true;
            }

            if (esc) {
                // restore state
                editableStatus = false;
                sheetNameInput = null;
                document.execCommand('undo');
                el.blur();
            } else if (nl) {
                // save
                let sheetName = el.innerHTML;
                let sheetId = el.getAttribute('data-id');
                updateSheetName(sheetName, sheetId);
                el.blur();
                event.preventDefault();
            }
        }
    }, true);

    $('.sheet_name_edit').on('click', function () {
        // $( "#sortable" ).sortable( "destroy" );
        if (editableStatus && sheetNameInput) {
            let sheetName = sheetNameInput.innerHTML;
            let sheetId = sheetNameInput.getAttribute('data-id');
            updateSheetName(sheetName, sheetId);
        }
    });

    // form submit
    function post(path, params, method = 'post') {
        const form = document.createElement('form');
        form.method = method;
        form.action = path;

        for (const key in params) {
            if (params.hasOwnProperty(key)) {
                const hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = key;
                hiddenField.value = params[key];
                form.appendChild(hiddenField);
            }
        }

        if (method == 'post') {
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = '_token';
            hiddenField.value = _token;
            form.appendChild(hiddenField);
        }

        document.body.appendChild(form);
        form.submit();
        form.remove();
    }


    // delete sheet
    $('.delete_sheet').on('click', function () {
        var id = $(this).data('id');
        var data = {
            sheet_id: id
        };
        var url = "{{ url('documents/remove_sheet') }}";
        someBlock.preloader();
        post(url, data);
    });


    // sortable sheet
    $(".image").on('mousedown', function () {
        $("#sortable").sortable({
            update: function (event, ui) {
                let order = $("#sortable").sortable("toArray", {
                    attribute: "data-order"
                });
                console.log(order);
                $.ajax({
                    url: "{{ url('documents/update_sheet_order') }}",
                    method: "POST",
                    data: {
                        _token: _token,
                        order: order
                    },
                    success: function (data) {
                        console.log(data);
                    }
                });
            }
        });
//        $( "#sortable" ).disableSelection();
    });


    // check sheet image
    var checkedSheetIds = [];
    $(document).on('click', '.check_sheet', function () {
        let sheetId = $(this).data('sheet_id');

        if ($(this).is(':checked')) {
            checkedSheetIds.push(sheetId);
        } else {
            let index = checkedSheetIds.indexOf(sheetId);
            checkedSheetIds.splice(index, 1);
        }
        console.log(sheetId, checkedSheetIds);
        if (checkedSheetIds.length) {
            $('.remove-multiple-sheets').removeClass('disable');
        } else {
            $('.remove-multiple-sheets').addClass('disable');
        }
    });

    // remove multiple sheets
    $(document).on('click', '.remove-multiple-sheets', function () {
        if (checkedSheetIds.length) {
            someBlock.preloader();
            $.ajax({
                url: "{{ url('documents/remove_multiple_sheets') }}",
                method: "POST",
                data: {
                    _token: _token,
                    ids: checkedSheetIds
                },
                success: function (res) {
                    console.log(res);
                    $('#sortable input:checked').each(function() {
                        $(this).parent().remove();
                    });
                    $('.remove-multiple-sheets').addClass('disable');
                    checkedSheetIds = [];
                    someBlock.preloader('remove');
                    toastr.success(res.message);
                },
                error: function() {
                    someBlock.preloader('remove');
                    checkedSheetIds = [];
                    console.log('server error while removing multiple sheets');
                }
            });
        }
    });


    @if(session('success'))
    toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
    toastr.error("{{ session('error') }}");
    @endif
</script>