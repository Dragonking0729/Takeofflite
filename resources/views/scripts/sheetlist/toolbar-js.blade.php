<script>
    // pdf upload
    $(".pdf-upload").click(function () {
        $("input[name='pdf']").trigger('click');
    });
    $('input[name="pdf"]').on('change', function() {
        $('.someBlock').preloader();
        $('form#pdfUpload').submit();
    });

    // picture upload
    $(".picture-upload").click(function () {
        $("input[name='picture']").trigger('click');
    });
    $('input[name="picture"]').on('change', function() {
        $('.someBlock').preloader();
        $('form#picUpload').submit();
    });

    // video upload
    $(".video-upload").click(function () {
        $("input[name='video']").trigger('click');
    });
    $('input[name="video"]').on('change', function() {
        $('.someBlock').preloader();
        $('form#videoUpload').submit();
    });

    // other upload
    $(".other-upload").click(function () {
        $("input[name='other']").trigger('click');
    });
    $('input[name="other"]').on('change', function() {
        $('.someBlock').preloader();
        $('form#otherUpload').submit();
    });



    $(document).on('mousedown', '.move_toolbar', function() {
        $('#drawing-toolbar').draggable();
    });
</script>