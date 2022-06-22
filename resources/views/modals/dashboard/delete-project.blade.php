<div class="modal fade" id="delete_project">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header border-bottom-0">
                <h4 class="modal-title">DELETE PROJECT</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form id="delete_project_modal_form" action="" method="POST">
                @csrf
                <!-- Modal body -->
                <div class="modal-body">
                    <input name="_method" type="hidden" value="DELETE">
                    <p class="text-center">Are you sure you want to delete your project?</p>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Delete</button>
                </div>
            </form>

        </div>
    </div>
</div>