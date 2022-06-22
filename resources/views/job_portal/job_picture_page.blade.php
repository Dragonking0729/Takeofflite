<div id="job_picture_page" class="tab-pane fade">
    <div class="row mt-3 job_pictures">
        @foreach($pictures as $picture)
            <div class="col-sm-12 mb-3">
                <img src="{{asset($picture->file)}}" alt="{{$picture->sheet_name}}" class="img-fluid"/>
            </div>
        @endforeach
    </div>
</div>