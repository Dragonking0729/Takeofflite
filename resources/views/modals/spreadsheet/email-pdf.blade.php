<!-- interview costitem formula modal -->
<div class="modal fade" id="email_pdf_modal" style="z-index: 9999; top: 35%;">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Email PDF</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" action="{{url('estimate/get_price')}}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="price_project_id" value="{{$page_info['project_id']}}">
                    <div class="form-group row justify-content-center">
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="email_pdf_choice" id="email_pdf_quote"
                                   value="send_quote" checked>
                            <label for="email_pdf_quote" class="form-check-label">Send Quote </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="email_pdf_choice"
                                   id="email_pdf_cost_info"
                                   value="send_cost_info">
                            <label for="email_pdf_cost_info" class="form-check-label">Send Our Cost Info </label>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="send_email_pdf">Send PDF</button>
                </div>
            </form>

        </div>
    </div>
</div>