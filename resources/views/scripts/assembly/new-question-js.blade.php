<script>
    {{-- cost item new TKL question script --}}
    $("#create_new_question").click(function () {
        let newQuestion = $("#new_question").val();
        if (newQuestion) {
            let helpNotes = $("#help_notes").val();
            let questionType = $("input[name='question_type']:checked").val();
            someBlock.preloader();
            $.ajax({
                url: "{{ url('costitem/new_question') }}",
                method: "POST",
                data: {
                    _token: _token,
                    newQuestion: newQuestion,
                    helpNotes: helpNotes,
                    questionType: questionType
                },
                success: function (data) {
                    someBlock.preloader('remove');
                    if (data.status === 'success') {
                        toastr.success(data.message);
                        let html = `<option value="${data.id}" data-question_type="${questionType}">${newQuestion}</option>`;
                        $('#variables').append(html);
                        $("#create_new_question_modal").modal('hide');
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        } else {
            toastr.error("Please add question");
        }
    })
</script>