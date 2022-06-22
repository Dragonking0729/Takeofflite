<script>

    var someBlock = $('.someBlock');
    var selectedLines = [];
    var _token = $("input[name=_token]").val();

    function updateLine(id, field, val) {
        someBlock.preloader();
        $.ajax({
            url: '{{route("daily_logs.update_log_line")}}',
            method: 'POST',
            data: {
                _token: _token,
                id: id,
                field: field,
                val: val,
            },
            success: function (res) {
                someBlock.preloader('remove');
                if (res.status === 'success') {
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            }
        });
    }


    function updateAttachedFilesHtml(id, data) {
        let html = '';
        let htmlId = "#attached_files_list_" + id;
        data.forEach(function (item) {
            html += '<div class="col-md-1 pb-2 pt-2"><div>';

            let filePath = "{{asset('')}}";
            filePath += item.path.replace('/', '');

            let filePath1 = "{{asset('/icons/noun_other_3482826.png')}}";
            if (item.type === 'svg' || item.type === 'jpg' || item.type === 'jpeg' || item.type === 'png') {
                html += `<a href="${filePath}" data-fancybox="gallery" data-caption="${item.name}">
                            <img src="${filePath}" alt="${filePath}"
                                 class="img-thumbnail rounded"
                                 style="width:100px;" title="${item.name}">
                        </a>`;

            } else {
                html += `<a href="${filePath1}" data-fancybox="gallery" data-caption="${item.name}">
                            <img src="${filePath1}"
                                 class="img-thumbnail rounded"
                                 alt="${item.name}" style="width:100px;"
                                 title="${item.name}">
                        </a>`;
            }
            html += `</div>
                        <div class="d-flex">
                            <div class="attach_file_name">${item.name}</div>
                            <div>
                                <a href="${filePath}" download>
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>`;
        });
        $(htmlId).html(html);
        $('.someBlock').preloader('remove');
    }


    // open create new daily logs modal
    $('.create_new_daily_logs').click(function () {
        let d = new Date($.now());
        let currentDateTime = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate() + " " + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
        $("#new_log_entry_date").val(currentDateTime);
    });


    // check all line
    $('.select_all').on('click', function () {
        let isChecked = $(this).data('checked');
        selectedLines = [];
        if (isChecked) {
            $(this).data('checked', 0);
            $('.select_daily_log_item').each(function () {
                $(this).data('checked', 0);
                $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
            });
            $('.remove_daily_logs').addClass('disabled');
            $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
        } else {
            $(this).data('checked', 1);
            $('.select_daily_log_item').each(function () {
                let lineId = $(this).data('id');
                selectedLines.push(lineId);
                $(this).data('checked', 1);
                $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            });
            $('.remove_daily_logs').removeClass('disabled');
            $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
        }
    });


    // select single line
    $(document).on('click', '.select_daily_log_item', function () {
        $('.remove_daily_logs').removeClass('disabled');
        let isChecked = $(this).data('checked');
        if (isChecked) {
            $(this).data('checked', 0);
            let lineId = $(this).data('id');
            let index = selectedLines.indexOf(lineId);
            if (index > -1) {
                selectedLines.splice(index, 1);
            }
            $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
            $('.select_all').css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
        } else {
            $(this).data('checked', 1);
            let lineId = $(this).data('id');
            if (!selectedLines.includes(lineId)) {
                selectedLines.push(lineId);
            }
            $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            if ($('.select_daily_log_item').length === selectedLines.length) {
                $('.select_all').css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            }
        }
        // enable/disable multiple delete proposal button
        let deletable = false;
        $('.select_daily_log_item').each(function () {
            if ($(this).data('checked')) {
                deletable = true;
            }
        });
        if (deletable) {
            $('.remove_daily_logs').removeClass('disabled');
        } else {
            $('.remove_daily_logs').addClass('disabled');
        }
    });


    // update customer can view
    $(document).on('click', '.customer_can_view', function () {
        let isChecked = $(this).data('checked');
        if (isChecked) {
            $(this).data('checked', 0);
            let id = $(this).data('id');
            $(this).css('background', 'url("/icons/uncheck.svg") no-repeat center center / 20px 20px');
            updateLine(id, 'customer_view', 0);
        } else {
            $(this).data('checked', 1);
            let id = $(this).data('id');
            $(this).css('background', 'url("/icons/check.svg") no-repeat center center / 20px 20px');
            updateLine(id, 'customer_view', 1);
        }

    });


    // attach more files
    $(document).on('click', '.daily_log_attach', function () {
        $('#attach_more_files').trigger('click');
    });
    $('#attach_more_files').on('change', function () {
        $('#attach_more_files_form').submit();
    });
    $('#attach_more_files_form').submit(function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let formData = new FormData(this);
        $('.someBlock').preloader();
        $.ajax({
            type: 'POST',
            url: "{{route('daily_logs.attach_more_files')}}",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: (res) => {
                this.reset();
                console.log(res);
                updateAttachedFilesHtml(id, res.data);
            },
            // error: function(data){
            //     alert(data.responseJSON.errors.files[0]);
            //     console.log(data.responseJSON.errors);
            // }
        });
    });


    // update note or entry date
    $(document).on('change', '.note, .log_entry_date_picker', function () {
        let id = $(this).data('id');
        let field = $(this).data('field');
        let val = $(this).val();
        updateLine(id, field, val);
    });


    // remove checked lines
    $('.remove_daily_logs').click(function () {
        if (selectedLines.length) {
            swal({
                title: 'Are you sure you want to delete?',
                text: "If you delete this, it will be gone forever.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        someBlock.preloader();
                        $.ajax({
                            url: '{{route("daily_logs.remove_logs")}}',
                            method: 'POST',
                            data: {
                                _token: _token,
                                selectedLines: selectedLines,
                            },
                            success: function (res) {
                                if (res.status === 'success') {
                                    toastr.success(res.message);
                                    window.location.reload();
                                } else {
                                    toastr.error(res.message);
                                }
                            }
                        });
                    }
                });

        }

    });


    // expand/collapse detail
    $(document).on('click', '.daily_log_detail_open_close', function () {
        let isOpen = $(this).data('open');
        if (isOpen) {
            $(this).data('open', 0);
            $(this).css('background', 'url("/icons/expand_bottom.svg") no-repeat 0 0 / 25px 25px');
            $(this).parent().parent().find('.daily_log_item_right_bottom').hide();
        } else {
            $(this).data('open', 1);
            $(this).css('background', 'url("/icons/expand_top.svg") no-repeat 0 0 / 25px 25px');
            $(this).parent().parent().find('.daily_log_item_right_bottom').show();
        }
    });


    $(document).ready(function () {
        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
        toastr.error("{{ session('error') }}");
        @endif
    });


</script>