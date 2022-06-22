<script>
    var _token = $("input[name=_token]").val();
    var someBlock = $('.someBlock');

    function removeProposalSignArea() {
        $(".proposal_sign_print_date").hide();
        $(".proposal_approve_form").show();
    }

    function approveProposal(proposalId, approveDate, approveName) {
        someBlock.preloader();
        $.ajax({
            url: '{{route("customer.approve_proposal")}}',
            method: 'POST',
            data: {
                _token: _token,
                proposal_id: proposalId,
                approve_date: approveDate,
                approve_name: approveName
            },
            success: function (res) {
                someBlock.preloader('remove');
                if (res.status === 'success') {
                    toastr.success(res.message)
                } else {
                    toastr.error('Unknown issue, please contact with admin');
                }
            }
        });
    }

    // print invoice
    $(document).on('click', '.print_invoice', function () {
        $("#invoice_preview_block").printThis();
    });

    // print proposal
    $(document).on('click', '.print_proposal', function () {
        $(".proposal_approve_form").hide();
        $(".proposal_sign_print_date").show();
        $("#proposal_preview_block").printThis({
            afterPrint: removeProposalSignArea
        });
    });

    // approve proposal
    $(document).on('click', '#proposal_approve_submit', function() {
        let proposalId = $(this).data('id');
        let approveDate = $("#proposal_approve_date").val();
        let approveName = $("#proposal_approve_full_name").val();
        if (!approveDate) {
            toastr.error('Please enter the date of approval');
        } else if (!approveName) {
            toastr.error('Please enter your full name');
        } else {
            swal({
                title: 'Are you sure you want to approve this proposal?',
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
                    approveProposal(proposalId, approveDate, approveName);
                }
            });
        }
    });

    // expand/collapse daily log detail
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
</script>