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
            success: function(data) {
                // update sheet name on the left sidebar
                let sheetNameInSidebarEle = document.getElementById("sidebar_sheet_name");
                sheetNameInSidebarEle.innerHTML = sheet_name;
            }
        });
    }


    document.addEventListener('keydown', function (event) {
        let esc = event.which === 27,
            nl = event.which === 13,
            el = event.target,
            input = el.nodeName !== 'INPUT' && el.nodeName !== 'TEXTAREA';

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

    document.addEventListener('click', function () {
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
    $('.delete_sheet').on('click', function() {
        let id = $(this).data('id');
        let data = {
            sheet_id: id
        };
        let url = "{{ url('documents/remove_sheet') }}";
        someBlock.preloader();
        post(url, data);
    });


    @if(session('success'))
    toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
    toastr.error("{{ session('error') }}");
    @endif
</script>