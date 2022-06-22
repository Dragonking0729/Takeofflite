<nav id="drawing-toolbar" class="active">
    <ul class="list-unstyled components mb-0">
        <li class="move_toolbar"></li>
        {{-- upload PDF --}}
        <li style="display: {{$category == 'plan' ? 'block' : 'none'}};">
            <span class="pdf-upload">
            </span>
            <form action="{{url('documents/file_upload')}}" method="POST" enctype="multipart/form-data" id="pdfUpload">
                @csrf
                <input type="file" style="display: none" name="pdf" accept=".pdf">
                <input type="hidden" name="project_id" value="{{$page_info['project_id']}}">
                <input type="hidden" name="category" value="{{$category}}">
            </form>
        </li>
        <li style="display: {{$category == 'picture' ? 'block' : 'none'}};">
            <span class="picture-upload">
            </span>
            <form action="{{url('documents/file_upload')}}" method="POST" enctype="multipart/form-data" id="picUpload">
                @csrf
                <input type="file" style="display: none" name="picture"
                       accept="image/bmp, image/gif, image/jpg, image/jpeg, image/png, image/tif, image/tiff">
                <input type="hidden" name="project_id" value="{{$page_info['project_id']}}">
                <input type="hidden" name="category" value="{{$category}}">
            </form>
        </li>
        <li style="display: {{$category == 'video' ? 'block' : 'none'}};">
            <span class="video-upload">
            </span>
            <form action="{{url('documents/file_upload')}}" method="POST" enctype="multipart/form-data"
                  id="videoUpload">
                @csrf
                <input type="file" style="display: none" name="video" accept="video/*">
                <input type="hidden" name="project_id" value="{{$page_info['project_id']}}">
                <input type="hidden" name="category" value="{{$category}}">
            </form>
        </li>
        <li style="display: {{$category == 'other' ? 'block' : 'none'}};">
            <span class="other-upload">
            </span>
            <form action="{{url('documents/file_upload')}}" method="POST" enctype="multipart/form-data"
                  id="otherUpload">
                @csrf
                <input type="file" style="display: none" name="other">
                <input type="hidden" name="project_id" value="{{$page_info['project_id']}}">
                <input type="hidden" name="category" value="{{$category}}">
            </form>
        </li>
        <li>
            <span class="remove-multiple-sheets disable">
            </span>
        </li>
    </ul>
</nav>